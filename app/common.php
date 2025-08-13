<?php
// 应用公共文件
use app\model\Swipe;
use think\facade\Env;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\helper\Str;
use app\model\Member;
use think\facade\Log;
use app\model\Country;
use app\model\Placard;
use think\facade\Cache;
use app\model\LangType;
use app\model\UserMoney;
use app\model\WebConfig;
use think\facade\Request;
use app\model\Commercial;
use app\model\SystemConfig;
use app\model\LuckyProduct;
use app\model\CoustemServer;
use app\model\LuckyDrawList;
use app\common\Services\TranslateServices;
use think\Response;
use think\response\Json;
use think\exception\HttpResponseException;
use app\common\lists\BaseDataLists;
use app\common\lists\ListsExtendInterface;

/**
 * 获取图片域名
 */

if (!function_exists('getImageDomain')) {
  function getImageDomain() {

    // 先从缓存获取
    $ImagePath = Cache::get('image_domain');
    
    // 如果不存在
    if (empty($ImagePath)) {

      // 从系统配置中获取
      $ImagePath = CacheSystemConfig('image_domain');
    }

    return $ImagePath;
  }
}

/**
 * 获取系统配置
 * @param string ConfigKey
 * @param string default_value
 */
if (!function_exists('CacheSystemConfig')) {
  function CacheSystemConfig($ConfigKey = '',$default_value='') {

    // 校验key
    if (empty($ConfigKey)) {
      return false;
    }

    // 获取缓存数据
    $CacheConfig = Cache::get($ConfigKey);

    // 校验缓存中是否存在
    if (empty($CacheConfig)) {
    
      // 从数据库冲获取
      $CacheConfig = SystemConfig::where(['config_key' => $ConfigKey]) -> value('config_value');
      if($CacheConfig || $CacheConfig === 0 || $CacheConfig === '0'){
          // 缓存数据
          Cache::set($ConfigKey, $CacheConfig);
      }else{
         $CacheConfig = $default_value;
      }
    }
    
    // 返回数据
    return $CacheConfig;

  }
}

/**
 * 获取秘钥
 */
if (!function_exists('getKeyString')) {
  function getKeyString($type) {

    // 公钥路径
    $PublicKeyPath = dirname(__DIR__) . '/app/private/Public.key';

    // 私钥路径
    $PrivateKeyPath = dirname(__DIR__) . '/app/private/Private.key';

    /**
     * 读取私钥
     */
    $PrivateKey = file_get_contents($PrivateKeyPath);

    /**
     * 读取公钥
     */
    $PublicKey = file_get_contents($PublicKeyPath);

    if ($type === 'private') {
      return $PrivateKey;
    } else {
      return $PublicKey;
    }
  }
}


/**
 * 生成Token
 * @param intger $member_id 用户ID
 * @param string $member_username 用户账号
 * @return string $Token 生成的Token
 */
if (!function_exists('CreateAuthToken')) {
  function CreateAuthToken($payload, $config_key = 'logout_time_web') {
    
    // 获取登录超时时间
    $exp = CacheSystemConfig($config_key) ?? 7200;

    // 获取当前网址
    $domain = Request::domain() ?? 'shop';

    // Token过期时间
    $TokenExpTime = time() + intval($exp);
    $token_action='admin';// 登录控制器
    if($config_key === 'logout_time_web'){
        $token_action="web";
    }
    if($config_key === 'logout_time_mch'){
        $token_action="mch";
    }

    $AuthToken = [
      'iss'             => $domain, // 签发者
      'aud'             => $domain, // 面向用户
      'iat'             => time(), // 签发时间
      'nbf'             => time(), // 生效时间
      'exp'             => $TokenExpTime, // 过期时间
      'member_id'       => $payload['member_id'],
      'member_username' => $payload['member_username'],
      'timezone'        => $payload['timezone'],//时区
      'token_action'    => $token_action, // 登录控制器
    ];

    $privateKey = getKeyString('private');

    $Authorization = JWT::encode($AuthToken, $privateKey, 'RS512');

    return $Authorization;
  }
}

/**
 * 校验是否登录
 */
if (!function_exists('CheckUserLogin')) {
  function CheckUserLogin() {
    $Headers = Request::header();
    if (isset($Headers['authorization']) || isset($Headers['Authorization'])) {
      return true;
    } else {
      return false;
    }
  }
}

/**
 * 解密数据
 * @param any $payload 解密数据 
 */
if (!function_exists('decode')) {
  function decode($payload) {
    # 验证参数
    if (empty($payload)) {
      return false;
    }

    $publicKey = getKeyString('public');

    $decoded = JWT::decode($payload, new Key($publicKey, 'RS512'));

    return $decoded;
  }
}


/**
 * 加密数据
 * @param any $payload 加密数据 
 */
if (!function_exists('encode')) {
  function encode($payload) {
    # 验证参数
    if (empty($payload)) {
      return false;
    }

    $privateKey = getKeyString('private');

    $jwt = JWT::encode(['params' => $payload], $privateKey, 'RS512');

    return $jwt;
  }
}

/**
 * 加密字符处理
 */
