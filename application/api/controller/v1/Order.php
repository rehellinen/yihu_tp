<?php
/**
 * Created by PhpStorm.
 * User: rehellinen
 * Date: 2018/3/26
 * Time: 19:49
 */

namespace app\api\controller\v1;

use app\common\exception\OrderException;
use app\common\exception\SuccessMessage;
use app\common\validate\Common;
use app\common\validate\Order as OrderValidate;
use app\common\service\Token as TokenService;
use app\common\model\Order as OrderModel;
use enum\OrderEnum;
use enum\TypeEnum;

class Order extends BaseController
{
    protected $beforeActionList = [
        'checkBuyerScope' => ['only' => 'placeOrder, getBuyerOrder, getDetail'],
        'checkSellerShopScope' => ['only' => 'getSellerOrder']
    ];

    /**
     * 买家下单API
     * @throws SuccessMessage
     */
    public function placeOrder()
    {
        (new OrderValidate())->goCheck('order');
        $post = $this->request->post();
        $goods = $post['goods'];

        $order = new \app\common\service\Order();
        $status = $order->place($goods, TokenService::getBuyerID());
        throw new SuccessMessage([
            'message' => '创建订单成功',
            'data' => $status
        ]);
    }

    /**
     * 买家 / 二手商家 / 自营商家获取所有订单
     * @param int $page 页码
     * @param int $size 每页数量
     * @throws OrderException
     * @throws SuccessMessage
     */
    public function getAllOrder($page = 1, $size = 14)
    {
        (new Common())->goCheck('page');
        $buyerID = TokenService::getBuyerID();
        $sellerID = TokenService::getCurrentTokenVar('sellerID');
        $shopID = TokenService::getCurrentTokenVar('shopID');

        if($buyerID){
            $res = (new OrderModel())->getOrderByUser($buyerID, $page, $size);
        }elseif($sellerID){
            $type = 2;
            $id = $sellerID;
            $res = (new OrderModel())->getOrderBySellerOrShop($type, $id, $page, $size);
        }elseif($shopID){
            $type = 1;
            $id = $shopID;
            $res = (new OrderModel())->getOrderBySellerOrShop($type, $id, $page, $size);
        }

        if($res->isEmpty()){
            throw new OrderException([
                'data' => [
                    'data' => []
                ]
            ]);
        }
        throw new SuccessMessage([
           '获取订单成功',
            'data' => $res->toArray()
        ]);
    }

    /**
     * 买家 / 二手商家 / 自营商家获取订单详情
     * @param $id
     * @param $type
     * @throws OrderException
     * @throws SuccessMessage
     */
    public function getDetail($id, $type)
    {
        (new Common())->goCheck('id');
        $buyerID = TokenService::getBuyerID();
        $sellerID = TokenService::getCurrentTokenVar('sellerID');
        $shopID = TokenService::getCurrentTokenVar('shopID');

        if($buyerID){
            $order = (new OrderModel)->getBuyerOrderByID($id, $buyerID, $type);
        }
        if($sellerID){
            $order = (new OrderModel)->getSellerOrShopDetailByID($id, $sellerID, 2);
        }
        if($shopID){
            $order = (new OrderModel)->getSellerOrShopDetailByID($id, $shopID, 1);
        }

        if(!$order){
            throw new OrderException();
        }
        $order = $order->hidden(['prepay_id']);
        throw new SuccessMessage([
            'message' => '获取订单详情成功',
            'data' => $order
        ]);
    }

    public function deliver($id)
    {
        (new Common())->goCheck('id');
        $sellerID = TokenService::getCurrentTokenVar('sellerID');
        $shopID = TokenService::getCurrentTokenVar('shopID');

        if($sellerID){
            $res = (new OrderModel)->updateOrderStatus($id, $sellerID, TypeEnum::OldGoods, OrderEnum::DELIVERED);
        }

        if($shopID){
            $res = (new OrderModel)->updateOrderStatus($id, $shopID, TypeEnum::NewGoods, OrderEnum::DELIVERED);
        }

        if($res){
            throw new SuccessMessage([
                'message' => '发货成功'
            ]);
        }
    }

    public function confirm($id)
    {
        (new Common())->goCheck('id');
        $buyerID = TokenService::getBuyerID();

        $res = (new OrderModel())->where([
            'id' => $id,
            'buyer_id' => $buyerID,
        ])->update(['status' => OrderEnum::COMPLETED]);

        if($res){
            throw new SuccessMessage([
                'message' => '确认收货成功'
            ]);
        }
    }
}