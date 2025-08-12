<?php

namespace app\api\controller;

use app\BaseController;
use app\common\Services\TranslateServices;

class IndexController extends BaseController {

  public function index() {

    // AccessKey
    $AccessKey = 'AKLTMGI2YjIxZmExZDk3NDQyYmE0MzJjMGY3NWVhZDQ3OTU';

    // SecretKey
    $SecretKey = 'TIRka1pqazVOV1k1T0RWb5EaGIPV0kxTIdJMk1ETXIaVEk0WVRZd1ItSQ==';
    
    // 初始化翻译服务
    $translator = TranslateServices::getInstance();

    // 配置翻译API秘钥
    $translator -> setAccessKey($AccessKey);
    $translator -> setSecretKey($SecretKey);

    // 需要翻译的内容
    $TransText = array('测试');

    // 翻译
    $Result = $translator -> translateText('zh', 'en', $TransText);

    return json($Result);

  }

}