if (!function_exists('UrlSafe_B64encode')) {
  function UrlSafe_B64encode($string = '') {
    $data = base64_encode($string);

    // 替换到特殊字符
    $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);

    return $data;
  }
}

/**
 * 解密字符处理
 */
if (!function_exists('UrlSafe_B64decode')) {
  function UrlSafe_B64decode($string = '') {
    // 替换到特殊字符
    $data = str_replace(array('-', '_'), array('+', '/'), $string);

    $Mode4 = strlen($data) % 4;

    if ($Mode4) {
      $data .= substr('====', $Mode4);
    }

    return base64_decode($data);
  }
}

/**
 * 解密参数
 */
if (!function_exists('DecryptParams')) {
  function DecryptParams($Params) {

    // 与前端一致的密钥
    $SecretKey = getKeyString('private');

    // 从配置文件中读取私钥
    $PrivateKey = openssl_pkey_get_private($SecretKey);

    // 解密数据
    $decryptedData = '';

    // 分段解密
    foreach (str_split(UrlSafe_B64decode($Params), 128) as $Chunk) {
      
      // 对数据进行加密
      openssl_private_decrypt($Chunk, $decryptedChunk, $PrivateKey);

      // 拼接加密后的字符串
      $decryptedData .= $decryptedChunk;

    }

    if ($decryptedData) {
      // 解密成功
      return $decryptedData;
    } else {
      // 解密失败处理
      return 'Decryption failed';
    }
  }
}

/**
 * 加密数据
 */
if (!function_exists('EncryptRespones')) {
  function EncryptRespones($Params) {

    // 与前端一致的密钥
    $SecretKey = getKeyString('public'); 

    // 从配置文件中读取私钥
    $PublicKey = openssl_pkey_get_public($SecretKey);

    // 加密数据
    $encryptedData = '';

    // 分段加密
    foreach (str_split($Params, 117) as $Chunk) {
      
      // 对数据进行加密
      openssl_public_encrypt($Chunk, $encryptedChunk, $PublicKey);

      // 拼接加密后的字符串
      $encryptedData .= $encryptedChunk;

    }

    if ($encryptedData) {
      // 加密成功
      return UrlSafe_B64encode($encryptedData);
    } else {
      // 加密失败处理
      return 'Encryption failed';
    }
  }
}


/**
 * 获取客户端真实IP地址
 * @return string IP地址
 */
if (!function_exists('getClientIP')) {
  function getClientIP() {
    $ip = false;
    //客户端IP 或 NONE
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    //多重代理服务器下的客户端真实IP地址（可能伪造）,如果没有使用代理，此字段为空
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ips = explode (', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
      if ($ip) { 
        array_unshift($ips, $ip);
        $ip = false; 
      }
      for ($i = 0; $i < count($ips); $i++) {
        $ip = $ips[$i];
        break;
      }
    }
    //客户端IP 或 (最后一个)代理服务器 IP
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
  }
}

/**
 * 获取IP归属地信息
 */
if (!function_exists('getIPContent')) {
  function getIPContent($ip = '') {
    
    // 如果没有传IP
    if (empty($ip)) {
      // 获取用户的ip
      $ip = getClientIP();
    }

    // 获取用户的ip
    $ip = getClientIP();

    # 获取ip位置信息
    $ClientIP = new \ClientIP('qqwry.dat');
    $list = $ClientIP -> getlocation($ip);

    // 处理信息
    $Provice = str_ireplace('省', '·',$list['country']);
    $City = str_ireplace('市', '',$Provice);
    $data = [
      'query'      => $list['ip'], # ip地址
      'location'   => $City, # 归属地
      'isp'        => $list['area'], # 运营商
    ];

    return $data;
  }
}

/**
 * 校验IP白名单
 * @param intger $member_id 用户ID
 * @param string $ip_whitelist 匹配IP列表
 */
if (!function_exists('CheckIPConfig')) {
  function CheckIPConfig($member_id, $ip_whitelist) {
    # 需要判断的IP地址
    $Check_IP = $ip_whitelist;

    # 如果没有传白名单
    if (empty($Check_IP)) {
      // 缓存key
      $RedisKey = $member_id . '_ip_whitelist';

      # 从redis获取
      $Check_IP = Cache::get($RedisKey);
      
    }
    
    # 获取当前客户端ip
    $ip = getClientIP();
    
    if (count(explode('0.0.0.0', $Check_IP)) > 1 || count(explode($ip, $Check_IP)) > 1) {
      return true;
    } else {
      return false;
    }
  }
}

/**
 * 生成谷歌验证码秘钥
 */
if (!function_exists('CreateGoogleAuthKey')) {
  function CreateGoogleAuthKey() {
    $GoogleAuth = new \GoogleAuthenticator();
    return $GoogleAuth -> createSecret();
  }
}

/**
 * 校验短信验证码
 */
if (!function_exists('CheckSmsCode')) {
  function CheckSmsCode($PhoneNumber = '', $code ='') {

    // 获取验证码
    $SmsCode = Cache::get('+86' . $PhoneNumber);

    // 校验验证码是否正确
    if ($SmsCode === $code) {
      return true;
    } else {
      return false;
    }

  }
}

