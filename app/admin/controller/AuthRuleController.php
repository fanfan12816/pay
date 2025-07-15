<?php

namespace app\admin\controller;

use app\model\AuthRule;
use think\facade\Cache;
use app\AdminController;
use app\model\AdminGroup;
use app\model\AdminMember;
use hg\apidoc\annotation as Apidoc;
use think\exception\ValidateException;
use app\admin\validate\AuthRuleValidate;

/**
 * @Apidoc\Title("菜单管理")
 * Author: JackMater
 */
class AuthRuleController extends AdminController {

  /**
   * @Apidoc\Title("菜单列表")
   * @Apidoc\Desc("获取菜单列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getAuthRuleList")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="每页数据条数")
   * @Apidoc\Query("rule_title", type="string", desc="菜单名称")
   * @Apidoc\Query("rule_status", type="string", desc="菜单状态")
   * @Apidoc\Query("type", type="number",require=true,default=0, desc="菜单类型,0=管理后台,1=商户后台")
   * @Apidoc\Returned("auth_title", type="string", desc="菜单名称")
   * @Apidoc\Returned("auth_remark", type="string", desc="备注") 
   * @Apidoc\Returned("auth_status", type="number", desc="菜单状态")
   * @Apidoc\Returned("auth_sort", type="number", desc="排序")
   * @Apidoc\Returned("release_time", type="string", desc="修改时间")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   */
  public function getAuthRuleList() {
    $pageIndex         =  input('pageIndex'); # 分页页码
    $pageSize          =  input('pageSize'); # 每页数据条数
    $rule_title        =  input('rule_title'); # 菜单名称
    $rule_status       =  input('rule_status'); # 菜单状态
    $rule_ismenu       =  input('rule_ismenu'); # 菜单类型
    $type              =  input('type'); # 菜单类型

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }

    # 菜单名称
    if (!empty($rule_title)) {
      $data['a.rule_title']   =   $rule_title;
    }

    # 菜单类型
    if (!empty($rule_ismenu)) {
      $data['a.rule_ismenu']   =   $rule_ismenu;
    }

    # 菜单状态
    if (isset($rule_status)) {
      $data['a.rule_status']   =   $rule_status;
    }
    
    # 菜单类型
    if (isset($type)) {
      $data['a.type']   =   $type;
    }else{
      $data['a.type']   = 0;
    }
    # 获取授权数据
    
    $AuthRule = AdminMember::alias('a') -> join('admin_auth b', 'a.member_group = b.id') -> where(['a.member_id' => $this -> member_id]) -> value('b.auth_permission');
    $authWhere[]=["id","in",$AuthRule];
    if($data['a.type']==1){
        $authWhere=[];
    }
    
    if (!empty($data)) {
      # 获取数据
      $result = AuthRule::alias('a') -> join('admin_user b', 'a.admin_id = b.member_id') -> where($data) ->where($authWhere)-> field('a.*,a.rule_title AS label,a.rule_title AS title, a.id AS value,b.member_username AS admin_name') -> order('a.rule_sort', 'asc') -> select();
    } else {
      # 获取数据
      $result = AuthRule::alias('a') -> join('admin_user b', 'a.admin_id = b.member_id') ->where($authWhere) -> field('a.*,a.rule_title AS label,a.rule_title AS title, a.id AS value,b.member_username AS admin_name') -> order('a.rule_sort', 'asc') -> select();
    }

    foreach ($result as $key => $value) {
      $value['key'] = $value['id'];
    }

