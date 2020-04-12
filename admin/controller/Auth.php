<?php
namespace app\admin\controller;
use function MongoDB\BSON\toJSON;
use think\Db;
use clt\Leftnav;
use app\admin\model\Admin;
use app\admin\model\AuthGroup;
use app\admin\model\authRule;
use think\facade\Request;
use think\Validate;
class Auth extends Common
{

    public function adminList(){
        if(Request::isAjax()){
            $val=input('val');
            $url['val'] = $val;
            $this->assign('testval',$val);
            $map='';
            if($val){
                $map['username|email|tel']= array('like',"%".$val."%");
            }
            if (session('aid')!=1){
                $map='admin_id='.session('aid');
            }
            $list=Db::table(config('database.prefix').'admin')->alias('a')
                ->join(config('database.prefix').'auth_group ag','a.group_id = ag.group_id','left')
                ->field('a.*,ag.title')
                ->where($map)
                ->select();
            return $result = ['code'=>0,'msg'=>'success','data'=>$list,'rel'=>1];
        }
        return view();
    }

    public function adminAdd(){
        if(Request::isAjax()){
            $data = input('post.');
            $check_user = Admin::get(['username'=>$data['username']]);
            if ($check_user) {
                return $result = ['code'=>0,'msg'=>'Already used! try again!'];
            }
            $data['pwd'] = input('post.pwd', '', 'md5');
            $data['add_time'] = time();
            $data['ip'] = request()->ip();
        
            $msg = $this->validate($data,'app\admin\validate\Admin');
            if($msg!='true'){
                return $result = ['code'=>0,'msg'=>$msg];
            }
           
            $checkPwd = Validate::make([input('post.pwd')=>'require']);
            if (false === $checkPwd) {
                return $result = ['code'=>0,'msg'=>'error'];
            }
            
            if (Admin::create($data)) {
                return ['code'=>1,'msg'=>'adminatrator add successfully','url'=>url('adminList')];
            } else {
                return ['code'=>0,'msg'=>'failed adding'];
            }
        }else{
            $auth_group = AuthGroup::all();
            $this->assign('authGroup',$auth_group);
            $this->assign('title',lang('add').lang('admin'));
            $this->assign('info','null');
            $this->assign('selected', 'null');
            return view('adminForm');
        }
    }
   
    public function adminDel(){
        $admin_id=input('post.admin_id');
        if (session('aid')==1){
            Admin::where('admin_id','=',$admin_id)->delete();
            return $result = ['code'=>1,'msg'=>'success'];
        }else{
            return $result = ['code'=>0,'msg'=>'No authorized'];
        }
    }
  
    public function adminState(){
        $id=input('post.id');
        $is_open=input('post.is_open');
        if (empty($id)){
            $result['status'] = 0;
            $result['info'] = 'ID not exist!';
            $result['url'] = url('adminList');
            return $result;
        }
        db('admin')->where('admin_id='.$id)->update(['is_open'=>$is_open]);
        $result['status'] = 1;
        $result['info'] = 'success';
        $result['url'] = url('adminList');
        return $result;
    }
   
    public function adminEdit(){
        if(request()->isPost()){
            $data = input('post.');
            $pwd=input('post.pwd');
            $map[] = ['admin_id','<>',$data['admin_id']];
            $where['admin_id'] = $data['admin_id'];

            if($data['username']){
                $map[] = ['username','=',$data['username']];
                $check_user = Admin::where($map)->find();
                if ($check_user) {
                    return $result = ['code'=>0,'msg'=>'Already exist! try again!'];
                }
            }
            if ($pwd){
                $data['pwd']=input('post.pwd','','md5');
            }else{
                unset($data['pwd']);
            }
            $msg = $this->validate($data,'app\admin\validate\Admin');
            if($msg!='true'){
                return $result = ['code'=>0,'msg'=>$msg];
            }
            Admin::update($data,$where);
            if( $data['admin_id'] == session('aid')){
                session('username',$data['username']);
                $avatar = $data['avatar']==''?'/static/admin/images/0.jpg':$data['avatar'];
                session('avatar',$avatar);
            }
            return $result = ['code'=>1,'msg'=>'success','url'=>url('adminList')];
        }else{
            $auth_group = AuthGroup::all();
            $admin = new Admin();
            $info = $admin->getInfo(input('admin_id'));
            $this->assign('info', json_encode($info,true));
            $this->assign('authGroup',$auth_group);
            $this->assign('title',lang('edit').lang('admin'));
            return view('adminForm');
        }
    }

    public function adminGroup(){
        if(request()->isPost()){
            $list = AuthGroup::all();
            return $result = ['code'=>0,'msg'=>'success','data'=>$list,'rel'=>1];
        }
        return view();
    }

    public function groupDel(){
        AuthGroup::where('group_id','=',input('id'))->delete();
        return $result = ['code'=>1,'msg'=>'success'];
    }
    
    public function groupAdd(){
        if(request()->isPost()){
            $data=input('post.');
            $data['addtime']=time();
            AuthGroup::create($data);
            $result['msg'] = 'success';
            $result['url'] = url('adminGroup');
            $result['code'] = 1;
            return $result;
        }else{
            $this->assign('title','add group');
            $this->assign('info','null');
            return $this->fetch('groupForm');
        }
    }
    
