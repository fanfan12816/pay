<?php

namespace app\common\Auth;

use app\model\{AuthRule,AdminGroup};
use app\common\model\Merchant;

/**
 * 权限验证服务
 * Class AuthService
 * @package app\common\auth
 */
class MchAuthService {

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
    'Login:Login',
    // 退出
    'Login:Logout',
    'Login:Test',
    // 图片验证码
    'Login:getCaptcha',
    // 获取管理员信息
    'Login:getLoginInfo',
    // 获取菜单
    'AuthRule:getMenuList',
    // 文件上传
    'UploadFileService:UploadFile',
    // 代付银行卡列表
    'Order:payoutBankLists',
    // 代收银行卡列表
    'Order:payinBankLists',
    // 获取通道列表
    'Order:channelLists',
    // 获取通道列表
    'System:webSite',
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
   * 检测权限
   * @return bool
   * @throws \think\db\exception\DataNotFoundException
   * @throws \think\db\exception\DbException
   * @throws \think\db\exception\ModelNotFoundException
   */
  public function CheckAuth() {

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
        $User = Merchant::where(['id' => $this -> member_id ])->field("*")->findOrEmpty();
        
        $AuthRule = AdminGroup::where("auth_code","=","mch") -> value('auth_permission');
        // 获取授权菜单列表
        $AuthRoleList = AuthRule::where(['rule_status' => 1]) -> where('type', '=',1) -> where('id', 'in', $AuthRule) -> field('id') -> select();
        
        $User["login_time"]=diyTimestamp($User["login_time"],$User["timezone"]);
        $User["update_time"]=diyTimestamp($User["update_time"],$User["timezone"]);
        $User["create_time"]=diyTimestamp($User["create_time"],$User["timezone"]);
        
        $AuthRole="";
        foreach ($AuthRoleList as $v){
            $AuthRole.= $v['id'].",";
        }
        $AuthRole=substr($AuthRole,0,-1);
        $User["auth_permission"]=$AuthRole;
        return $User;
  }

}