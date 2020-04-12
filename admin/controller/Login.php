<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\Admin;
use think\captcha\Captcha;
class Login extends Controller
{
    private $cache_model,$system;
    public function initialize(){
        if (session('aid')) {
            $this->redirect('admin/index/index');
        }
        $this->cache_model=array('AuthRule','System');
        $this->system = cache('System');
        $this->assign('system',$this->system);
        if(empty($this->system)){
            foreach($this->cache_model as $r){
                savecache($r);
            }
        }
    }
    public function index(){
        if(request()->isPost()) {
            $data = input('post.');
            $admin = new Admin();
            $return = $admin->login($data,$this->system['code']);
            return ['code' => $return['code'], 'msg' => $return['msg']];
        }else{
            return $this->fetch();
        }
    }
    public function verify(){
        $config =    [
           
            'fontSize'    =>    25,
      
            'length'      =>    4,
            
            'useNoise'    =>    false,
            'bg'          =>    [255,255,255],
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }
}