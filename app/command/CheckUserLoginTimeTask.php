<?php
declare (strict_types = 1);

namespace app\command;

use app\model\AdminMember;
use app\common\model\Merchant;
use think\facade\Cache;
use think\console\Input;
use think\console\Output;
use think\console\Command;
use app\model\SystemConfig;
use think\console\input\Option;
use think\console\input\Argument;


class CheckUserLoginTimeTask extends Command {

  protected function configure() {

    // 指令配置
    $this -> setName('CheckUserLoginTimeTask') -> setDescription('会员登录超时检测');

  }

  protected function execute(Input $input, Output $output) {

    $output -> writeln(date("Y-m-d H:i:s"));
    $output -> writeln('后台登录超时检测执行开始');
    // 获取在线用户ID
    $LoginUserList = AdminMember::where(['member_online' => 1]) -> select();
    // 获取未操作退出时间
    $logout_request_web = CacheSystemConfig('logout_time_admin');
    
    // 处理数据
    foreach ($LoginUserList as $key => $value) {
      
      // 获取登录时间
      $LoginKey = $value['member_id'] . '_admin_LoginTime';

      // 获取最后活跃时间
      $LoginTime = Cache::get($LoginKey);

      // 计算多长时间没有活跃
      $TimeNumber = time() - intval($LoginTime);
      
      // 获取用户信息
      $User = AdminMember::where(['member_id' => $value['member_id']]) -> find();
      
      // 如果
      if (intval($TimeNumber) >= intval($logout_request_web)) {
        // 更新为离线状态
        $User -> save(['member_online' => 0]);
      } else {
        // 更新为在线状态
        $User -> save(['member_online' => 1]);
      }
    }

    // 指令输出
    $output -> writeln('后台登录超时检测执行完成');
    
    $output -> writeln('商户登录超时检测执行开始');
    // 获取在线用户ID
    $LoginUserList = Merchant::where(['online' => 1]) -> select();
    // 获取未操作退出时间
    $logout_request_web = CacheSystemConfig('logout_time_mch');
    
    // 处理数据
    foreach ($LoginUserList as $key => $value) {
      
      // 获取登录时间
      $LoginKey = $value['member_id'] . '_mch_LoginTime';

      // 获取最后活跃时间
      $LoginTime = Cache::get($LoginKey);

      // 计算多长时间没有活跃
      $TimeNumber = time() - intval($LoginTime);
      
      // 获取用户信息
      $User = Merchant::where(['id' => $value['id']]) -> find();
      
      // 如果
      if (intval($TimeNumber) >= intval($logout_request_web)) {
        // 更新为离线状态
        $User -> save(['online' => 0]);
      } else {
        // 更新为在线状态
        $User -> save(['online' => 1]);
      }
    }

    // 指令输出
    $output -> writeln('后台登录超时检测执行完成');

  }

}