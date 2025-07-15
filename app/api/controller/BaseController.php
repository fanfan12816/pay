<?php
declare (strict_types = 1);

namespace app\api\controller;

use think\App;
use think\Validate;
use app\common\model\Merchant;
use think\exception\ValidateException;

/**
 * 控制器基础类
 */
abstract class BaseController {
  /**
   * Request实例
   * @var \think\Request
   */
  protected $request;

  /**
   * 应用实例
   * @var \think\App
   */
  protected $app;

  /**
   * 是否批量验证
   * @var bool
   */
  protected $batchValidate = false;

  /**
   * 控制器中间件
   * @var array
   */
  protected $middleware = [];

  /**
   * IP属地
   */
  public $City = [];

  /**
   * 商户信息
   */
  protected $MchInfo;

  /**
   * 构造方法
   * @access public
   * @param  App  $app  应用对象
   */
  public function __construct(App $app) {
    $this -> app     = $app;
    $this -> request = $this -> app -> request;

    // 控制器初始化
    $this -> initialize();
  }

  // 初始化
  protected function initialize() {

    header('Access-Control-Allow-Origin: *');
    // 获取IP属地信息
    $this -> City = getIPContent();

    // 如果已登录
    if (CheckUserLogin()) {
      
        // 截取Bearer 前戳
        $AuthToken = $this -> request -> header('Authorization');
        $field = [
            'id', 'sn','nick_name',"frozen_capital",'account','debug','money','timezone','secret_key','disable','ip_white'
        ];
        // return ajaxReturn(200,'测试',[$field,$this->mchid]);
       
        $User = Merchant::where(['secret_key' => $AuthToken])->field($field)->findOrEmpty();
      
        // 用户ID
        $this -> MchInfo = $User;
    }
  }
}