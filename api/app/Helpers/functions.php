<?php
function durationFormat($seconds)
{
    if (empty($seconds)) {
        return '';
    }
    if ($seconds > 3600){
        $hours = intval($seconds/3600);
        $minutes = $seconds % 3600;
        $time = $hours.":".gmstrftime('%M:%S', $minutes);
    }else{
        $time = gmstrftime('%M:%S', $seconds);
    }
    return $time;
}

/**
 * 返回会员到期时间
 */
function dealVipEndTime($vip_end_time)
{
    if (empty($vip_end_time) || (strtotime(date('Y-m-d', $vip_end_time).' 23:59:59') < time())){
        return 0;
    }
    return date('Y-m-d', $vip_end_time);
}

/**
 * 判断是否为vip
 */
function isVip($vip_end_time)
{
    if (!empty($vip_end_time) && (strtotime(date('Y-m-d', $vip_end_time).' 23:59:59') > time())){
        return 1;
    }
    return 0;
}

/**
 * 处理数量超过万的用w
 */
function dealNum($num)
{
    if ($num >= 10000) {
        return round($num/10000, 1) . 'w';
    } else {
        return (string)$num;
    }
}

/**
 * 格式化时间
 */
function formatDate($date)
{
    $t = time()-strtotime($date);
    $f = array(
        '31536000'=>'年',
        '2592000'=>'个月',
        '604800'=>'星期',
        '86400'=>'天',
        '3600'=>'小时',
        '60'=>'分钟',
        '1'=>'秒'
    );
    foreach ($f as $k=>$v)    {
        if (0 !=$c=floor($t/(int)$k)) {
            return $c.$v.'前';
        }
    }
}

/**
 * 获取截取视频切片的链接
 */
function getShortVideoUrl($url)
{
    if (empty($url) || strstr($url, 'http') || strstr($url, 'https')) {
        return $url;
    } elseif (strstr($url, 'qiniu_')) {
        $config = App\Models\Config::getConfig('upload');
        $qiniu_domain = $config['upload_qiniu_domain'];
        $url = $qiniu_domain . $url;
        $upload_qiniu_video_segment = $config['upload_qiniu_video_segment'];
        $result = json_decode(curl($url . $upload_qiniu_video_segment) ,true);
        if (isset($result['result'])) {
            $short_url = $result['result'][0]['segment_filename'];
            return $qiniu_domain . $short_url;
        } else {
            return $url;
        }
    } elseif (strstr($url, 'aliyun_')) {
        $config = App\Models\Config::getConfig('upload');
        $aliyun_domain = $config['upload_aliyun_domain'];
        return $aliyun_domain . $url;
    } else {
        return request()->root() . '/' . $url;
    }
}

/**
 * 拼接图片地址
 */
function dealUrl($url)
{
    if (empty($url) || strstr($url, 'http') || strstr($url, 'https')) {
        return $url;
    } elseif (strstr($url, 'qiniu_')) {
        $config = App\Models\Config::getConfig('upload');
        $qiniu_domain = $config['upload_qiniu_domain'];
        return $qiniu_domain . $url;
    } elseif (strstr($url, 'aliyun_')) {
        $config = App\Models\Config::getConfig('upload');
        $aliyun_domain = $config['upload_aliyun_domain'];
        return $aliyun_domain . $url;
    } else {
        return request()->root() . '/' . $url;
    }
}

/**
 * 拼接图片地址
 */
function dealAvatar($url)
{
    if (empty($url)) {
        $config = App\Models\Config::getConfig('base');
        $url = $config['base_default_avatar'];
    }
    if (empty($url) || strstr($url, 'http') || strstr($url, 'https')) {
        return $url;
    } elseif (strstr($url, 'qiniu_')) {
        $config = App\Models\Config::getConfig('upload');
        $qiniu_domain = $config['upload_qiniu_domain'];
        return $qiniu_domain . $url;
    } elseif (strstr($url, 'aliyun_')) {
        $config = App\Models\Config::getConfig('upload');
        $aliyun_domain = $config['upload_aliyun_domain'];
        return $aliyun_domain . $url;
    } else {
        return request()->root() . '/' . $url;
    }
}

/**
 * 生成邀请码
 */
function createRefcode()
{
    $str = range('A', 'Z');
    $strs = range('a', 'z');
    unset($str[array_search('O', $str)]);
    unset($strs[array_search('o', $strs)]);
    $arr = array_merge(range(0, 9), $str, $strs);
    shuffle($arr);
    $invitecode = '';
    $arr_len = count($arr);
    for ($i = 0; $i < 6; $i++) {
        $rand = mt_rand(0, $arr_len - 1);
        $invitecode .= $arr[$rand];
    }
    return $invitecode;
}

/**
 * 生成不重复的随机数字
 * @param  int $start  需要生成的数字开始范围
 * @param  int $end    结束范围
 * @param  int $length 需要生成的随机数个数
 * @return number      生成的随机数
 */
function getRandNumber($start=0,$end=9,$length=8)
{
	//初始化变量为0
	$connt = 0;
	//建一个新数组
	$temp = array();
	while($connt < $length){
	//在一定范围内随机生成一个数放入数组中
	$temp[] = mt_rand($start, $end);
	//$data = array_unique($temp);
	//去除数组中的重复值用了“翻翻法”，就是用array_flip()把数组的key和value交换两次。这种做法比用 array_unique() 快得多。
	$data = array_flip(array_flip($temp));
	//将数组的数量存入变量count中
	$connt = count($data);
	}
	//为数组赋予新的键名
	shuffle($data);
	//数组转字符串
	$str=implode(",", $data);
	//替换掉逗号
	$number=str_replace(',', '', $str);
	return '1' . $number;
}

/**
 * 生产唯一单号
 */
function genRequestSn($unique=0)
{
    $orderNo = date('YmdHis').substr(microtime(), 2, 5) . mt_rand(10000,99999);
    if(!empty($unique)) $orderNo = $orderNo.$unique;
    return $orderNo;
}

/**
 * 判断手机号
 * @param $phone
 * @return bool
 */
function checkPhoneValidate($phone){
    $g = "/^1[34578]\d{9}$/";
    $g2 = "/^19[89]\d{8}$/";
    $g3 = "/^166\d{8}$/";
    if (preg_match($g, $phone)) {
        return true;
    } else if (preg_match($g2, $phone)) {
        return true;
    } else if (preg_match($g3, $phone)) {
        return true;
    }
    return false;
}

/**
 * @action curl获取数据
 * @param string
 * @return array
 */
function curl($url, $post = '',$headers = array(), $timeout = 10)
{
    $headerArr = array();
    foreach( $headers as $n => $v ) {
        $headerArr[] = $n .':' . $v;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (!empty($post)) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
