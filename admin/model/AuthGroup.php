<?php
namespace app\admin\model;
use think\Model;
class AuthGroup extends Model
{
    protected $type       = [
     
        'addtime' => 'timestamp:Y-m-d H:i:s',
    ];
}