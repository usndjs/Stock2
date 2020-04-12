<?php

function savecache($name = '', $id = '')
{
    if ($name == 'Field') {
        if ($id) {
            $Model = db($name);
            $data  = $Model->order('sort')->where('moduleid=' . $id)->column('*', 'field');
            $name  = $id . '_' . $name;
            $data  = $data ? $data : null;
            cache($name, $data);
        } else {
            $module = cache('Module');
            foreach ($module as $key => $val) {
                savecache($name, $key);
            }
        }
    } elseif ($name == 'System') {
        $Model = db($name);
        $list  = $Model->where(array('id' => 1))->find();
        cache($name, $list);
    } elseif ($name == 'Module') {
        $Model     = db($name);
        $list      = $Model->order('sort')->select();
        $pkid      = $Model->getPk();
        $data      = array();
        $smalldata = array();
        foreach ($list as $key => $val) {
            $data[$val[$pkid]]       = $val;
            $smalldata[$val['name']] = $val[$pkid];
        }
        cache($name, $data);
        cache('Mod', $smalldata);
    } elseif ($name == 'cm') {
        $list = db('category')
            ->alias('c')
            ->join('module m', 'c.moduleid = m.id')
            ->order('c.sort')
            ->field('c.*,m.title as mtitle,m.name as mname')
            ->select();
        cache($name, $list);
    } else {
        $Model = db($name);
        $list  = $Model->order('sort')->select();
        $pkid  = $Model->getPk();
        $data  = array();
        foreach ($list as $key => $val) {
            $data[$val[$pkid]] = $val;
        }
        cache($name, $data);
    }
    return true;
}


function style($title_style)
{
    $title_style = explode(';', $title_style);
    return $title_style[0] . ';' . $title_style[1];
}

function callback($status = 0, $msg = '', $url = null, $data = '')
{
    $data = array(
        'msg'    => $msg,
        'url'    => $url,
        'data'   => $data,
        'status' => $status,
    );
    return $data;
}

function getvalidate($info)
{
    $validate_data = array();
    if ($info['minlength']) {
        $validate_data['minlength'] = ' minlength:' . $info['minlength'];
    }

    if ($info['maxlength']) {
        $validate_data['maxlength'] = ' maxlength:' . $info['maxlength'];
    }

    if ($info['required']) {
        $validate_data['required'] = ' required:true';
    }

    if ($info['pattern']) {
        $validate_data['pattern'] = ' ' . $info['pattern'] . ':true';
    }

    $errormsg = '';
    if ($info['errormsg']) {
        $errormsg = ' title="' . $info['errormsg'] . '"';
    }
    $validate = implode(',', $validate_data);
    $validate = 'validate="' . $validate . '" ';
    $parseStr = $validate . $errormsg;
    return $parseStr;
}
function string2array($info)
{
    if ($info == '') {
        return array();
    }

    eval("\$r = $info;");
    return $r;
}
function array2string($info)
{
    if ($info == '') {
        return '';
    }

    if (!is_array($info)) {
        $string = stripslashes($info);
    }
    foreach ($info as $key => $val) {
        $string[$key] = stripslashes($val);
    }
    $setup = var_export($string, true);
    return $setup;
}

function getform($form, $info, $value = '')
{
    $type = $info['type'];
    return $form->$type($info, $value);
}

function byte_format($input, $dec = 0)
{
    $prefix_arr = array("B", "KB", "MB", "GB", "TB");
    $value      = round($input, $dec);
    $i          = 0;
    while ($value > 1024) {
        $value /= 1024;
        $i++;
    }
    $return_str = round($value, $dec) . $prefix_arr[$i];
    return $return_str;
}
function toDate($time, $format = 'Y-m-d H:i:s')
{
    if (empty($time)) {
        return '';
    }
    $format = str_replace('#', ':', $format);
    return date($format, $time);
}