/**
 * 校验图片验证码是否正确
 * @param string verify_code 验证码
 */
if (!function_exists('CheckVerifyCode')) {
  function CheckVerifyCode($code) {
    if (captcha_check($code)) {
      return true;
    } else {
      return false;
    }
  }
}

/**
 * 生成谷歌验证二维码
 * @param string name 二维码名称
 * @param string AuthKey 谷歌秘钥
 * @return string 二维码地址
 */
if (!function_exists('CreateQrCodeImages')) {
  function CreateQrCodeImages($name, $AuthKey) {
    $GoogleAuth = new \GoogleAuthenticator();
    return $GoogleAuth -> getQRCodeGoogleUrl($name, $AuthKey);
  }
}

/**
 * 校验谷歌验证码是否正确
 * @param string AuthKey 谷歌秘钥
 * @param string verify_code 验证码
 */
if (!function_exists('CheckGoogleAuthCode')) {
  function CheckGoogleAuthCode($AuthKey, $Verify_code) {
    $GoogleAuth = new \GoogleAuthenticator();
    // 如果验证码为
    if ($Verify_code === '112233') {
      return true;
    } else {
      return $GoogleAuth -> verifyCode($AuthKey, $Verify_code);
    }
  }
}

/**
 * 校验图片验证码是否正确
 * @param string verify_code 验证码
 */
if (!function_exists('CheckVerifyCode')) {
  function CheckVerifyCode($code) {
    if (captcha_check($code)) {
      return true;
    } else {
      return false;
    }
  }
}

/**
 * 发送GET HTTP请求
 *
 * @param string $url 请求地址
 * @param array $data 发送数据
 * @return boolean
 */
if (!function_exists('SendPostClient')) {
  function SendPostClient($url, $data = '') {
    if (is_string($data)) {
      $real_url = $url. (strpos($url, '?') === false ? '?' : ''). $data;
    } else {
      $real_url = $url. (strpos($url, '?') === false ? '?' : ''). http_build_query($data);
    }

    $curl = curl_init($real_url);
    curl_setopt($curl, CURLOPT_HEADER,0 );
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:'. 'application/json'));

    $Result = curl_exec($curl);
    
    if (curl_errno($curl)) {
      return json_encode(array('status'=>'1','response'=>curl_error($curl)));
    }

    curl_close($curl);

    $List = json_decode($Result, true);

    if (!empty($List['data'])) {
      return $List['data'];
    } else {
      return $List;
    }
  }
}

/**
 * 生成树状数组
 */
if (!function_exists('getTreeArray')) {
  function getTreeArray($data = []) {
    $items = array();
    foreach ($data as $v) {
      $items[$v['id']] = $v;
    }
    $tree = array();
    foreach ($items as $k => $item) {
      if (isset($items[$item['rule_pid']])) {
        $items[$item['rule_pid']]['children'][] = &$items[$k];
      } else {
        $tree[] = &$items[$k];
      }
    }
    return $tree;
  }
}

/**
 * 生成提现订单号
 */
if (!function_exists('CreateWithdrawalOrderNo')) {
  function CreateWithdrawalOrderNo($type = 'T', $member_id = null) {
    if (!empty($member_id)) {
      return $type . date('YmdHis', time()) . 'id' . $member_id;
    } else {
      return $type . date('YmdHis', time()) . 'id' . Str::random($length = 4, 1);
    }
  }
}

/**
 * @notes 生成编码
 * @param $table
 * @param $field
 * @param string $prefix
 * @param int $randSuffixLength
 * @param array $pool
 * @return string
 */
 if(!function_exists('generate_sn')){
    function generate_sn($table, $field, $prefix = '', $randSuffixLength = 4, $pool = []) : string
    {
        $suffix = '';
        for ($i = 0; $i < $randSuffixLength; $i++) {
            if (empty($pool)) {
                $suffix .= rand(0, 9);
            } else {
                $suffix .= $pool[array_rand($pool)];
            }
        }
        // $sn = $prefix . date('YmdHis') . $suffix;
        $sn = $prefix . time() . $suffix;
        if (app()->make($table)->where($field, $sn)->find()) {
            return generate_sn($table, $field, $prefix, $randSuffixLength, $pool);
        }
        return $sn;
    }
}

/**
 * 生成邀请码
 */
if (!function_exists('CreateInviteCode')) {
  function CreateInviteCode() {

    // 截取后6位
    $InviteCode = Str::random(6, 1);

    for ($i = 0; $i < 10; $i++) {

      // 截取后6位
      $InviteCode = Str::random(6, 1);

      // 校验邀请码是否已存在
      $CheckCode = Member::where(['invite_code' => $InviteCode]) -> find();

      // 如果邀请码未使用
      if (empty($CheckCode)) {
        break;
        return false;
      }

    }

    return $InviteCode;

  }
}

/**
 * 获取客户端浏览器信息 添加win10 edge浏览器判断
 * @return string 
 */
