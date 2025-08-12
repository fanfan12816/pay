<?php

// 文件上传服务

namespace app\mch\controller;

use think\facade\Env;
use think\facade\Config;
use app\MchController;
use think\facade\Request;
use think\facade\Filesystem;
use hg\apidoc\annotation as Apidoc;
use app\common\service\bot\BotService;

/**
 * @Apidoc\Title("文件上传")
 * Author: JackMater
 */
class UploadFileServiceController extends MchController {

  /**
   * @Apidoc\Title("文件上传接口")
   * @Apidoc\Desc("文件上传接口")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UploadFile")
   * @Apidoc\Returned("path", type="string", desc="下载地址")
   * @Apidoc\Returned("size", type="string", desc="文件大小")
   */
  public function getUploadFileList() {

    // 获取public目录的对象
    $publicPath = public_path();

    // 读取public目录下所有的文件和目录
    $files = Filesystem::disk('public')->listFiles($publicPath);

    return json($publicPath);

  }

  /**
   * @Apidoc\Title("文件上传接口")
   * @Apidoc\Desc("文件上传接口")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/UploadFile")
   * @Apidoc\Param("Tinymce", type="string", require=false, desc="是否返回全网址")
   * @Apidoc\Returned("path", type="string", desc="下载地址")
   * @Apidoc\Returned("size", type="string", desc="文件大小")
   */
  public function UploadFile() {
    // return json(['code' => 0, 'data' => ['code' => 0, 'message' => '文件上传失败!']]);
    # 获取文件信息
    $prefix="mchUpload";
    BotService::addLog($prefix,"","上传图片开始","start");
    BotService::addLog($prefix,"获取原始参数",[input(),$_FILES['file']]);
    // 获取用户的ip
    $ip = getClientIP();
    BotService::addLog($prefix,"获取请求IP",[$ip]);
     # 如果请求头中有携带token
    if ($this -> mchid) {
        BotService::addLog($prefix,"用户uid",[$this -> mchid]);
        $file            =  request() -> file('file'); # 上传文件信息
        $filePath        =  $file -> getRealPath(); # 本地文件路径
        $size            =  round((($_FILES['file']['size'] / 1024) / 1024), 2); # 计算文件大小(Mb)
        $format          =  $file -> getOriginalExtension(); # 获取文件后戳
    
        # 是否富文本
        $Tinymce         =  input('Tinymce'); # 是否富文本
    
        # 获取文件地址
        $fileRoute =$this -> mchid.'/'. date('Y', time()) . '/' . date('m', time()) . '/' . date('d', time());

        try {
            validate(
                    [
                        'file' => [
                            // 限制文件大小(单位b)，这里限制为4M
                            // 'fileSize' => 4 * 1024 * 1024,
                            // 限制文件后缀，多个后缀以英文逗号分割
                            // 'fileExt'  => 'gif,jpg,png,jpeg'
                            'fileExt'  => 'jpg,png,jpeg,gif,bmp,webp'
                        ]
                    ],
                    [
                        'file.fileSize' => '文件太大',
                        'file.fileExt' => '不支持的文件后缀',
                    ]
                )->check(['file' => $file]);
                
              # 执行阿里云上传
              $result = Filesystem::disk('public') -> putFile($fileRoute, $file, 'uniqid');
        
              # 验证上传状态
              if ($result) {
                try {
        
                  // 如果是富文本上传图片
                  if (isset($Tinymce)) {
                    $FilePath = getImageDomain() . '/UploadFile/' . $result;
                  } else {
                    $FilePath = '/UploadFile/' . $result;
                  }
        
                  BotService::addLog($prefix,"图片上传完成",[$FilePath],"end");
                  # 文件上传成功
                  return json([
                    'code'  => 1, 
                    'data'  => [
                      'code' => 1, 
                      'message'  => '文件上传成功!',
                      'path' => $FilePath, # 文件下载地址
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
    } else {
      # 未登录
      return json(['code' => 0, 'data' => ['code' => 0, 'message' => '未登录!']]);
    }

  }

}