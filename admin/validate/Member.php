<?php
namespace app\admin\validate;

use think\Validate;

class Member extends Validate
{
	protected $rule = [
		['group_id', 'require', 'pls choose'],
		['username', 'require', 'error'],
		['pwd', 'require|length:6,25', 'try again'],
		['petname', 'require', 'error'],
		['tel', 'checkName:tel|unique:member', 'already registered'],
		['email', 'email|unique:member', 'error'],
	];
	
	protected function checkName($value,$rule,$data){
		if(is_mobile_phone($value)){
			return true;
		}else{
			return 'error';
		}
	}
}