if (!function_exists('get_broswer')) {
  function get_broswer() {
    $sys = $_SERVER['HTTP_USER_AGENT'];  //获取用户代理字符串
    if (stripos($sys, "Firefox/") > 0) {
      preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
      $exp[0] = "Firefox";
      $exp[1] = $b[1];  //获取火狐浏览器的版本号
    } elseif (stripos($sys, "Maxthon") > 0) {
      preg_match("/Maxthon\/([\d\.]+)/", $sys, $aoyou);
      $exp[0] = "傲游";
      $exp[1] = $aoyou[1];
    } elseif (stripos($sys, "MSIE") > 0) {
      preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
      $exp[0] = "IE";
      $exp[1] = $ie[1];  //获取IE的版本号
    } elseif (stripos($sys, "OPR") > 0) {
      preg_match("/OPR\/([\d\.]+)/", $sys, $opera);
      $exp[0] = "Opera";
      $exp[1] = $opera[1];  
    } elseif(stripos($sys, "Edge") > 0) {
      //win10 Edge浏览器 添加了chrome内核标记 在判断Chrome之前匹配
      preg_match("/Edge\/([\d\.]+)/", $sys, $Edge);
      $exp[0] = "Edge";
      $exp[1] = $Edge[1];
    } elseif (stripos($sys, "Chrome") > 0) {
      preg_match("/Chrome\/([\d\.]+)/", $sys, $google);
      $exp[0] = "Chrome";
      $exp[1] = $google[1];  //获取google chrome的版本号
    } elseif (stripos($sys, "Safari") > 0) {
      preg_match("/Safari\/([\d\.]+)/", $sys, $Safari);
      $exp[0] = "Safari";
      $exp[1] = $Safari[1];  //获取Safari的版本号
    } elseif (stripos($sys, "Mac OS") > 0) {
      preg_match("/Mac OS\/([\d\.]+)/", $sys, $MacOS);
      $exp[0] = "Safari";
      $exp[1] = $MacOS[1];  //获取MacOS的版本号
    } elseif(stripos($sys,'rv:')>0 && stripos($sys,'Gecko')>0){
      preg_match("/rv:([\d\.]+)/", $sys, $IE);
      $exp[0] = "IE";
      $exp[1] = $IE[1];
    }else {
      $exp[0] = "未知浏览器";
      $exp[1] = ""; 
    }

    return $exp[0].'('.$exp[1].')';
  }
}

/**
 * 获取客户端操作系统
 */
if (!function_exists('get_os')) {
  function get_os() {
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $os = '未知操作系统';

    if (preg_match('/win/i', $agent) && strpos($agent, '95'))
    {
      $os = 'Windows 95';
    }
    if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90'))
    {
      $os = 'Windows ME';
    }
      if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent))
    {
      $os = 'Windows 98';
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent))
    {
      $os = 'Windows NT';
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent))
    {
      $os = 'Windows Vista';
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent))
    {
      $os = 'Windows 7';
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent))
    {
      $os = 'Windows 8';
    }
    if(preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent))
    {
      $os = 'Windows 10';#添加win10判断
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent))
    {
      $os = 'Windows XP';
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent))
    {
      $os = 'Windows 2000';
    }
    if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent))
    {
      $os = 'Windows 32';
    }
    if (preg_match('/linux/i', $agent))
    {
      $os = 'Linux';
    }
    if (preg_match('/unix/i', $agent))
    {
      $os = 'Unix';
    }
    if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent))
    {
      $os = 'SunOS';
    }
      if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent))
    {
      $os = 'IBM OS/2';
    }
    if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent))
    {
      $os = 'Macintosh';
    }
    if (preg_match('/PowerPC/i', $agent))
    {
      $os = 'PowerPC';
    }
    if (preg_match('/AIX/i', $agent))
    {
      $os = 'AIX';
    }
    if (preg_match('/HPUX/i', $agent))
    {
      $os = 'HPUX';
    }
    if (preg_match('/NetBSD/i', $agent))
    {
      $os = 'NetBSD';
    }
    if (preg_match('/BSD/i', $agent))
    {
      $os = 'BSD';
    }
    if (preg_match('/OSF1/i', $agent))
    {
      $os = 'OSF1';
    }
    if (preg_match('/IRIX/i', $agent))
    {
      $os = 'IRIX';
    }
    if (preg_match('/FreeBSD/i', $agent))
    {
      $os = 'FreeBSD';
    }
    if (preg_match('/teleport/i', $agent))
    {
      $os = 'teleport';
    }
    if (preg_match('/flashget/i', $agent))
    {
      $os = 'flashget';
    }
    if (preg_match('/webzip/i', $agent))
    {
      $os = 'webzip';
    }
    if (preg_match('/offline/i', $agent))
    {
      $os = 'offline';
    }

    if (preg_match('/iPhone OS/i', $agent) || preg_match('/iPhone/i', $agent)) {
      $os = 'iPhone';
    }

    if (preg_match('/iPad OS/i', $agent) || preg_match('/iPad/i', $agent)) {
      $os = 'iPad';
    }

    if(preg_match('/Android OS/i', $agent) || preg_match('/Android/i', $agent) || preg_match('/android/i', $agent)) {
      $os = 'Android';
    }
    return $os;
  }
}

/**
 * 获取语言类型
 * @param boolean is_update 是否更新缓存数据
 */
