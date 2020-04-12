<?php
namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Controller;
use think\facade\Env;
class UpFiles extends Common
{
    public function upload(){
        
        $fileKey = array_keys(request()->file());
        
        $file = request()->file($fileKey['0']);
       
        $info = $file->validate(['ext' => 'jpg,png,gif,jpeg'])->move('uploads');
        if($info){
            $result['code'] = 1;
            $result['info'] = '';
            $path=str_replace('\\','/',$info->getSaveName());
            $result['url'] = '/uploads/'. $path;
            return $result;
        }else{
           

            $result['code'] =0;
            $result['info'] =  $file->getError();
            $result['url'] = '';
            return $result;
        }
    }
    public function file(){
        $fileKey = array_keys(request()->file());
       
        $file = request()->file($fileKey['0']);
        
        $info = $file->validate(['ext' => 'zip,rar,pdf,swf,ppt,psd,ttf,txt,xls,doc,docx'])->move('uploads');
        if($info){
            $result['code'] = 0;
            $result['info'] = '';
            $path=str_replace('\\','/',$info->getSaveName());

            $result['url'] = '/uploads/'. $path;
            $result['ext'] = $info->getExtension();
            $result['size'] = byte_format($info->getSize(),2);
            return $result;
        }else{
           
            $result['code'] =1;
            $result['info'] = '';
            $result['url'] = '';
            return $result;
        }
    }
    public function pic(){
        
        $fileKey = array_keys(request()->file());
        
        $file = request()->file($fileKey['0']);
        
        $info = $file->validate(['ext' => 'jpg,png,gif,jpeg'])->move(Env::get('root_path') . 'public/uploads');
        if($info){
            $result['code'] = 1;
            $result['info'] = '';
            $path=str_replace('\\','/',$info->getSaveName());
            $result['url'] = '/uploads/'. $path;
            return json_encode($result,true);
        }else{
            
            $result['code'] =0;
            $result['info'] = '';
            $result['url'] = '';
            return json_encode($result,true);
        }
    }

    public function editUpload(){
        
        $fileKey = array_keys(request()->file());
     
        $file = request()->file($fileKey['0']);
        
        $info = $file->validate(['ext' => 'jpg,png,gif,jpeg'])->move('uploads');
        if($info){
            $path=str_replace('\\','/',$info->getSaveName());
            return '/uploads/'. $path;
        }else{
           
            $result['code'] =1;
            $result['msg'] = '';
            $result['data'] = '';
            return json_encode($result,true);
        }
    }
    
    public function upImages(){
        $fileKey = array_keys(request()->file());
      
        $file = request()->file($fileKey['0']);
        
        $info = $file->validate(['ext' => 'jpg,png,gif,jpeg'])->move(Env::get('root_path') . 'public/uploads');
        if($info){
            $result['code'] = 0;
            $result['msg'] = '';
            $path=str_replace('\\','/',$info->getSaveName());
            $result["src"] = '/uploads/'. $path;
            return $result;
        }else{
            
            $result['code'] =1;
            $result['msg'] = '';
            return $result;
        }
    }

    public function editimg(){
        $allowExtesions = array(
            'image' => 'gif,jpg,jpeg,png,bmp',
            'flash' => 'swf,flv',
            'media' => 'swf,flv,mp3,wav,wma,wmv,mid,avi,mpg,asf,rm,rmvb',
            'file' => 'doc,docx,xls,xlsx,ppt,htm,html,txt,zip,rar,gz,bz2',
        );
       
        $fileKey = array_keys(request()->file());
      
        $file = request()->file($fileKey['0']);
        
        $info = $file->validate(['ext'=>$allowExtesions[input('fileType')]])->move('./uploads');
        if($info){
            $path=str_replace('\\','/',$info->getSaveName());
            $url = '/uploads/'. $path;
            $result['code'] = '000';
            $result['message'] = '';
            $result['item'] = ['url'=>$url];
            return json($result);
        }else{
           
            $result['code'] =001;
            $result['message'] = $file->getError();
            $result['url'] = '';
            return json($result);
        }
    }
}