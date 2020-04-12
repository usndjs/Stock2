<?php
namespace app\admin\controller;
use think\Db;
use \tp5er\Backup;
class Database extends Common
{
    protected $db = '', $datadir;
    function initialize(){
        parent::initialize();
        $this->config=array(
            'path'     => './Data/',
            'part'     => 20971520,
            'compress' => 0,
            'level'    => 9 
        );
        $this->db = new Backup($this->config);
    }
    public function database(){
        if(request()->isPost()){
            $list = $this->db->dataList();
            $total = 0;
            foreach ($list as $k => $v) {
                $list[$k]['size'] = format_bytes($v['data_length']);
                $total += $v['data_length'];
            }
            return $result = ['code'=>0,'msg'=>'success','data'=>$list,'total'=>format_bytes($total),'tableNum'=>count($list),'rel'=>1];
        }
        return view();
    }
    
    public function optimize() {
        $tables = input('tables/a');
        if (empty($tables)) {
            return ['code'=>0,'msg'=>'please make your choice'];
        }
        if($this->db->optimize($tables)){
            return ['code'=>1,'msg'=>'success'];
        }else{
            return ['code'=>0,'msg'=>'try again'];
        }
    }
    
    public function repair() {
        $tables = input('tables/a');
        if (empty($tables)) {
            return ['code'=>0,'msg'=>''];
        }
        if($this->db->repair($tables)){
            return ['code'=>1,'msg'=>''];
        }else{
            return ['code'=>0,'msg'=>''];
        }
    }
    
    public function backup(){
        $tables = input('post.tables/a');
        if (!empty($tables)) {
            foreach ($tables as $table) {
                $this->db->setFile()->backup($table, 0);
            }
            return ['code'=>1,'msg'=>''];
        } else {
            return ['code'=>0,'msg'=>''];
        }
    }
    
    public function restore(){
        if(request()->isPost()){
            $list =  $this->db->fileList();
            return ['code'=>0,'msg'=>'','data'=>$list,'rel'=>1];
        }
        return view();
    }
  
    public function import($time) {
        $list  = $this->db->getFile('timeverif',$time);
        $this->db->setFile($list)->import(1);
        return ['code'=>1,'msg'=>''];
    }

    
    public function downFile($time) {
        $this->db->downloadFile($time);
    }

    public function delSqlFiles() {
        $time = input('post.time');
        if($this->db->delFile($time)){
            return ['code'=>1,'msg'=>""];
        }else{
            return ['code'=>0,'msg'=>""];
        }
    }
}