function toCity($id)
{
    if (empty($id)) {
        return '';
    }
    $name = db('region')->where(['id' => $id])->value('name');
    return $name;
}
function template_file($module = '')
{
    $viewPath   = config('template.view_path');
    $viewSuffix = config('template.view_suffix');
    $viewPath   = $viewPath ? $viewPath : 'view';
    $filepath   = think\facade\Env::get('app_path') . strtolower(config('default_module')) . '/' . $viewPath . '/';
    $tempfiles  = dir_list($filepath, $viewSuffix);
    $arr        = [];
    foreach ($tempfiles as $key => $file) {
        $dirname = basename($file);
        if ($module) {
            if (strstr($dirname, $module . '_')) {
                $arr[$key]['value']    = substr($dirname, 0, strrpos($dirname, '.'));
                $arr[$key]['filename'] = $dirname;
                $arr[$key]['filepath'] = $file;
            }
        } else {
            $arr[$key]['value']    = substr($dirname, 0, strrpos($dirname, '.'));
            $arr[$key]['filename'] = $dirname;
            $arr[$key]['filepath'] = $file;
        }
    }
    return $arr;
}
function dir_list($path, $exts = '', $list = array())
{
    $path  = dir_path($path);
    $files = glob($path . '*');
    foreach ($files as $v) {
        $fileext = fileext($v);
        if (!$exts || preg_match("/\.($exts)/i", $v)) {
            $list[] = $v;
            if (is_dir($v)) {
                $list = dir_list($v, $exts, $list);
            }
        }
    }
    return $list;
}
function dir_path($path)
{
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/') {
        $path = $path . '/';
    }

    return $path;
}
function fileext($filename)
{
    return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
}
function checkField($table, $value, $field)
{
    $count = db($table)->where(array($field => $value))->count();
    if ($count > 0) {
        return true;
    } else {
        return false;
    }
}

function rand_string($len = 6, $type = '', $addChars = '')
{
    $str = '';
    switch ($type) {
        case 0:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 1:
            $chars = str_repeat('0123456789', 3);
            break;
        case 2:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3:
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 4:
            break;
        default:
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
            break;
    }
    if ($len > 10) {

        $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
    }
    if ($type != 4) {
        $chars = str_shuffle($chars);
        $str   = substr($chars, 0, $len);
    } else {
        
        for ($i = 0; $i < $len; $i++) {
            $str .= msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
        }
    }
    return $str;
}


function is_email($user_email)
{
    $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
    if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false) {
        if (preg_match($chars, $user_email)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}


function is_mobile_phone($mobile_phone)
{   

    $chars = "/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$/";
    if (preg_match($chars, $mobile_phone)) {
        return true;
    }
    return false;
}

function getIp()
{
    if (@$_SERVER['HTTP_CLIENT_IP'] && $_SERVER['HTTP_CLIENT_IP'] != 'unknown') {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (@$_SERVER['HTTP_X_FORWARDED_FOR'] && $_SERVER['HTTP_X_FORWARDED_FOR'] != 'unknown' && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match('/^\d[\d.]+\d$/', $ip) ? $ip : '';
}


function str_cut($sourcestr, $cutlength, $suffix = '...')
{
    $returnstr  = '';
    $i          = 0;
    $n          = 0;
    $str_length = strlen($sourcestr);
    while (($n < $cutlength) and ($i <= $str_length)) {
        $temp_str = substr($sourcestr, $i, 1);
        $ascnum   = Ord($temp_str); 
        if ($ascnum >= 224) 
        {
            $returnstr = $returnstr . substr($sourcestr, $i, 3); 
            $i         = $i + 3; 
            $n++; 
        } elseif ($ascnum >= 192) 
        {
            $returnstr = $returnstr . substr($sourcestr, $i, 2); 
            $i         = $i + 2; 
            $n++; 
        } elseif ($ascnum >= 65 && $ascnum <= 90) 
        {
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i         = $i + 1; 
            $n++; 
        } else 
        {
            $returnstr = $returnstr . substr($sourcestr, $i, 1);
            $i         = $i + 1; 
            $n         = $n + 0.5; 
        }
    }
    if ($n > $cutlength) {
        $returnstr = $returnstr . $suffix; 
    }
    return $returnstr;
}

function dir_delete($dir)
{
    $dir = dir_path($dir);
    if (!is_dir($dir)) {
        return false;
    }

    $list = glob($dir . '*');
    foreach ($list as $v) {
        is_dir($v) ? dir_delete($v) : @unlink($v);
    }
    return @rmdir($dir);
}

function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false)
{
    $method = strtoupper($method);
    $ci     = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); 
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); 
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); 
            break;
    }
    $ssl = preg_match('/^https:\/\//i', $url) ? true : false;
    curl_setopt($ci, CURLOPT_URL, $url);
    if ($ssl) {
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false); 
    }
    //curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2); 
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response    = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code   = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
    //return array($http_code, $response,$requestinfo);
}

function convert_arr_key($arr, $key_name)
{
    $arr2 = array();
    foreach ($arr as $key => $val) {
        $arr2[$val[$key_name]] = $val;
    }
    return $arr2;
}

