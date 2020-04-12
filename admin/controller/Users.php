<?php
namespace app\admin\controller;
use app\admin\model\Users as UsersModel;
class Users extends Common{
    
    public function index(){
        if(request()->isPost()){
            $key=input('post.key');
            $page =input('page')?input('page'):1;
            $pageSize =input('limit')?input('limit'):config('pageSize');
            $list=db('users')->alias('u')
                ->join(config('database.prefix').'user_level ul','u.level = ul.level_id','left')
                ->field('u.*,ul.level_name')
                ->where('u.email|u.mobile|u.username','like',"%".$key."%")
                ->order('u.id desc')
                ->paginate(array('list_rows'=>$pageSize,'page'=>$page))
                ->toArray();
            foreach ($list['data'] as $k=>$v){
                $list['data'][$k]['reg_time'] = date('Y-m-d H:s',$v['reg_time']);
            }
            return $result = ['code'=>0,'msg'=>'','data'=>$list['data'],'count'=>$list['total'],'rel'=>1];
        }
        return $this->fetch();
    }
 
    public function usersState(){
        $id=input('post.id');
        $is_lock=input('post.is_lock');
        if(db('users')->where('id='.$id)->update(['is_lock'=>$is_lock])!==false){
            return ['status'=>1,'msg'=>''];
        }else{
            return ['status'=>0,'msg'=>''];
        }
    }
    public function edit($id=''){
        if(request()->isPost()){
            $user = db('users');
            $data = input('post.');
            $level =explode(':',$data['level']);
            $data['level'] = $level[1];
            $province =explode(':',$data['province']);
            $data['province'] = isset( $province[1])?$province[1]:'';
            $city =explode(':',$data['city']);
            $data['city'] = isset( $city[1])?$city[1]:'';
            $district =explode(':',$data['district']);
            $data['district'] = isset( $district[1])?$district[1]:'';
            if(empty($data['password'])){
                unset($data['password']);
            }else{
                $data['password'] = md5($data['password']);
            }
            if ($user->update($data)!==false) {
                $result['msg'] = '';
                $result['url'] = url('index');
                $result['code'] = 1;
            } else {
                $result['msg'] = '';
                $result['code'] = 0;
            }
            return $result;
        }else{
            $province = db('Region')->where ( array('pid'=>1) )->select ();
            $user_level=db('user_level')->order('sort')->select();
            $info = UsersModel::get($id);
            $this->assign('info',json_encode($info,true));
            $this->assign('title',lang('edit').lang('user'));
            $this->assign('province',json_encode($province,true));
            $this->assign('user_level',json_encode($user_level,true));

            $city = db('Region')->where ( array('pid'=>$info['province']) )->select ();
            $this->assign('city',json_encode($city,true));
            $district = db('Region')->where ( array('pid'=>$info['city']) )->select ();
            $this->assign('district',json_encode($district,true));
            return $this->fetch();
        }
    }

    public function getRegion(){
        $Region=db("region");
        $pid = input("pid");
        $arr = explode(':',$pid);
        $map['pid']=$arr[1];
        $list=$Region->where($map)->select();
        return $list;
    }

    public function usersDel(){
        db('users')->delete(['id'=>input('id')]);
        db('oauth')->delete(['uid'=>input('id')]);
        return $result = ['code'=>1,'msg'=>''];
    }
    public function delall(){
        $map[] =array('id','IN',input('param.ids/a'));
        db('users')->where($map)->delete();
        $result['msg'] = '';
        $result['code'] = 1;
        $result['url'] = url('index');
        return $result;
    }


    public function userGroup(){
        if(request()->isPost()){
            $userLevel=db('user_level');
            $list=$userLevel->order('sort')->select();
            return $result = ['code'=>0,'msg'=>'','data'=>$list,'rel'=>1];
        }
        return $this->fetch();
    }
    public function groupAdd(){
        if(request()->isPost()){
            $data = input('post.');
            db('user_level')->insert($data);
            $result['msg'] = '';
            $result['url'] = url('userGroup');
            $result['code'] = 1;
            return $result;
        }else{
            $this->assign('title',lang('add')."");
            $this->assign('info','null');
            return $this->fetch('groupForm');
        }
    }
    public function groupEdit(){
        if(request()->isPost()) {
            $data = input('post.');
            db('user_level')->update($data);
            $result['msg'] = '';
            $result['url'] = url('userGroup');
            $result['code'] = 1;
            return $result;
        }else{
            $map['level_id'] = input('param.level_id');
            $info = db('user_level')->where($map)->find();
            $this->assign('title',lang('edit')."");
            $this->assign('info',json_encode($info,true));
            return $this->fetch('groupForm');
        }
    }
    public function groupDel(){
        $level_id=input('level_id');
        if (empty($level_id)){
            return ['code'=>0,'msg'=>''];
        }
        db('user_level')->where(array('level_id'=>$level_id))->delete();
        return ['code'=>1,'msg'=>''];
    }
    public function groupOrder(){
        $userLevel=db('user_level');
        $data = input('post.');
        $userLevel->update($data);
        $result['msg'] = '';
        $result['url'] = url('userGroup');
        $result['code'] = 1;
        return $result;
    }




}