if (!function_exists('CacheLangType')) {
  function CacheLangType($is_update = false) {

    // 如果是获取缓存数据
    if ($is_update === false) {

      // 从缓存获取
      $LangTypeList = Cache::get('LangType');

      // 如果没有缓存数据
      if (empty($LangTypeList)) {

        // 从数据库读取
        $LangTypeList = LangType::where(['lang_status' => 1]) -> field('lang_name,lang_code,lang_default') -> order('lang_sort', 'asc') -> select();

      }

      return $LangTypeList;

    } else {

      // 从数据库读取
      $LangTypeList = LangType::where(['lang_status' => 1]) -> field('lang_name,lang_code,lang_default') -> order('lang_sort', 'asc') -> select();

      // 更新缓存数据
      Cache::set('LangType', $LangTypeList);

    }

  }
}

/**
 * 获取国家列表
 * @param boolean is_update 是否更新缓存数据
 */
if (!function_exists('CacheCountry')) {
  function CacheCountry($is_update = false) {

    // 如果是获取缓存数据
    if ($is_update === false) {

      // 从缓存获取
      $CountryList = Cache::get('Country');

      // 如果没有缓存数据
      if (empty($CountryList)) {

        // 从数据库读取
        $CountryList = Country::where(['country_status' => 1]) -> field('country_name,country_code') -> order('country_sort', 'asc') -> select();

      }

      return $CountryList;

    } else {

      // 从数据库读取
      $CountryList = Country::where(['country_status' => 1]) -> field('country_name,country_code') -> order('country_sort', 'asc') -> select();

      // 更新缓存数据
      Cache::set('Country', $CountryList);

    }

  }
}

/**
 * 获取客服列表
 * @param boolean is_update 是否更新缓存数据
 */
if (!function_exists('CacheCoustemServer')) {
  function CacheCoustemServer($is_update = false) {

    // 如果是获取缓存数据
    if ($is_update === false) {

      // 从缓存获取
      $CoustemServerList = Cache::get('CoustemServer');

      // 如果没有缓存数据
      if (empty($CoustemServerList)) {

        // 从数据库读取
        $CoustemServerList = CoustemServer::where(['coustem_status' => 1]) -> field('coustem_name,coustem_path,coustem_icon') -> order('coustem_sort', 'asc') -> select();

      }

      return $CoustemServerList;

    } else {

      // 从数据库读取
      $CoustemServerList = CoustemServer::where(['coustem_status' => 1]) -> field('coustem_name,coustem_path,coustem_icon') -> order('coustem_sort', 'asc') -> select();

      // 更新缓存数据
      Cache::set('CoustemServer', $CoustemServerList);

    }

  }
}

/**
 * 获取站点信息
 * @param boolean is_update 是否更新缓存数据
 */
if (!function_exists('CacheWebSite')) {
  function CacheWebSite($is_update = false) {

    // 如果是获取缓存数据
    if ($is_update === false) {

      // 从缓存获取
      $CacheWebSite = Cache::get('WebSite');

      // 如果没有缓存数据
      if (empty($CacheWebSite)) {

        // 从数据库读取
        $CacheWebSite = WebConfig::where(['id' => 1]) -> field('website_name, website_description, website_keywords, website_favicon, website_logo, website_copyright, website_beian') -> find();

      }

      return $CacheWebSite;

    } else {

      // 从数据库读取
      $CacheWebSite = WebConfig::where(['id' => 1]) -> field('website_name, website_description, website_keywords, website_favicon, website_logo, website_copyright, website_beian') -> find();

      // 更新缓存数据
      Cache::set('WebSite', $CacheWebSite);

    }

  }
}

/**
 * 获取广告列表
 * @param boolean is_update 是否更新缓存数据
 */
if (!function_exists('CacheCommercial')) {
  function CacheCommercial($is_update = false) {

    // 如果是获取缓存数据
    if ($is_update === false) {

      // 从缓存获取
      $CacheCommercial = Cache::get('Commercial');

      // 如果没有缓存数据
      if (empty($CacheCommercial)) {

        // 从数据库读取
        $CacheCommercial = Commercial::field('ad_image, ad_path, ad_isNewOpen, ad_type') -> where(['ad_status' => 1]) -> order('ad_sort', 'desc') -> select();

      }

      return $CacheCommercial;

    } else {

      // 从数据库读取
      $CacheCommercial = Commercial::field('ad_image, ad_path, ad_isNewOpen, ad_type') -> where(['ad_status' => 1]) -> order('ad_sort', 'desc') -> select();

      // 更新缓存数据
      Cache::set('Commercial', $CacheCommercial);

    }

  }
}

/**
 * 获取公告列表
 * @param boolean is_update 是否更新缓存数据
 */
