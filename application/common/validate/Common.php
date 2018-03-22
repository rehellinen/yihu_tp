<?php
/**
 * Created by PhpStorm.
 * User: rehellinen
 * Date: 2017/9/30
 * Time: 12:25
 */

namespace app\common\validate;


use think\Validate;

class Common extends BaseValidate
{
    protected $rule = [
        ['id', 'require|number'],
        ['status', 'require|number']
    ];

    protected $scene = [
        ['id' => 'id'],
        ['status' => 'status']
    ];
}