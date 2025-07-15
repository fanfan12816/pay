<?php

namespace app\admin\controller\system;

use app\AdminController;
use app\common\model\{Merchant,MerchantChannel,Channel,ChannelBank,BotGroup,Language};
use app\common\service\MchSystemService;
use hg\apidoc\annotation as Apidoc;

use app\admin\lists\system\LanguageLists;

use think\exception\ValidateException;
use app\common\service\{ConfigService,FileService};



/**
 * @Apidoc\Title("a系统管理-收银台语言管理")
 * Author: JackMater
 */

class LanguageController extends AdminController {

  /**
   * @Apidoc\Title("收银台主题列表")
   * @Apidoc\Desc("收银台主题列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/system/lang/theme")
   * 
   */
  public function theme() {
    //   return messageReturn(1,'开发中');
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $data=[
            ["key"=>"default","title"=>"默认主题"],    
            ["key"=>"default2","title"=>"默认主题2"],    
            ["key"=>"sf888","title"=>"sf888主题"],    
        ];
        return ajaxReturn(1,'操作成功',["data"=>$data]);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }
  
  /**
   * @Apidoc\Title("列表")
   * @Apidoc\Desc("列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/system/lang/lists")
   * @Apidoc\Query("keyword", type="string", require=true, desc="关键字搜索")
   * @Apidoc\Query("mch_id", type="string", require=true, desc="商户id")
   * @Apidoc\Query("channel_id", type="string", require=true, desc="通道号id")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("pageIndex", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("pageSize", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={Language::class})
   */
  public function lists() {
    //   return messageReturn(1,'开发中');
    # 如果请求头中有携带token
    if ($this -> member_id) {
        
        return returnDataListsAdmin(new LanguageLists(),1);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }
  /**
   * @Apidoc\Title("导出")
   * @Apidoc\Desc("导出")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/system/lang/inExport")
   * @Apidoc\Query("keyword", type="string", require=true, desc="关键字搜索")
   * @Apidoc\Query("mch_id", type="string", require=true, desc="商户id")
   * @Apidoc\Query("channel_id", type="string", require=true, desc="通道号id")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("pageIndex", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("pageSize", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("url", type="string", desc="文件url地址")
   */
  public function inExport() {
    # 如果请求头中有携带token
    return messageReturn(0,'未配置');

    if ($this -> member_id) {
        $list = new LanguageLists();
        $data=["url"=>FileService::getFileUrl("/UploadFile/excel/member_template.xlsx")];
        return ajaxReturn(1,'操作成功',$data);
     
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }

  /**
   * @Apidoc\Title("编辑")
   * @Apidoc\Desc("开发中")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/system/lang/edit")
   * @Apidoc\Param("id", type="string", desc="主键id")
   * @Apidoc\Param("title", type="string", desc="标题")
   * @Apidoc\Param("logo", type="string", desc="logo")
   * @Apidoc\Param("desc", type="string", desc="描述")
   * @Apidoc\Param("next", type="string", desc="下一步")
   * @Apidoc\Param("previous", type="string", desc="上一步")
   * @Apidoc\Param("accomplish", type="string", desc="完成")
   * @Apidoc\Param("bank", type="string", desc="选择银行")
   * @Apidoc\Param("bankInfo", type="string", desc="付款信息")
   * @Apidoc\Param("credit", type="string", desc="付款凭证")
   * @Apidoc\Param("await", type="string", desc="等待确认")
   * @Apidoc\Param("bankName", type="string", desc="银行名称")
   * @Apidoc\Param("bankNum", type="string", desc="银行卡号")
   * @Apidoc\Param("bankIban", type="string", desc="iban")
   * @Apidoc\Param("bankUser", type="string", desc="持卡人姓名")
   * @Apidoc\Param("price", type="string", desc="金额")
   * @Apidoc\Param("sn", type="string", desc="订单号")
   * @Apidoc\Param("create", type="string", desc="下单时间")
   * @Apidoc\Param("end", type="string", desc="过期时间")
   * @Apidoc\Param("scpzts", type="string", desc="请上传付款凭证")
   * @Apidoc\Param("payName", type="string", desc="请填写付款人姓名")
   * @Apidoc\Param("fzwzts", type="string", desc="复制成功")
   * @Apidoc\Param("extra", type="string", desc="extra")
   */
  public function edit() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $id=input("id",'');
        $params=input('param.');
        if(empty($id)){
            return messageReturn(0,'主键id不能为空');
        }
        $Model=Language::where(['id' => $id])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'配置不存在');
        }else{
            $newdata=isModification($params,$Model);
            if(array_key_exists('newData',$newdata)){
                $newdata['newData']['update_by']=$this -> member_id;
                $Model->allowField(['title', 'logo','desc','next','previous','accomplish','bank','bankInfo','credit','await','bankName','bankNum','bankIban','bankUser','price','sn','create','end','scpzts','payName','fzwzts','warn','bg_img','bg_color',"text_color",'update_by','theme'])->save($newdata['newData']);
                return messageReturn(1,"操作成功");
            }else{
                return messageReturn(1,"数据相同,未作修改");
            }
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }


  
}