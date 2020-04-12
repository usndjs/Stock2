<?php
namespace app\admin\controller;

use think\Db;
use wei\Wechat as Wechat;

class SmallBussin extends BaseWechat
{
	
	public function index()
	{
        if(request()->isPost()){
            $page =input('page')?input('page'):1;
            $pageSize =input('limit')?input('limit'):config('pageSize');
            $list=db('small_bussin')
                ->order('id desc')
                ->paginate(array('list_rows'=>$pageSize,'page'=>$page))
                ->toArray();
            foreach ($list['data'] as $k=>$v){
                $list['data'][$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            }
            return $result = ['code'=>0,'msg'=>'','data'=>$list['data'],'count'=>$list['total'],'rel'=>1];
        }
        return $this->fetch();
	}


	
	public function bussinAdd()
	{
		if(request()->isPost())
		{
			$data = input('post.');
			$a = Wechat()->applyEnter($data);
			var_dump($a);exit;
		}
		$system = Db::name('system')->field('mch_id,cert_sn')->find();
		$this->assign('system',$system);
		return $this->fetch();
	}

	
	public function createBusinessCode()
	{
		$osn = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
		return ['code'=>1,'msg'=>'','osn'=>$osn];
	}



    function random_str($length=16) {
      
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for($i = 0; $i < $length; $i++)
        {
           
     
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
//            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return ['code'=>1,'msg'=>'生成成功','str'=>$str];
    }

    //获取mediaid
    public function upload(){
        // 获取上传文件表单字段名
        $fileKey = array_keys(request()->file());
        // 获取表单上传文件
        $file = request()->file($fileKey['0']);
        // 移动到框架应用根目录/public/uploads/ 目录下

        $info = $file->validate(['ext' => 'jpg,png,gif,jpeg'])->move('uploads');
        if($info){
            $result['code'] = 1;
            $result['info'] = '图片上传成功!';
            $path=str_replace('\\','/',$info->getSaveName());
            $result['url'] = '/uploads/'. $path;
            $url = $_SERVER['HTTP_HOST'] . $result['url'];
            $aa = $this->media($url);
            var_dump($aa);exit;
            return $result;
        }else{
            // 上传失败获取错误信息

            $result['code'] =0;
            $result['info'] =  $file->getError();
            $result['url'] = '';
            return $result;
        }
    }
    
    public function media($media_addr)
    {
    	// $url = self::WXAPIHOST . 'secapi/mch/uploadmedia';
    	$url = 'https://api.mch.weixin.qq.com/secapi/mch/uploadmedia';
    	$data = [
            'mch_id' => '1563357881',
            'media_hash' => $this->hashMedia($media_addr),
        ];
        $data['sign_type'] = 'HMAC-SHA256';
        $data['sign'] = $this->makeSign($data, $data['sign_type']);
        // CURLFile 类的解释 http://php.net/manual/zh/class.curlfile.php
        $data['media'] = new \CURLFile($this->media_addr);
        $header = [
            "content-type:multipart/form-data",
        ];
        $res = $this->httpsRequest($url, $data, $header, true);
        var_dump($res); die;

        // 处理返回值
        $rt = $this->disposeReturn($res, ['media_id']);

        return $rt;
    }

    /**
     * httpsRequest  https请求（支持GET和POST）
     * @param $url
     * @param null $data
     * @return mixed
     */
    public function httpsRequest($url, $data = '', array $headers = [], $userCert = false, $timeout = 30)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        //设置超时
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        if ($userCert) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);//严格校验
            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
            list($sslCertPath, $sslKeyPath) = $this->getSSLCertPath();
            curl_setopt($curl, CURLOPT_SSLCERT, $sslCertPath);
            curl_setopt($curl, CURLOPT_SSLKEY, $sslKeyPath);
        } else {
            if (substr($url, 0, 5) == 'https') {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名
            }
        }
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            // curl_setopt($curl, CURLINFO_HEADER_OUT, true); //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
            // $headers = curl_getinfo($curl, CURLINFO_HEADER_OUT); //官方文档描述是“发送请求的字符串”，其实就是请求的header。这个就是直接查看请求header，因为上面允许查看
        }
        curl_setopt($curl, CURLOPT_HEADER, true);    // 是否需要响应 header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output          = curl_exec($curl);
        $header_size     = curl_getinfo($curl, CURLINFO_HEADER_SIZE);    // 获得响应结果里的：头大小
        $response_header = substr($output, 0, $header_size);    // 根据头大小去获取头信息内容
        $http_code       = curl_getinfo($curl, CURLINFO_HTTP_CODE);    // 获取响应状态码
        $response_body   = substr($output, $header_size);
        $error           = curl_error($curl);
        curl_close($curl);


        return [$response_body, $http_code, $response_header, $error];
    }

    /**
     * hashMedia 设置上传图片hash值
     * @param $media_addr
     * @param string $type
     * @return string
     */
    protected function hashMedia($media_addr, $type = 'md5')
    {
    	$media_addr = 'http://' . $media_addr;
        return hash_file($type, $media_addr);
    }

    /**
     * MakeSign 生成签名
     * @param $data
     * @param string $signType
     * @return string
     */
    public function makeSign(array $data, $signType = 'HMAC-SHA256')
    {

        //签名步骤一：按字典序排序参数
        ksort($data);

        $string = $this->toUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->diy_key;

        //签名步骤三：MD5加密或者HMAC-SHA256
        if ($signType == 'md5') {
            //如果签名小于等于32个,则使用md5验证
            $string = md5($string);
        } else {
            //是用sha256校验
            $string = hash_hmac("sha256", $string, $this->diy_key);
        }
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * ToUrlParams     格式化参数格式化成url参数
     * @param $data
     * @return string
     */
    protected function toUrlParams(array $data)
    {
        $buff = "";
        foreach ($data as $k => $v) {
            if ($k != "sign" && $v !== "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
}