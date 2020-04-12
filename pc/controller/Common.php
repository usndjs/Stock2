<?php
namespace app\pc\controller;
//use think\Db;
use think\Controller;
class Common extends Controller
{
    public function initialize()
    {
    	$is_mobile = $this->isMobile();
        if($is_mobile){
            return redirect('http://www.thinkphp.cn');
        }
    }

    public function isMobile() {
       
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
      
        if (isset($_SERVER['HTTP_VIA'])) {
        
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile', 'MicroMessenger');
            
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        
        if (isset($_SERVER['HTTP_ACCEPT'])) {
          
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
}