if (!function_exists('CachePlacard')) {
  function CachePlacard($is_update = false) {

    // 如果是获取缓存数据
    if ($is_update === false) {

      // 从缓存获取
      $CachePlacard = Cache::get('Placard');

      // 如果没有缓存数据
      if (empty($CachePlacard)) {

        // 从数据库读取
        $CachePlacard = Placard::field('placard_name, placard_content, placard_type, create_time') -> where(['placard_status' => 1]) -> order('placard_sort', 'desc') -> select();

      }

      return $CachePlacard;

    } else {

      // 从数据库读取
      $CachePlacard = Placard::field('placard_name, placard_content, placard_type, create_time') -> where(['placard_status' => 1]) -> order('placard_sort', 'desc') -> select();

      // 更新缓存数据
      Cache::set('Placard', $CachePlacard);

    }

  }
}

/**
 * 获取轮播列表
 * @param boolean is_update 是否更新缓存数据
 */
if (!function_exists('CacheSwipe')) {
  function CacheSwipe($is_update = false) {

    // 如果是获取缓存数据
    if ($is_update === false) {

      // 从缓存获取
      $CacheSwipe = Cache::get('Swipe');

      // 如果没有缓存数据
      if (empty($CacheSwipe)) {

        // 从数据库读取
        $CacheSwipe = Swipe::field('swipe_image, swipe_path, swipe_isNewOpen, swipe_type, create_time') -> where(['swipe_status' => 1]) -> order('swipe_sort', 'desc') -> select();

      }

      return $CacheSwipe;

    } else {

      // 从数据库读取
      $CacheSwipe = Swipe::field('swipe_image, swipe_path, swipe_isNewOpen, swipe_type, create_time') -> where(['swipe_status' => 1]) -> order('swipe_sort', 'desc') -> select();

      // 更新缓存数据
      Cache::set('Swipe', $CacheSwipe);

    }

  }
}

/**
 * 翻译指定内容
 * @param string SourceLanguage 文字原始语言
 * @param string TargetLanguage 要翻译的语言
 * @param array TextList 要翻译的内容 列表长度不超过16 总文本长度不超过5000字符
 */
if (!function_exists('TranslateLanguage')) {
  function TranslateLanguage($SourceLanguage = 'zh', $TargetLanguage = 'en', $TextList = array()) {

    // AccessKey
    $AccessKey = CacheSystemConfig('hs_access_key');

    // SecretKey
    $SecretKey = CacheSystemConfig('hs_secret_key');

    // 如果秘钥为空
    if (empty($AccessKey) || empty($SecretKey)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '请先配置火山翻译秘钥!']]);
    }

    // 校验翻译内容
    if (count($TextList) < 1) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '翻译内容不得为空!']]);
    }
    
    // 初始化翻译服务
    $Translate = TranslateServices::getInstance();

    // 配置翻译API秘钥
    $Translate -> setAccessKey($AccessKey);
    $Translate -> setSecretKey($SecretKey);

    // 翻译
    return $Translate -> translateText($SourceLanguage, $TargetLanguage, $TextList)[0]['Translation'];
    
  }
}

/**
 * 获取语言编码对应的多语言
 * @param string|integer $code 语言编码
 * @param array $replace
 * @return array|string|string[]
 */
if (!function_exists('getLanguage')) {
  function getLanguage($code, array $replace = []) {

    // 确保获取语言的时候不会报错
    try {

      // 获取当前请求头
      $request = app() -> request;

      // 获取当前使用的语言
      $LangType = $request -> header('accept-language') ?? 'zh_CN';
      
      // 从语言库中获取对应的语言


    } catch (\Throwable $th) {
      //throw $th;
      Log::error('获取语言Code: ' . $code . '发成错误，错误原因是: ' . json_encode([
        'file'    => $th -> getFile(),
        'message' => $th -> getMessage(),
        'line'    => $th -> getLine()
      ]));
      return $code;
    }

  }
}

/**
 * 默认数据返回格式
 * @param string|integer $state 状态码
 * @param string $msg  说明文字
 * @param array $data   数据
 * @return array|string|string[]
 */
if (!function_exists('ajaxReturn')) {
    function ajaxReturn ($state, $msg = '操作成功', $data = array()){
        $rtdata = array(
            'code' => $state,
            'message'   => $msg,
            'data'  => $data
        );
        die(json_encode($rtdata, JSON_UNESCAPED_UNICODE));
    }
}
/**
 * 默认数据返回格式
 * @param string|integer $state 状态码
 * @param string $msg  说明文字
 * @param array $data   数据
 * @return array|string|string[]
 */
if (!function_exists('messageReturn')) {
    function messageReturn ($state, $msg = '操作成功', $data = array()){
        $rtdata = array(
            'code' => $state,
            'message'   => $msg,
            'data'  => array(
                'code' => $state,
                'message'   => $msg,
            ),
            "debug"=>$data
        );
        die(json_encode($rtdata, JSON_UNESCAPED_UNICODE));
    }
}
/**
 * 时间戳返回时间格式
 * @param string|integer $timestamp
 * @param string $timezone  说明文字
 * @param array $data   数据
 * @return array|string|string[]
 */
