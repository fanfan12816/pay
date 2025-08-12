<?php

namespace app\mch\controller;

use app\model\AuthRule;
use app\model\AdminGroup;
use think\facade\Cache;
use app\MchController;
use app\common\model\Merchant;
use hg\apidoc\annotation as Apidoc;
use think\exception\ValidateException;

/**
 * @Apidoc\Title("菜单管理")
 * Author: JackMater
 */
class AuthRuleController extends MchController {

  /**
   * @Apidoc\Title("获取树形菜单")
   * @Apidoc\Desc("获取树形菜单")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/getMenuList")
   * @Apidoc\Returned("code", type="number", desc="200 成功 500 失败")
   */
  public function getMenuList() {

    // 获取菜单列表
    // $AuthRuleMenu = AuthRule::where('type',"=", 1)->where("rule_status","=",1) -> where('rule_ismenu', '<>', 'button') -> order('rule_sort', 'asc') -> select();
    // AdminGroup::where("auth_code","=","mch") -> value('b.auth_permission');
    // 获取当前管理员授权菜单ID
    $AuthRuleCode = AdminGroup::where("auth_code","=","mch") -> value('auth_permission');

    // 获取菜单列表
    $AuthRuleMenu = AuthRule::whereIn('id', $AuthRuleCode) -> where('rule_ismenu', '<>', 'button') -> order('rule_sort', 'asc') -> select();

    // 生成树形菜单
    $AuthRuleTreeMenu = $this -> getTreeMenu($AuthRuleMenu);

    if ($AuthRuleTreeMenu) {
      return json(['code' => 200,'data' => $AuthRuleTreeMenu]);
    } else {
      return json(['code' => 500,  'message' => '没有找到你要的数据!']);
    }

  }

   /**
   * @Apidoc\Title("获取权限ID")
   * @Apidoc\Desc("获取权限ID")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/getPermCode")
   * @Apidoc\Returned("code", type="number", desc="200 成功 500 失败")
   */
  public function getPermCode() {

    $RoleList = $this -> getRoleList();

    if ($RoleList) {
      return json(['code' => 200,'data' => ['data' => $RoleList]]);
    } else {
      return json(['code' => 500,  'message' => '没有找到你要的数据!']);
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
    // $AuthRule = AdminMember::alias('a') -> join('admin_auth b', 'a.member_group = b.id') -> where(['a.member_id' => $this -> member_id]) -> value('b.auth_permission');
    $AuthRule = AdminGroup::where("auth_code","=","mch") -> value('auth_permission');
    
    // 获取授权菜单列表
    // $AuthRuleList = AuthRule::where('type',"=", 1)->where("rule_status","=",1) -> column('rule_title');
    // 获取授权菜单列表
    $AuthRuleList = AuthRule::whereIn('id', $AuthRule) ->where("rule_status","=",1) -> column('rule_title');

    return $AuthRuleList;
  }

}