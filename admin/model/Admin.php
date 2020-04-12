<?php
namespace app\admin\model;
use think\Model;
use think\Db;
class Admin extends Model
{
    protected $pk = 'admin_id';
    public function login($data,$code){
        if($code=='open'){
            if(!$this->check($data['vercode'])){
                return ['code' => 0, 'msg' => 'verify failed'];
            }
        }
        $user=Db::name('admin')->where('username',$data['username'])->find();
        if($user) {
            if ($user['is_open']==1 && $user['pwd'] == md5($data['password'])){
                session('username', $user['username']);
                session('aid', $user['admin_id']);
                $avatar = $user['avatar'] == '' ? '/static/admin/images/0.jpg' : $user['avatar'];
                session('avatar', $avatar);
                return ['code' => 1, 'msg' => 'log in success!'];
            }else{
                return ['code' => 0, 'msg' => 'username or password error, please try again'];
            }
        }else{
            return ['code' => 0, 'msg' => 'no user'];
        }
    }
    public function getInfo($admin_id){
        $info = Db::name('admin')->field('pwd',true)->find($admin_id);
        return $info;
    }
    public function check($code){
        return captcha_check($code);
    }

}

