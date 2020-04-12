<?php
namespace app\pc\controller;

use think\Db;
use think\Controller;
use think\captcha\Captcha;

class Home extends Controller{
    
    
    public function index()
    {
    	$session_u = session('user');
    	if($session_u){
    		return redirect('/center');
    	}
        $this->system = cache('System');
        $this->assign('system',$this->system);
        return view('login');
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

    public function check($code){
        return captcha_check($code);
    }

    public function login()
    {
        if(request()->isPost()) {
            $data = input('post.');
            $this->system = cache('System');
            if($this->system['code']=='open'){
                if(!$this->check($data['vercode'])){
                    return ['code' => 0, 'msg' => 'verify failed'];
                }
            }

            $user = Db::name('users')->where(['account'=>$data['username']])->find();
            if(!$user){
                return ['code' => 0, 'msg' => 'user not exist'];
            }
            if(md5($data['password']) == $user['password']){
                $session_u['id'] = $user['id'];
                $session_u['account'] = $user['account'];
                session('user',$session_u);
                return ['code' => 1, 'msg' => 'login success'];
            }else{
                return ['code' => 0, 'msg' => 'password error'];
            }
            
        }else{
            return ['code' => 0, 'msg' => 'not allowed'];
        }
    }


    public function register()
    {
        if(request()->isPost()) {
            $data = input('post.');
            if($data['password'] != $data['re_password']){
                return ['code' => 0, 'msg' => 'Two different password entries'];
            }

            $this->system = cache('System');
            if($this->system['code']=='open'){
                if(!$this->check($data['vercode'])){
                    return ['code' => 0, 'msg' => 'verify failed'];
                }
            }

            $user = Db::name('users')->where(['account'=>$data['username']])->find();
            if($user){
                return ['code' => 0, 'msg' => 'user has exist'];
            }
            $datas = [
                'account' => $data['username'],
                'password' => md5($data['password'])
            ];
            $insert = Db::name('users')->insertGetId($datas);
            if($insert){
                $session_u['id'] = $insert;
                $session_u['account'] = $data['username'];
                session('user',$session_u);
                return ['code' => 1, 'msg' => 'success'];
            }else{
                return ['code' => 0, 'msg' => 'failed'];
            }
        }
        $session_u = session('user');
    	if($session_u){
    		return redirect('/center');
    	}
        $this->system = cache('System');
        $this->assign('system',$this->system);
        return view();
    }
    

  
    public function center()
    {
        $session_u = session('user');
        $user = Db::name('users')->where(['id'=>$session_u['id']])->find();
        
        $stock = Db::name('stock_code')->where(['uid'=>$session_u['id']])->order('id desc')->select();
        
        foreach($stock as $key=>$v){
        	$url = 'http://web.juhe.cn:8080/finance/stock/usa?gid='. $v['code'] .'&key=6f142aadfcdd12bc137e532a6bb2e156';
        	$return = $this->https_get($url);
        	$res = $return['result'][0]['data'];
        	
        	$stock[$key]['gid'] = $res['gid'];
        	$stock[$key]['name'] = $res['name'];
        	$stock[$key]['lastestpri'] = $res['lastestpri'];
        	$stock[$key]['openpri'] = $res['openpri'];
        	$stock[$key]['maxpri'] = $res['maxpri'];
        	$stock[$key]['minpri'] = $res['minpri'];
        	$stock[$key]['limit'] = $res['limit'];
        	$stock[$key]['max52'] = $res['max52'];
        	$stock[$key]['min52'] = $res['min52'];
        	// $stock[$key]['gid'] = $res['gid'];
        	// $stock[$key]['gid'] = $res['gid'];
        }

        $this->assign('stock',$stock);
        $this->assign('session_u',$session_u);
        $this->assign('user',$user);
        return view();
    }



    public function income()
    {
        return view();
    }

    public function logout()
    {
        session(null);
        return redirect('/');
    }

    public function stock()
    {
        $key = input('param.key');
        $url = 'http://web.juhe.cn:8080/finance/stock/usa?gid='. $key .'&key=6f142aadfcdd12bc137e532a6bb2e156';
        $return = $this->https_get($url);

        if($return['resultcode'] != 200){
        	$this->error($return['reason']);
        	
        }
        $session_u = session('user');
        $user = Db::name('users')->where(['id'=>$session_u['id']])->find();

        $this->assign('stock',$return['result'][0]['data']);
        $this->assign('user',$user);
        $this->assign('session_u',$session_u);
        return view();
    }

    public function add_stock()
    {
    	if(request()->isPost()){
    		$data = input('post.');
	        $uid = session('user')['id'];
	        
	        $stock_code = Db::name('stock_code')->where(['code'=>$data['code']])->where(['uid'=>$uid])->find();
	        if($stock_code){
	        	return ['code'=>0,'msg'=>'stock has exist'];
	        }
	        
	        $datas = [
	        		'uid' => $uid,
	        		'code' => $data['code'],
	        		'add_time' => time()
	        	];
	        
	        $insert = Db::name('stock_code')->insert($datas);
	        if($insert){
	        	return ['code'=>1,'msg'=>'add success'];
	        }else{
	        	return ['code'=>0,'msg'=> 'add failed , please again'];
	        }
    	}
        return ['code'=>405,'msg'=>'not allowed'];
    }


    public function https_get($url, $timeout = 1) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_HEADER, 0);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $data = curl_exec($curl);
 
        curl_close($curl);

        $jsoninfo = json_decode($data, true);

        return ($jsoninfo);
    }

}