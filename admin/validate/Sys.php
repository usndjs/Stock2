<?php
namespace app\admin\validate;

use think\Validate;

class Sys extends Validate
{
	protected $rule = [
		['sys_name', 'require', ''],
		['sys_url', 'require', '']
	];
}