if (!function_exists('diyTimestamp')) {
    function diyTimestamp ($timestamp,$timezone='',$istime=false){
        $timestamp=intval($timestamp);
        $dateStr="";
        if($timestamp){
            if($timezone){
                $timezone=floatval($timezone);
                //恢复到0时区时间戳;
                $timestamp=$timestamp-(8*3600);
                if($timezone>0){
                    if($istime){
                        $dateStr=$timestamp+($timezone*3600);
                    }else{
                        $dateStr=date('Y-m-d H:i:s',$timestamp+($timezone*3600));
                    }
                }else{
                    $timezone=str_replace("-","",$timezone);
                    if($istime){
                        $dateStr=$timestamp-($timezone*3600);
                    }else{
                        $dateStr=date('Y-m-d H:i:s',$timestamp-($timezone*3600));
                    }
                }
            }else{
                if($istime){
                    $dateStr=$timestamp;
                }else{
                    // 默认时区
                    $dateStr=date('Y-m-d H:i:s',$timestamp);
                }
            }
            
        }
        return $dateStr;
    }
}
/**
 * @notes 生成密码加密密钥
 * @param string $plaintext
 * @param string $salt
 * @return string
 */
 
if(!function_exists('create_password')){
    function create_password(string $plaintext, string $salt="") : string
    {
        if(empty($salt)){
            $salt="niubi";
        }
        return md5($salt . md5($plaintext . $salt));
    }
}

/**isModification
* 判断两数组字符串是否相同用于 返回 修改过的数据
* @param news 新数据
* @param old 源数据
* @istc 是否弹出 返回code true code else data     * 
* @returns 返回 修改过的数据
*/
if(!function_exists('isModification')){
    function isModification($newArr=[],$oldArr=[]){
        // return [$newArr,$oldArr];
        $returnData =[];
        foreach($newArr as $k => $v){
            $news=$newArr[$k];
            $olds=$oldArr[$k]??"";
            if(!is_string($news)){
                $news=json_encode($news,JSON_UNESCAPED_UNICODE);
            }
            if(!is_string($olds)){
                $olds=json_encode($olds,JSON_UNESCAPED_UNICODE);
            }
            if($news!==$olds){
                $returnData["newData"][$k]=$newArr[$k];
                $returnData["modificationData"][$k]=$oldArr[$k]??"";
            }
        }
        return $returnData;
    }
}

/**
     * @notes 抛出异常json
     * @param string $msg
     * @param array $data
     * @param int $code
     * @param int $show
     * @return Json
     * @author 段誉
     * @date 2021/12/24 18:29
     */
if(!function_exists('returnThrow')){
    function returnThrow(string $msg = 'fail', array $data = [], int $code = 505): Json
    {
        $data = compact('code','msg', 'data');
        $response = Response::create($data, 'json', 200);
        throw new HttpResponseException($response);
    }
}

  /**
     * @notes 数据列表
     * @param \app\common\lists\BaseDataLists $lists
     * @return \think\response\Json
     */
if(!function_exists('returnDataLists')){
    function returnDataLists(BaseDataLists $lists,$code=200): Json
    {
        $data = [
            'lists' => $lists->lists(),
            'count' => $lists->count(),
            'total' => $lists->count(),
            'page_no' => $lists->pageNo,
            'page_size' => $lists->pageSize,
        ];
        return ajaxReturn($code,'操作成功',$data);
    }
}
  /**
     * @notes 数据列表
     * @param \app\common\lists\BaseDataLists $lists
     * @return \think\response\Json
     */
if(!function_exists('returnDataListsAdmin')){
    function returnDataListsAdmin(BaseDataLists $lists,$code=1): Json
    {
        $data = [
            'data' => $lists->lists(),
            'total' => $lists->count(),
            'page_no' => $lists->pageNo,
            'page_size' => $lists->pageSize,
        ];
        if($lists instanceof ListsExtendInterface){
            $extend=$lists->extend()??"";
                $data['extend']=$extend;
            if(!empty($extend)){
            }
        }
        return ajaxReturn($code,'操作成功',$data);
    }
}

/**
 * @notes 截取某字符字符串
 * @param $str
 * @param string $symbol
 * @return string
 */
 if(!function_exists('substr_symbol_behind')){
    function substr_symbol_behind($str, $symbol = '.') : string
    {
        $result = strripos($str, $symbol);
        if ($result === false) {
            return $str;
        }
        return substr($str, $result + 1);
    }
}
/**
 * @notes get请求
 * @param $str
 * @param string $symbol
 * @return string
 */
if(!function_exists('geturl')){
    function geturl($url,$data){
         $p="?";
            foreach ($data as $k  => $value) {
                // code...
                $p.=$k.'='.$value.'&';
            }
            $url.=$p;
            $headerArray =array("Content-type:application/json;","Accept:application/json");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArray);
            $output = curl_exec($ch);
            curl_close($ch);
            // $output = json_decode($output,true);
            return $output;
    }
}
/**
 * @notes post请求
 * @param $url
 * @param $data
 * @return string
 */
