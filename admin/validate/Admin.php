<?php
namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule =   [
        'username'  => 'require|length:3,25',
        'email'     =>'email'
    ];
    protected $message  =   [
        'username.require'      => 'error',
        'username.length'       => '3-25 pls',
        'email.email'           => 'error',
    ];
}