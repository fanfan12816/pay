<?php

namespace app\api\controller;

use app\BaseController;
use app\model\AppStartLog;
use hg\apidoc\annotation as Apidoc;
use think\exception\ValidateException;
use app\api\validate\AppStartingValidate;

/**
 * @Apidoc\Title("APP服务")
 * Author: JackMater
 */

class AppStartingController extends BaseController {

  /**
   * @Apidoc\Title("APP启动日志")
   * @Apidoc\Desc("上传APP启动日志")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/CreateAppStaringLogs")
   * @Apidoc\Query("member_id", type="number", desc="会员ID")
   * @Apidoc\Query("app_type", type="string", desc="系统类型 Andord iOS Apple Windows Linux MacOS HarmonyOS")
   * @Apidoc\Query("rom_name", type="string", desc="ROM类型 MIUI OriginOS ColorOS HyperOS")
   * @Apidoc\Query("app_version", type="string", desc="APP版本")
   * @Apidoc\Query("wgt_version", type="string", desc="资源版本")
   * @Apidoc\Query("language", type="string", desc="语言类型")
   * @Apidoc\Query("device_type", type="string", desc="设备类型 Pad Phone PC")
   * @Apidoc\Query("device_name", type="string", desc="手机型号")
   * @Apidoc\Query("system_verison", type="string", desc="系统版本")
   * @Apidoc\Query("network_type", type="string", desc="网络类型")
   * @Apidoc\Query("app_ua", type="string", desc="APP内核UA")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function CreateAppStaringLogs() {

    $data['member_id']            =       input('member_id'); # 会员ID
    $data['app_type']             =       input('app_type'); # 系统类型
    $data['rom_name']             =       input('rom_name'); # ROM类型
    $data['app_version']          =       input('app_version'); # APP版本
    $data['wgt_version']          =       input('wgt_version'); # 资源版本
    $data['language']             =       input('language'); # 语言类型
    $data['device_type']          =       input('device_type'); # 设备类型
    $data['device_name']          =       input('device_name'); # 手机型号
    $data['system_verison']       =       input('system_verison'); # 系统版本
    $data['network_type']         =       input('network_type'); # 网络类型
    $data['app_ua']               =       input('app_ua'); # APP内核UA

    try {
      validate(AppStartingValidate::class) -> scene('Create') -> check($data);
    } catch (ValidateException $e) {
      // 验证失败 输出错误信息
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => $e -> getError()]]);
    }

    // 写入数据
    $CreateLogs = AppStartLog::create($data);

    // 校验
    if ($CreateLogs) {
      return json(['code' => 1, 'data' => ['code' => 1, 'message' => lang('logs_success')]]);
    } else {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => lang('logs_error')]]);
    }

  }

}