    if ($result) {
      return json(['code' => 1,'data' => ['data' => $result]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }
  }

  /**
   * @Apidoc\Title("修改菜单")
   * @Apidoc\Desc("修改菜单")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UpgradeAuthRule")
   * @Apidoc\Query("id", type="number", desc="数据ID")
   * @Apidoc\Query("auth_title", type="string", desc="菜单名称")
   * @Apidoc\Query("auth_remark", type="string", desc="备注") 
   * @Apidoc\Query("auth_status", type="number", desc="菜单状态")
   * @Apidoc\Query("auth_sort", type="number", desc="排序")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function UpgradeAuthRule() {
    $id                       =  input('id'); # 数据ID 
    $data['rule_pid']         =  input('rule_pid'); # 父ID
    $data['rule_title']       =  input('rule_title'); # 菜单名称
    $data['rule_permission']  =  input('rule_permission'); # 权限标识
    $data['rule_path']        =  input('rule_path'); # 路由地址
    $data['rule_component']   =  input('rule_component'); # 组件/页面路径
    $data['rule_icon']        =  input('rule_icon'); # 菜单图标
    $data['rule_condition']   =  input('rule_condition'); # 菜单条件
    $data['rule_remark']      =  input('rule_remark'); # 菜单备注
    $data['rule_ismenu']      =  input('rule_ismenu'); # 菜单类型
    $data['rule_keepalive']   =  input('rule_keepalive'); # 是否缓存
    $data['rule_show']        =  input('rule_show'); # 是否显示
    $data['rule_sort']        =  input('rule_sort'); # 菜单排序
    $data['rule_status']      =  input('rule_status'); # 菜单状态
    $data['admin_id']         =  $this -> member_id; # 管理员ID

    try {
      validate(AuthRuleValidate::class) -> scene('Upgrade') -> check(array_merge($data, ['id' => $id]));
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    // 如果是接口
    if ($data['rule_ismenu'] === 'button') {
      $data['rule_keepalive'] = 0;
      $data['rule_show'] = 0;
    }

    // 如果是目录或者是菜单
    if ($data['rule_ismenu'] == 'menu' || $data['rule_ismenu'] == 'catalogue') {
      // 如果没有输入菜单路由
      if (empty($data['rule_path'])) {
        return json(['code' => 0, 'data' => ['code' => 0, 'message' => '请输入菜单路由!']]);
      }
    }

    $Upgrade = AuthRule::where(['id' => $id]) -> update($data);

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '更新成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '更新失败!']]);
    }
  }

  /**
   * @Apidoc\Title("新增菜单")
   * @Apidoc\Desc("新增菜单")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/AddAuthRule")
   * @Apidoc\Query("auth_title", type="string", desc="菜单名称")
   * @Apidoc\Query("auth_remark", type="string", desc="备注") 
   * @Apidoc\Query("auth_status", type="number", desc="菜单状态")
   * @Apidoc\Query("type", type="number", desc="菜单类型,0=管理后台,1=商户后台")
   * @Apidoc\Query("auth_sort", type="number", desc="排序")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function AddAuthRule() {
    $data['rule_pid']         =  input('rule_pid'); # 父ID
    $data['rule_title']       =  input('rule_title'); # 菜单名称
    $data['rule_permission']  =  input('rule_permission'); # 权限标识
    $data['rule_path']        =  input('rule_path'); # 路由地址
    $data['rule_component']   =  input('rule_component'); # 组件/页面路径
    $data['rule_icon']        =  input('rule_icon'); # 菜单图标
    $data['rule_condition']   =  input('rule_condition'); # 菜单条件
    $data['rule_remark']      =  input('rule_remark'); # 菜单备注
    $data['rule_ismenu']      =  input('rule_ismenu'); # 菜单类型
    $data['rule_keepalive']   =  input('rule_keepalive'); # 是否缓存
    $data['rule_show']        =  input('rule_show'); # 是否显示
    $data['rule_sort']        =  input('rule_sort'); # 菜单排序
    $data['rule_status']      =  input('rule_status'); # 菜单状态
    $data['type']             =  input('type')??0; # 菜单类型
    $data['admin_id']         =  $this -> member_id; # 管理员ID

    try {
      validate(AuthRuleValidate::class) -> scene('Create') -> check($data);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    // 如果是接口
    if ($data['rule_ismenu'] === 'button') {
      $data['rule_keepalive'] = 0;
      $data['rule_show'] = 0;
    }

    // 如果是目录或者是菜单
    if ($data['rule_ismenu'] == 'menu' || $data['rule_ismenu'] == 'catalogue') {
      // 如果没有输入菜单路由
      if (empty($data['rule_path'])) {
        return json(['code' => 0, 'data' => ['code' => 0, 'message' => '请输入菜单路由!']]);
      }
    }

    $Create = AuthRule::create($data);

    if ($Create) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '新增成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '新增失败!']]);
    }
  }

  /**
   * @Apidoc\Title("删除菜单")
   * @Apidoc\Desc("删除菜单")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/DeleteAuthRule")
   * @Apidoc\Query("id", type="string", desc="数据ID")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function DeleteAuthRule() {
    $id          =  input('id'); # 数据ID
    $verify_code =  input('verify_code'); # 验证码

    try {
      validate(AuthRuleValidate::class) -> scene('Delete') -> check(['id' => $id]);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    $Upgrade = AuthRule::where(['id' => $id]) -> delete();

    if ($Upgrade) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => '删除成功!']]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '删除失败!']]);
    }
  }

  /**
   * @Apidoc\Title("获取树形菜单")
   * @Apidoc\Desc("获取树形菜单")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getMenuList")
   * @Apidoc\Returned("code", type="number", desc="1 成功 0 失败")
   */
  public function getMenuList() {

    // 获取当前管理员授权菜单ID
    $AuthRuleCode = AdminMember::alias('a') -> join('admin_auth b', 'a.member_group = b.id') -> where(['a.member_id' => $this -> member_id]) -> value('b.auth_permission');

    // 获取菜单列表
    $AuthRuleMenu = AuthRule::whereIn('id', $AuthRuleCode) -> where('rule_ismenu', '<>', 'button') -> order('rule_sort', 'asc') -> select();

    // 生成树形菜单
    $AuthRuleTreeMenu = $this -> getTreeMenu($AuthRuleMenu);

    if ($AuthRuleTreeMenu) {
      return json(['code' => 1,'data' => $AuthRuleTreeMenu]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }

  }

  /**
   * 获取权限ID
   */
  public function getPermCode() {

    $RoleList = $this -> getRoleList();

    if ($RoleList) {
      return json(['code' => 1,'data' => ['data' => $RoleList]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }

  }

  /**
   * 获取授权菜单数组
   */
  private function getRoleList() {
    return array_values($this->getRuleList());
  }

  /**
   * 获取树状菜单列表
   * @param $menuList
   * @param int $parentId
   * @return array
   */
  protected function getTreeMenu($menuList, int $pid = 0) {
    $data = [];
    foreach ($menuList as $key => $item) {
      if ($item['rule_pid'] == $pid) {

        $children = $this -> getTreeMenu($menuList, $item['id']);

        $route = [
          // 路由地址
          'path'                 =>  $item['rule_path'],
          // 路由名称
          'name'                 =>  $item['rule_title'],
          // 前端路由组件路径
          'component'            =>  $item['rule_component'],
          // Meta
          'meta'                 =>  [
            // 菜单名称
            'title'              =>  $item['rule_title'],
            // 是否开启路由缓存
            'ignoreKeepAlive'    =>  $item['rule_keepalive'] == 1 ? false : true,
            // 是否显示菜单
            'hideMenu'           =>  $item['rule_show'] == 1 ? false : true,
            // 菜单图标
            'icon'               =>  $item['rule_icon'],
            // 菜单排序
            'orderNo'            =>  $item['rule_sort'],
          ]
        ];

        // 如果是打开网址
        if (!empty($item['rule_path']) && substr($item['rule_path'], 0, 4) == 'http') {
          $route['meta']['frameSrc']   =   $item['rule_path'];
        }

        // 如果有子路由
        if (!empty($children)) {
          $route['redirect']     =   $item['rule_path'] . '/' . $children[0]['path'];
          $route['children']     =   $children;
        }

        $data[] = $route;
        unset($menuList[$key]);
      }
    }
    return $data;
  }

  /**
   * 获得权限规则列表
   * @param int $uid 用户id
   * @return array
   */
  private function getRuleList() {
    
    // 获取当前管理员授权菜单ID
    $AuthRule = AdminMember::alias('a') -> join('admin_auth b', 'a.member_group = b.id') -> where(['a.member_id' => $this -> member_id]) -> value('b.auth_permission');
    
    // 获取授权菜单列表
    $AuthRuleList = AuthRule::whereIn('id', $AuthRule) -> column('rule_title');

    return $AuthRuleList;
  }

}