function getCity($ip = '')
{
    $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);
    if (empty($res)) {return false;}
    $jsonMatches = array();
    preg_match('#\{.+?\}#', $res, $jsonMatches);
    if (!isset($jsonMatches[0])) {return false;}
    $json = json_decode($jsonMatches[0], true);
    if (isset($json['ret']) && $json['ret'] == 1) {
        $json['ip'] = $ip;
        unset($json['ret']);
    } else {
        return false;
    }
    return $json;
}

function imgUrl($img, $defaul = '')
{
    if ($img) {
        if (substr($img, 0, 4) == 'http') {
            $imgUrl = $img;
        } else {
            $imgUrl = $img;
        }
    } else {
        if ($defaul) {
            $imgUrl = $defaul;
        } else {
            $imgUrl = '/static/admin/images/tong.png';
        }

    }
    return $imgUrl;
}

function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) {
        $size /= 1024;
    }

    return round($size, 2) . $delimiter . $units[$i];
}

function isMobile()
{
   
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }

    
    if (isset($_SERVER['HTTP_VIA'])) {
     
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
   
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');
        
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

function is_weixin()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

function is_qq()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'QQ') !== false) {
        return true;
    }
    return false;
}
function is_alipay()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'AlipayClient') !== false) {
        return true;
    }
    return false;
}


function get_user_info($user_id_or_name, $type = 0, $oauth = '')
{
    $map = array();
    if ($type == 0) {
        $map[] = ['user_id', '=', $user_id_or_name];
    }
    if ($type == 1) {
        $map[] = ['email', '=', $user_id_or_name];
    }
    if ($type == 2) {
        $map[] = ['mobile', '=', $user_id_or_name];
    }
    if ($type == 3) {
        $map[] = ['openid', '=', $user_id_or_name];
        $map[] = ['oauth', '=', $oauth];
    }
    if ($type == 4) {
        $map[] = ['unionid', '=', $user_id_or_name];
        $map[] = ['oauth', '=', $oauth];
    }
    if ($type == 5) {
        $map[] = ['nickname', '=', $user_id_or_name];
    }
    $user = db('users')->where($map)->find();
    return $user;
}

function trim_array_element($array)
{
    if (!is_array($array)) {
        return trim($array);
    }

    return array_map('trim_array_element', $array);
}

function convert_arr_kv($arr, $key_name, $value)
{
    $arr2 = array();
    foreach ($arr as $key => $val) {
        $arr2[$val[$key_name]] = $val[$value];
    }
    return $arr2;
}

