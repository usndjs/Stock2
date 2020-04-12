<?php
namespace app\common\taglib;
use think\template\TagLib;
class Clt extends TagLib {
    protected $tags = array(
     
        'close'     => ['attr' => 'time,format', 'close' => 0], 
        'open'      => ['attr' => 'name,type', 'close' => 1],

        'tinfo' => array('attr' => 'db,where,id','close' => 1),
        'tfield' => array('attr' => 'db,where,name','close' => 0),
        'clist'=> array('attr' => 'db,order,limit,where,id,key','close' => 1),
        'tlist' => array('attr' => 'db,order,limit,where,id,key','close' => 1),
    );

    public function tagClose($tag)
    {
        $format = empty($tag['format']) ? 'Y-m-d H:i:s' : $tag['format'];
        $time = empty($tag['time']) ? time() : $tag['time'];
        $parse = '<?php ';
        $parse .= 'echo date("' . $format . '",' . $time . ');';
        $parse .= ' ?>';
        return $parse;
    }


    public function tagOpen($tag, $content)
    {
        $type = empty($tag['type']) ? 0 : 1; 
        $name = $tag['name']; 
        $parse = '<?php ';
        $parse .= '$test_arr=[[1,3,5,7,9],[2,4,6,8,10]];'; 
        $parse .= '$__LIST__ = $test_arr[' . $type . '];';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }


    public function tagTfield($tag){
        $db = $tag['db']; 
        $where = isset($tag['where'])?$tag['where']:'';
        $name = $tag['name'];
        $str = '<?php ';
        $str .= 'echo db("' . $db . '")->where("' . $where . '")->value("'.$name.'");';
        $str .= '?>';
        return $str;
    }


    public function tagTinfo($attr,$content){
        $db = $attr['db']; 
        $where = isset($attr['where'])?$attr['where']:'';; 
        $id = $attr['id'];
        $str = '<?php ';
        $str .= '$'.$id.' =db("' . $db . '")->where("' . $where . '")->find();';
        $str .= '?>';
        $str .= $content;
        return $str;
    }
    public function tagClist($attr,$content) {
        $db = $attr['db']; 
        $order = isset($attr['order'])?$attr['order']:' a.sort asc,a.createtime desc,a.id desc';    
        $limit = isset($attr['limit'])?$attr['limit']:'15'; 
        $where = isset($attr['where'])?$attr['where'].' and (status = 1 or (status = 0 and createtime <'.time().'))':' status = 1 or (status = 0 and createtime <'.time().') '; 
        $id = $attr['id'];
        $key = isset($attr['key'])?$attr['key']:'k';
        $str = '<?php ';
        $str.='$result = db("'.$db.'")->alias("a")->join("category c"," a.catid = c.id","left")
            ->where("'.$where.'")
            ->field("a.*,c.catdir,c.catname")
            ->limit('.$limit.')
            ->order("'.$order.'")
            ->select();';
        $str .= 'if($result){';
        $str .= 'foreach ($result as $'.$key.'=>$'.$id.'):';
        $str .= '$result[$'.$key.']["time"]= toDate($'.$id.'["createtime"],"Y-m-d");';
        $str .= '$result[$'.$key.']["thumb"]= $'.$id.'["thumb"]?$'.$id.'["thumb"]:"";';
        $str .= '?>';
        $str .= '<?php endforeach; ?>';
        $str .= '<?php ';
        $str .= 'foreach ($result as $'.$key.'=>$'.$id.'):';
        $str .= '?>';
        $str .= $content;
        $str .= '<?php endforeach; ?>';
        $str .= '<?php }else{echo "<div class=\'fly-none\'>no related</div>";}?>';
        /*$str .= '<?php dump($result);?>';*/
        return $str;
    }

    public function tagTlist($attr,$content) {
        $db = $attr['db']; 
        $order = isset($attr['order'])?$attr['order']:'';    
        $limit = isset($attr['limit'])?$attr['limit']:''; 
        $id = $attr['id'];
        $key = isset($attr['key'])?$attr['key']:'k';
        $str = '<?php ';
        $str.='$result = db("'.$db.'")->where("'.$where.'")->limit('.$limit.')->order("'.$order.'")->select();';

        $str .= 'foreach ($result as $'.$key.'=>$'.$id.'):';
        $str .='$result[$'.$key.']["time"]= isset($'.$id.'["createtime"])?toDate($'.$id.'["createtime"]):""';
        $str .= '?>';
        $str .= $content;
        $str .= '<?php endforeach?>';
        return $str;
    }



}