    public function groupEdit(){
        if(request()->isPost()) {
            $data=input('post.');
            $where['group_id'] = $data['group_id'];
            AuthGroup::update($data,$where);
            $result = ['code'=>1,'msg'=>'success','url'=>url('adminGroup')];
            return $result;
        }else{
            $id = input('id');
            $info = AuthGroup::get(['group_id'=>$id]);
            $this->assign('info', json_encode($info,true));
            $this->assign('title','edit group');
            return $this->fetch('groupForm');
        }
    }
    
    public function groupAccess(){
        $nav = new Leftnav();
        $admin_rule=db('auth_rule')->field('id,pid,title')->order('sort asc')->select();
        $rules = db('auth_group')->where('group_id',input('id'))->value('rules');
        $arr = $nav->auth($admin_rule,$pid=0,$rules);
        $arr[] = array(
            "id"=>0,
            "pid"=>0,
            "title"=>"total",
            "open"=>true
        );
        $this->assign('data',json_encode($arr,true));
        return $this->fetch();
    }
    public function groupSetaccess(){
        $rules = input('post.rules');
        if(empty($rules)){
            return array('msg'=>'choose your auth','code'=>0);
        }
        $data = input('post.');
        $where['group_id'] = $data['group_id'];
        if(AuthGroup::update($data,$where)){
            return array('msg'=>'success','url'=>url('adminGroup'),'code'=>1);
        }else{
            return array('msg'=>'error','code'=>0);
        }
    }


    public function adminRule(){
        if(request()->isPost()){
            $arr = cache('authRuleList');
            if(!$arr){
				$arr = Db::name('authRule')->order('pid asc,sort asc')->select();
				foreach($arr as $k=>$v){
                    $arr[$k]['lay_is_open']=false;
                }
                cache('authRuleList', $arr, 3600);
            }
            return $result = ['code'=>0,'msg'=>'success','data'=>$arr,'is'=>true,'tip'=>'success'];
        }
        return view();
    }
    public function clear(){
        $arr = Db::name('authRule')->where('pid','neq',0)->select();
        foreach ($arr as $k=>$v){
            $p = Db::name('authRule')->where('id',$v['pid'])->find();
            if(!$p){
                Db::name('authRule')->where('id',$v['id'])->delete();
            }
        }
        cache('authRule', NULL);
        cache('authRuleList', NULL);
        $this->success('success');
    }
    public function ruleAdd(){
        if(request()->isPost()){
            $data = input('post.');
            $data['addtime'] = time();
            authRule::create($data);
            cache('authRule', NULL);
            cache('authRuleList', NULL);
            cache('addAuthRuleList', NULL);
            return $result = ['code'=>1,'msg'=>'success','url'=>url('adminRule')];
        }else{
            $nav = new Leftnav();
            $arr = cache('addAuthRuleList');
            if(!$arr){
                $authRule = authRule::all(function($query){
                    $query->order('sort', 'asc');
                });
                $arr = $nav->menu($authRule);
                cache('addAuthRuleList', $arr, 3600);
            }
            $this->assign('admin_rule',$arr);
            return $this->fetch();
        }
    }
    public function ruleOrder(){
        $auth_rule=db('auth_rule');
        $data = input('post.');
        if($auth_rule->update($data)!==false){
            cache('authRuleList', NULL);
            cache('authRule', NULL);
            cache('addAuthRuleList', NULL);
            return $result = ['code'=>1,'msg'=>'success','url'=>url('adminRule')];
        }else{
            return $result = ['code'=>0,'msg'=>'failed'];
        }
    }

    public function ruleState(){
        $id=input('post.id');
        $menustatus=input('post.menustatus');
        if(db('auth_rule')->where('id='.$id)->update(['menustatus'=>$menustatus])!==false){
            cache('authRule', NULL);
            cache('authRuleList', NULL);
            cache('addAuthRuleList', NULL);
            return ['status'=>1,'msg'=>'success'];
        }else{
            return ['status'=>0,'msg'=>'failed'];
        }
    }
    
    public function ruleTz(){
        $id=input('post.id');
        $authopen=input('post.authopen');
        if(db('auth_rule')->where('id='.$id)->update(['authopen'=>$authopen])!==false){
            cache('authRule', NULL);
            cache('authRuleList', NULL);
            cache('addAuthRuleList', NULL);
            return ['status'=>1,'msg'=>'success'];
        }else{
            return ['status'=>0,'msg'=>'failed'];
        }
    }
    public function ruleDel(){
        authRule::destroy(['id'=>input('param.id')]);
        cache('authRule', NULL);
        cache('authRuleList', NULL);
        cache('addAuthRuleList', NULL);
        return $result = ['code'=>1,'msg'=>'success'];
    }

    public function ruleEdit(){
        if(request()->isPost()) {
            $datas = input('post.');
            if(authRule::update($datas)) {
                cache('authRule', NULL);
                cache('authRuleList', NULL);
                cache('addAuthRuleList', NULL);
                return json(['code' => 1, 'msg' => 'success', 'url' => url('adminRule')]);
            } else {
                return json(['code' => 0, 'msg' =>'failed']);
            }
        }else{
            $admin_rule = authRule::get(function($query){
                $query->where(['id'=>input('id')])->field('id,href,title,icon,sort,menustatus');
            });
            $this->assign('rule',$admin_rule);
            return $this->fetch();
        }
    }
}