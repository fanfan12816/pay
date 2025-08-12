<?php

namespace app\admin\controller;

use think\facade\Cache;
use app\AdminController;
use app\model\UserMoney;
use app\model\InterFacelog;
use hg\apidoc\annotation as Apidoc;
use think\exception\ValidateException;

/**
 * @Apidoc\Title("日志管理")
 * Author: JackMater
 */
class InterFaceController extends AdminController {

  /**
   * @Apidoc\Title("接口日志列表")
   * @Apidoc\Desc("获取接口日志列表")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getInerFaceLogList")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="每页数据条数")
   * @Apidoc\Query("interface_type", type="string", desc="接口应用名")
   * @Apidoc\Query("interface_method", type="string", desc="请求类型")
   * @Apidoc\Query("interface_ip", type="string", desc="客户端IP地址")
   * @Apidoc\Returned("interface_path", type="string", desc="接口地址")
   * @Apidoc\Returned("interface_type", type="string", desc="接口应用名") 
   * @Apidoc\Returned("interface_method", type="string", desc="请求类型")
   * @Apidoc\Returned("interface_headers", type="string", desc="请求头内容")
   * @Apidoc\Returned("interface_re_header", type="string", desc="响应头内容")
   * @Apidoc\Returned("interface_params", type="string", desc="请求参数")
   * @Apidoc\Returned("interface_respones", type="string", desc="响应内容")
   * @Apidoc\Returned("interface_ip", type="string", desc="客户端IP地址")
   * @Apidoc\Returned("interface_city", type="string", desc="客户端IP属地")
   * @Apidoc\Returned("create_time", type="string", desc="创建时间")
   */
  public function getInerFaceLogList() {

    $pageIndex         =  input('pageIndex'); # 分页页码
    $pageSize          =  input('pageSize'); # 每页数据条数
    $interface_type    =  input('interface_type'); # 接口应用名
    $interface_method  =  input('interface_method'); # 请求类型
    $interface_ip      =  input('interface_ip'); # 客户端IP地址

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }

    # 接口应用名
    if (!empty($interface_type)) {
      $data['interface_type']   =   $interface_type;
    }

    # 请求类型
    if (!empty($interface_method)) {
      $data['interface_method']   =   $interface_method;
    }

    # 客户端IP地址
    if (isset($interface_ip)) {
      $data['interface_ip']   =   $interface_ip;
    }
    
    if (!empty($data)) {
      # 获取数据
      $result = InterFacelog::where($data) -> page($pageIndex, $pageSize) -> order('create_time', 'desc') -> select();

      # 总页数
      $total = InterFacelog::where($data) -> count();
    } else {
      # 获取数据
      $result = InterFacelog::page($pageIndex, $pageSize) -> order('create_time', 'desc') -> select();

      # 总页数
      $total = InterFacelog::count();
    }