function send_email($to, $subject = '', $content = '')
{
    $mail   = new PHPMailer\PHPMailer\PHPMailer();
    $arr    = db('config')->where('inc_type', 'smtp')->select();
    $config = convert_arr_kv($arr, 'name', 'value');

    $mail->CharSet = 'UTF-8'; 
    $mail->isSMTP();
    $mail->SMTPDebug = 0;
 
    $mail->Host = $config['smtp_server'];
   
    $mail->Port = $config['smtp_port'];

    if ($mail->Port == '465') {
        $mail->SMTPSecure = 'ssl';
    } 
 
    $mail->SMTPAuth = true;
 
    $mail->Username = $config['smtp_user'];

    $mail->Password = $config['smtp_pwd'];
  
    $mail->setFrom($config['smtp_user'], $config['email_id']);
 

    if (is_array($to)) {
        foreach ($to as $v) {
            $mail->addAddress($v);
        }
    } else {
        $mail->addAddress($to);
    }

    $mail->isHTML(true); // send as HTML
    //标题
    $mail->Subject = $subject;
    //HTML内容转换
    $mail->msgHTML($content);
    return $mail->send();
}
function safe_html($html)
{
    $elements = [
        'html'       => [],
        'body'       => [],
        'a'          => ['target', 'href', 'title', 'class', 'style'],
        'abbr'       => ['title', 'class', 'style'],
        'address'    => ['class', 'style'],
        'area'       => ['shape', 'coords', 'href', 'alt'],
        'article'    => [],
        'aside'      => [],
        'audio'      => ['autoplay', 'controls', 'loop', 'preload', 'src', 'class', 'style'],
        'b'          => ['class', 'style'],
        'bdi'        => ['dir'],
        'bdo'        => ['dir'],
        'big'        => [],
        'blockquote' => ['cite', 'class', 'style'],
        'br'         => [],
        'caption'    => ['class', 'style'],
        'center'     => [],
        'cite'       => [],
        'code'       => ['class', 'style'],
        'col'        => ['align', 'valign', 'span', 'width', 'class', 'style'],
        'colgroup'   => ['align', 'valign', 'span', 'width', 'class', 'style'],
        'dd'         => ['class', 'style'],
        'del'        => ['datetime'],
        'details'    => ['open'],
        'div'        => ['class', 'style'],
        'dl'         => ['class', 'style'],
        'dt'         => ['class', 'style'],
        'em'         => ['class', 'style'],
        'font'       => ['color', 'size', 'face'],
        'footer'     => [],
        'h1'         => ['class', 'style'],
        'h2'         => ['class', 'style'],
        'h3'         => ['class', 'style'],
        'h4'         => ['class', 'style'],
        'h5'         => ['class', 'style'],
        'h6'         => ['class', 'style'],
        'header'     => [],
        'hr'         => [],
        'i'          => ['class', 'style'],
        'img'        => ['src', 'alt', 'title', 'width', 'height', 'id', 'class'],
        'ins'        => ['datetime'],
        'li'         => ['class', 'style'],
        'mark'       => [],
        'nav'        => [],
        'ol'         => ['class', 'style'],
        'p'          => ['class', 'style'],
        'pre'        => ['class', 'style'],
        's'          => [],
        'section'    => [],
        'small'      => [],
        'span'       => ['class', 'style'],
        'sub'        => ['class', 'style'],
        'sup'        => ['class', 'style'],
        'strong'     => ['class', 'style'],
        'table'      => ['width', 'border', 'align', 'valign', 'class', 'style'],
        'tbody'      => ['align', 'valign', 'class', 'style'],
        'td'         => ['width', 'rowspan', 'colspan', 'align', 'valign', 'class', 'style'],
        'tfoot'      => ['align', 'valign', 'class', 'style'],
        'th'         => ['width', 'rowspan', 'colspan', 'align', 'valign', 'class', 'style'],
        'thead'      => ['align', 'valign', 'class', 'style'],
        'tr'         => ['rowspan', 'align', 'valign', 'class', 'style'],
        'tt'         => [],
        'u'          => [],
        'ul'         => ['class', 'style'],
        'video'      => ['autoplay', 'controls', 'loop', 'preload', 'src', 'height', 'width', 'class', 'style'],
        'embed'      => ['src', 'height', 'align', 'width', 'class', 'style', 'type', 'pluginspage', 'wmode', 'play', 'loop', 'menu', 'allowscriptaccess', 'allowfullscreen'],
        'source'     => ['src', 'type'],
    ];
    $html = strip_tags($html, '<' . implode('><', array_keys($elements)) . '>');
    $xml  = new \DOMDocument();
    libxml_use_internal_errors(true);
    if (!strlen($html)) {
        return '';
    }
    if ($xml->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $html)) {
        foreach ($xml->getElementsByTagName("*") as $element) {
            if (!isset($elements[$element->tagName])) {
                $element->parentNode->removeChild($element);
            } else {
                for ($k = $element->attributes->length - 1; $k >= 0; --$k) {
                    if (!in_array($element->attributes->item($k)->nodeName, $elements[$element->tagName])) {
                        $element->removeAttributeNode($element->attributes->item($k));
                    } elseif (in_array($element->attributes->item($k)->nodeName, ['href', 'src', 'style', 'background', 'size'])) {
                        $_keywords = ['javascript:', 'javascript.:', 'vbscript:', 'vbscript.:', ':expression'];
                        $find      = false;
                        foreach ($_keywords as $a => $b) {
                            if (false !== strpos(strtolower($element->attributes->item($k)->nodeValue), $b)) {
                                $find = true;
                            }
                        }
                        if ($find) {
                            $element->removeAttributeNode($element->attributes->item($k));
                        }
                    }
                }
            }
        }
    }
    $html = substr($xml->saveHTML($xml->documentElement), 12, -14);
    $html = strip_tags($html, '<' . implode('><', array_keys($elements)) . '>');
    return $html;
}


function is_use_mobile($mobile,$uid = '')
{   
    if ($uid) {
        $map[] = ['user_name','=',$mobile];
        $map[] = ['id','!=',$uid];
    }else{
        $map[] = ['user_name','=',$mobile];
    }
    $mobile = db('members')->where($map)->value('id');
    if ($mobile) {
        return true;
    }
    return false;
}

