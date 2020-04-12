<?php
namespace app\admin\controller;
use think\Db;
use think\Controller;
class Common extends Controller
{
    protected $mod,$role,$system,$nav,$menudata,$cache_model,$module,$moduleid,$adminRules,$HrefId;
    public function initialize()
    {
  
        if (!session('aid')) {
            $this->redirect('admin/login/index');
        }
        define('MODULE_NAME',strtolower(request()->controller()));
        define('ACTION_NAME',strtolower(request()->action()));
    
        if(session('aid')!=1){
            $this->HrefId = db('auth_rule')->where('href',MODULE_NAME.'/'.ACTION_NAME)->value('id');
            
            $map['a.admin_id'] = session('aid');
            $rules=Db::table(config('database.prefix').'admin')->alias('a')
                ->join(config('database.prefix').'auth_group ag','a.group_id = ag.group_id','left')
                ->where($map)
                ->value('ag.rules');
            $this->adminRules = explode(',',$rules);
            if($this->HrefId){
                if(!in_array($this->HrefId,$this->adminRules)){
                    $this->error('not authorized');
                }
            }
        }
        $this->cache_model=array('AuthRule','System',);
        foreach($this->cache_model as $r){
            if(!cache($r)){
                savecache($r);
            }
        }
        $this->system = cache('System');
        $this->rule = cache('AuthRule');

    }
    
    public function _empty(){
        return $this->error('loading...');
    }
}
