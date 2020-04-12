<?php
namespace app\admin\model;

use think\Model;

class Users extends Model
{
	protected $name = 'users';
    protected $type       = [
       
        'reg_time' => 'timestamp:Y-m-d H:i:s',
    ];

	protected function setpwdAttr($value){
			return md5($value);
	}

}