    if ($result) {
      return json(['code' => 1,'data' => ['data' => $result, 'total' => $total]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }

  }

  /**
   * @Apidoc\Title("余额日志")
   * @Apidoc\Desc("获取余额日志")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/getFinancialRecords")
   * @Apidoc\Query("pageIndex", type="number", desc="页码")
   * @Apidoc\Query("pageSize", type="number", desc="数据条数")
   * @Apidoc\Query("toTime", type="string", desc="开始时间")
   * @Apidoc\Query("formTime", type="string", desc="结束时间")
   * @Apidoc\Query("member_username", type="string", desc="用户账号")
   * @Apidoc\Query("type", type="number", desc="变更类型")
   * @Apidoc\Query("order_no", type="number", desc="订单号")
   * @Apidoc\Query("operate", type="number", desc="操作类型")
   * @Apidoc\Returned("member_id", type="string", desc="用户ID")
   * @Apidoc\Returned("member_nickname", type="string", desc="用户昵称")
   * @Apidoc\Returned("member_portrait", type="string", desc="头像链接地址")
   * @Apidoc\Returned("member_username", type="string", desc="登录账号")
   * @Apidoc\Returned("order_no", type="string", desc="订单号")
   * @Apidoc\Returned("operate", type="string", desc="操作类型 1余额充值 2申请提现 3提现退款 4买入股票 5卖出股票 默认为1")
   * @Apidoc\Returned("money", type="string", desc="金额")
   * @Apidoc\Returned("new_meony", type="string", desc="变更后余额")
   * @Apidoc\Returned("old_meony", type="number", desc="变更前余额")
   * @Apidoc\Returned("type", type="string", desc="变更类型 1增加 0扣减 默认1")
   * @Apidoc\Returned("desc", type="number", desc="变更备注")
   * @Apidoc\Returned("create_time", type="string", desc="写入时间")
   */
  public function getFinancialRecords() {

    $pageIndex           =  input('pageIndex'); # 分页页码
    $pageSize            =  input('pageSize'); # 每页数据条数
    $toTime              =  input('toTime'); # 检测开始时间
    $formTime            =  input('formTime'); # 检测结束时间
    $type                =  input('type'); # 类型
    $member_username     =  input('member_username'); # 用户账号
    $order_no            =  input('order_no'); # 订单号
    $operate             =  input('operate'); # 操作类型

    # 验证分页参数是否为空
    if (empty($pageIndex) || empty($pageSize)) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '分页参数不能为空!']]);
    }

    # 类型
    if (isset($type)) {
      $data['a.type'] =  $type;
    }

    # 账户
    if (!empty($member_username)) {
      $data['b.member_username'] =  $member_username;
    }

    # 订单号
    if (!empty($order_no)) {
      $data['order_no']    =    $order_no;
    }

    # 操作类型
    if (isset($operate)) {
      $data['operate']    =    $operate; 
    }

    # 验证时间
    if ($toTime && $formTime) {
      $Member = UserMoney::alias('a') -> join('member b', 'a.member_id = b.member_id')
      -> field('a.*,b.member_username,b.member_nickname,b.member_portrait') -> whereTime('a.create_time', 'between', [$toTime, $formTime]) -> order(['a.create_time' => 'desc']);
    } else {
      $Member = UserMoney::alias('a') -> join('member b', 'a.member_id = b.member_id')
      -> field('a.*,b.member_username,b.member_nickname,b.member_portrait') -> order(['a.create_time' => 'desc']);
    }

    if (!empty($data)) {
      # 获取数据
      $MemberList = $Member -> where($data) -> page($pageIndex, $pageSize) -> select();
      # 获取总条数
      $total = UserMoney::alias('a') -> join('member b', 'a.member_id = b.member_id') -> where($data) -> count();
    } else {
      # 获取数据
      $MemberList = $Member -> page($pageIndex, $pageSize) -> select();
      # 获取总条数
      $total = UserMoney::count();
    }

    // 处理数据
    foreach ($MemberList as $key => $value) {
      // 获取上级
      $value['parent_name'] = Member::where(['member_id' => $value['parent_id']]) -> value('member_username');

      // 获取上上级
      $value['second_parent_name'] = Member::where(['member_id' => $value['second_parent_id']]) -> value('member_username');

      // 获取上上上级
      $value['third_parent_name'] = Member::where(['member_id' => $value['third_parent_id']]) -> value('member_username');

      // 获取一级代理
      $value['agent_level_1_name'] = '';

      // 获取二级代理
      $value['agent_level_2_name'] = '';

      // 获取一级代理
      if (intval($value['agent_level_1']) > 0) {
        $value['agent_level_1_name'] = Member::where(['member_id' => $value['agent_level_1']]) -> value('member_username');
      }

      // 获取二级代理
      if (intval($value['agent_level_2']) > 0) {
        $value['agent_level_2_name'] = Member::where(['member_id' => $value['agent_level_2']]) -> value('member_username');
      }

      // 获取VIP等级信息
      $value['vip_level'] = Vip::where(['level' => $value['level']]) -> value('name');
    }

    if ($MemberList) {
      return json(['code' => 1,'data' => ['data' => $MemberList, 'total' => $total]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '没有找到你要的数据!']]);
    }
  }

}