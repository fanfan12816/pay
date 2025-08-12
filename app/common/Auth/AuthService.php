<?php

namespace app\common\Auth;

use app\model\AuthRule;
use app\model\AdminMember;

/**
 * 权限验证服务
 * Class AuthService
 * @package app\common\auth
 */
class AuthService {

  /**
   * 用户ID
   * @var null
   */
  protected $member_id = null;

  /**
   * 管理员信息
   * @var array|\think\Model|null
   */
  protected $MemberInfo;

  /**
   * 当前访问的节点信息
   * @var array
   */
  protected $RuleNode;

  /**
   * 管理员所有授权节点
   * @var array
   */
  protected $AdminNode;

  /**
   * 默认允许访问的接口
   * @var array
   */
  protected $AuthMethods = [
    // 登录
    'Login:LoginUser',
    // 退出
    'Login:Logout',
    // 图片验证码
    'Login:getCaptcha',
    // 获取管理员信息
    'Login:getUserInfo',
    // 获取菜单
    'AuthRule:getMenuList',
    'AuthRule:getAuthRuleList',
    // 文件上传
    'UploadFileService:UploadFile',
    
    'system.Language:theme',
  ];

  /***
   * 构造方法
   * AuthService constructor.
   * @param  null  $member_id
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\DbException
   * @throws \think\db\exception\ModelNotFoundException
   */
  public function __construct($member_id = null) {
    $this -> member_id = $member_id;
    $this -> MemberInfo = $this -> getAdminInfo();
    $this -> AdminNode = $this -> getAdminNode();
    $this -> RuleNode = $this -> getCurrentNode();
    return $this;
  }

  /**
   * 检测账户
   * @return bool
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\DbException
   * @throws \think\db\exception\ModelNotFoundException
   */
  public function CheckAdmin() {
      
    return $this -> MemberInfo;
  }

  /**
   * 检测权限
   * @return bool
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\DbException
   * @throws \think\db\exception\ModelNotFoundException
   */
  public function CheckAuth() {
    // 判断是否为超级管理员
    if ($this -> MemberInfo['auth_system'] == 1) {
      return true;
    }

    // 判断该节点是否允许访问
    if (in_array($this -> RuleNode, $this -> AdminNode)) {
      return true;
    }

    return false;
  }

  /**
   * 获取当前访问的节点
   * @return string
   */
  public function getCurrentNode() {
    $Node = request() -> controller() . ':' . request() -> action();
    return $Node;
  }

  /**
   * 获取管理员被授权的节点信息
   * @return array
   */
  public function getAdminNode() {
    $AuthNodeList =  AuthRule::where([
      ['id', 'in', $this -> MemberInfo['auth_permission']],
      ['rule_ismenu', 'in', 'menu,button'],
      ['rule_status', '=', 1]
    ]) -> column('rule_permission');

    return array_merge($this -> AuthMethods, $AuthNodeList);
  }

  /**
   * 获取管理员信息
   * @return array|\think\Model|null
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\DbException
   * @throws \think\db\exception\ModelNotFoundException
   */
  public function getAdminInfo() {
    return AdminMember::alias('a') -> join('admin_auth b', 'a.member_group = b.id') -> where(['a.member_id' => $this -> member_id]) -> field('a.*,b.auth_permission') -> find();
  }

}