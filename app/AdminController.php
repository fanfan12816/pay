<?php
declare (strict_types = 1);

namespace app;

use think\App;
use think\Validate;
use think\exception\ValidateException;

/**
 * 控制器基础类
 */
abstract class AdminController {
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
   * 用户ID
   */
  protected $member_id;

  /**
   * 用户名
   */
  protected $member_username;

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
    // 获取IP属地信息
    $this -> City = getIPContent();

    // 如果已登录
    if (CheckUserLogin()) {
      
      // 截取Bearer 前戳
      $AuthToken = str_ireplace('Bearer ', '', $this -> request -> header('Authorization'));

      // 解密
      $payload = decode($AuthToken);

      // 用户ID
      $this -> member_id = $payload -> member_id;

      // 手机号
      $this -> member_username = $payload -> member_username;
    }
  }

  /**
   * 验证数据
   * @access protected
   * @param  array        $data     数据
   * @param  string|array $validate 验证器名或者验证规则数组
   * @param  array        $message  提示信息
   * @param  bool         $batch    是否批量验证
   * @return array|string|true
   * @throws ValidateException
   */
  protected function validate(array $data, string | array $validate, array $message = [], bool $batch = false) {
    if (is_array($validate)) {
      $v = new Validate();
      $v -> rule($validate);
    } else {
      if (strpos($validate, '.')) {
        // 支持场景
        [$validate, $scene] = explode('.', $validate);
      }
      $class = false !== strpos($validate, '\\') ? $validate : $this -> app -> parseClass('validate', $validate);
      $v     = new $class();
      if (!empty($scene)) {
        $v -> scene($scene);
      }
    }

    $v -> message($message);

    // 是否批量验证
    if ($batch || $this -> batchValidate) {
      $v -> batch(true);
    }

    return $v -> failException(true) -> check($data);
  }
}