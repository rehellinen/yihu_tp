<?php
/**
 * Created by PhpStorm.
 * User: rehellinen
 * Date: 2018/3/19
 * Time: 18:30
 */

namespace app\api\controller\v1;

use app\common\exception\GoodsException;
use app\common\exception\SuccessMessage;
use app\common\model\Goods as GoodsModel;
use app\common\validate\Common;

class Goods extends BaseController
{
    public function getGoods()
    {
        $goods = (new GoodsModel)->getNotSold();
        if(!$goods){
            throw new GoodsException();
        }

        throw new SuccessMessage([
            'data' => $goods,
            'message' => '获取所有产品信息成功'
        ]);
    }

    public function getGoodsById($id)
    {
        (new Common())->goCheck('id');
        $goods = (new GoodsModel())->getById($id);
        if(!$goods){
            throw new GoodsException();
        }

        throw new SuccessMessage([
            'data' => $goods,
            'message' => '获取产品信息成功'
        ]);
    }
}