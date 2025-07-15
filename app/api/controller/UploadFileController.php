<?php

namespace app\api\controller;

use app\BaseController;
use think\facade\Filesystem;
use hg\apidoc\annotation as Apidoc;

/**
 * @Apidoc\Title("文件上传")
 */
class UploadFileController extends BaseController {

  /**
   * @Apidoc\Title("文件上传")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("/api/v1/UploadFile")
   * @Apidoc\Header("Authorization", type="string",require=true, desc="Token") 
   * @Apidoc\Query("file", type="string",require=true, desc="文件对象")
   * @Apidoc\Returned("path", type="string", desc="文件下载地址")
   * @Apidoc\Returned("size", type="string", desc="文件大小")
   */
  public function UploadFile() {

    # 获取文件信息
    $file            =  request() -> file('file'); # 上传文件信息
    $filePath        =  $file -> getRealPath(); # 本地文件路径
    $size            =  round((($_FILES['file']['size'] / 1024) / 1024), 2); # 计算文件大小(Mb)
    $format          =  $file -> getOriginalExtension(); # 获取文件后戳

    # 获取文件地址
    $fileRoute = date('Y', time()) . '/' . date('m', time()) . '/' . date('d', time());

    try {
      # 执行阿里云上传
      $result = Filesystem::disk('public') -> putFile($fileRoute, $file, 'uniqid');

      # 验证上传状态
      if ($result) {
        try {
          # 文件上传成功
          return json([
            'code'  => 1, 
            'data'  => [
              'code' => 1, 
              'message'  => '文件上传成功!',
              'path' => '/UploadFile/' . $result, # 文件下载地址
              'size' => strval($size) . 'MB' # 文件大小
            ],
          ]);
        } catch (Exception $e) {
          # 上传失败
          return json(['code' => 0, 'data' => ['code' => 0, 'message' => '文件上传失败!']]);
        }
      } else {
        return json(['code' => 0, 'data' => ['code' => 0, 'message' => '文件上传错误!']]);
      }
    } catch (Exception $e) {
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '文件上传失败!']]);
    }

  }

}