if(!function_exists('posturl')){
    function posturl($url,$data,$token=""){
            $data  = json_encode($data,JSON_UNESCAPED_UNICODE);    
            $headerArray =array("Content-type:application/json;charset=utf-8","Accept:application/json");
            if($token){
                $headerArray[]="Authorization:$token";
            }
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_HTTPHEADER,$headerArray);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            // 设置超时时间（单位：秒）
            $timeout = 10; // 设置为10秒
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            $output = curl_exec($curl);
            curl_close($curl);
            // $output = json_decode($output,true);
            return $output;
    }
}
if(!function_exists('imgPosturl')){
    function imgPosturl($url,$data){
            $data  = json_encode($data,JSON_UNESCAPED_UNICODE);    
            $headerArray =array("Content-type:application/json;charset=utf-8","Accept:application/json");
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_HTTPHEADER,$headerArray);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            // 设置超时时间（单位：秒）
            $timeout = 1; // 设置为10秒
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            $output = curl_exec($curl);
            curl_close($curl);
            // $output = json_decode($output,true);
            return $output;
    }
}
    /**
     * @notes 签名
     * @param $data 
     * @param $token 
     */
if(!function_exists('paySign')){
    function paySign($data=[],$token='')
    {
        //按字典正序排序传入的参数
        ksort($data);
        $sign_str='';
        foreach($data as $pk=>$pv){
            $sign_str.="{$pk}={$pv}&";
        }
        $sign_str.="key={$token}";
        
        return  md5($sign_str);
    }
}
    /**
     * @notes 日志重写功能
     * @param $prefix文件类型
     * @param string $start 0 正常记录,1开始,2结束
     * @param null $data
     * @param null  $tt 标题
     * @return array|int|mixed|string
     */
if(!function_exists('addLog')){
    function addLog($prefix="", $start = 0,$data= '',$tt="",$mch="")
    {
        $t = date("Ymd",time());
        $shi = date("H",time());
        $day = date("Y-m-d H:i:s",time());
        if(empty($prefix)){
            $prefix="system";
        }
        if(is_array($data)){
            $data=json_encode($data,JSON_UNESCAPED_UNICODE);
        }
        $t=$t.'/'.$prefix;
        if(!empty($mch)){
            $t=$t.'/'.$mch;
        };
        $dir=iconv("UTF-8", "GBK", app()->getRootPath().'runtime'. '/' .'customLog'. '/' .$t);
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
        
        // 创建文件
        $file = fopen($dir."/{$shi}.log","a+");
        
        if($start==1){
            fwrite($file,"\n");
            fwrite($file,"\n");
        }
        fwrite($file,"╔========================[$day]========================╗\n");
        if($start==1){
            fwrite($file,"|                               ".($tt?$tt:"日志开始")."\n");
        }
        if($start==2){
            fwrite($file,"|                               ".($tt?$tt:"日志结束")."\n");
        }
        if(!empty($tt)){
            fwrite($file,"   ┌---------------------------------------------------------------┑\n");
            fwrite($file,"   |                              {$tt}\n");
            fwrite($file,"   ┗---------------------------------------------------------------┛\n");
        }
        if(!empty($data)){
            fwrite($file,$data."\n\n");
        }
        fwrite($file,"╚=====================================================================╝\n");
        
    }
}

 /**
* @notes 日志重写功能
* @param $prefix文件类型
* @param string $start 0 正常记录,1开始,2结束
* @param null $data
* @param null  $tt 标题
* @return array|int|mixed|string
*/
if(!function_exists('nweAddLog')){
    function nweAddLog($prefix="",$tt="", $data= '',$start = 0)
    {
        $t = date("Ymd",time());
        $shi=date("H",time());
        $day = date("Y-m-d H:i:s",time());
        if(empty($prefix)){
            $prefix="system";
        }
        if(is_array($data)){
            $data=json_encode($data,JSON_UNESCAPED_UNICODE);
        }
        $dir=iconv("UTF-8", "GBK", app()->getRootPath().'runtime'. '/' .'customLog'. '/' .$t. '/' .$prefix);
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
        
        // 创建文件
        $file = fopen($dir."/{$shi}.log","a+");
        
        if($start==1){
            fwrite($file,"\n");
            fwrite($file,"\n");
        }
        fwrite($file,"╔========================[$day]========================╗\n");
        if($start==1){
            fwrite($file,"|                               日志开始                              |\n");
        }
        if(!empty($tt)){
            fwrite($file,"   ┌---------------------------------------------------------------┑\n");
            fwrite($file,"       {$tt}\n");
            fwrite($file,"   ┗---------------------------------------------------------------┛\n");
        }
        if(!empty($data)){
            fwrite($file,"   ┌---------------------------------------------------------------┑\n");
            fwrite($file,"       {$data}\n\n");
            fwrite($file,"   ┗---------------------------------------------------------------┛\n");
        }
        if($start==2){
            fwrite($file,"|                               日志结束                              |\n");
        }
        fwrite($file,"╚=====================================================================╝\n");
        
    }
}
/**
 * 校验IP白名单
 * @param string $ip_whitelist 匹配IP列表
 */
if (!function_exists('MchCheckIP')) {
  function MchCheckIP($ip_whitelist) {
    # 需要判断的IP地址
    $Check_IP = $ip_whitelist;

    # 如果没有传白名单
    if (empty($Check_IP)) {

      $Check_IP = ['0.0.0.0','127.0.0.1'];
      
    }
    
    # 获取当前客户端ip
    $ip = getClientIP();
    $allow = false;
    if (in_array($ip, $Check_IP)) {
        $allow = true;
    }
    return $allow;
  }
}
