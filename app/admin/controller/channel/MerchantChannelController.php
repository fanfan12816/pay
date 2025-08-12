<?php

namespace app\admin\controller\channel;

use app\AdminController;
use app\common\model\{Merchant,MerchantChannel,Channel,ChannelBank};
use app\common\service\MchSystemService;
use hg\apidoc\annotation as Apidoc;

use app\admin\lists\channel\MerchantChannelLists;
use app\admin\validate\channel\MerchantChannelValidate;
use app\common\service\ConfigService;
use think\exception\ValidateException;
use app\common\service\{FileService,ExcelService};
/**
 * @Apidoc\Title("a通道-商户通道管理")
 * Author: JackMater
 */

class MerchantChannelController extends AdminController {
  
  /**
   * @Apidoc\Title("列表")
   * @Apidoc\Desc("列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/channel/merchant/lists")
   * @Apidoc\Query("mch_id", type="string", require=true, desc="商户id")
   * @Apidoc\Query("channel_id", type="string", require=true, desc="通道号id")
   * @Apidoc\Query("keyword", type="string", require=true, desc="关键字搜索")
   * @Apidoc\Query("status", type="number", require=true, desc="状态:0-关闭,1开启")
   * @Apidoc\Query("in_status", type="number", require=true, desc="代收状态:0-关闭,1开启")
   * @Apidoc\Query("out_status", type="number", require=true, desc="代付状态:0-关闭,1开启")
   * @Apidoc\Query("source", type="number", require=true, desc="通道来源:0-内部通道,1外接通道")
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
   * @Apidoc\Returned(ref={MerchantChannel::class})
   */
  public function lists() {
    //   return messageReturn(1,'开发中');
    # 如果请求头中有携带token
    if ($this -> member_id) {
        return returnDataListsAdmin(new MerchantChannelLists(),1);
        
     
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }
    /**
   * @Apidoc\Title("导出")
   * @Apidoc\Desc("导出")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/channel/merchant/inExport")
   * @Apidoc\Query("mch_id", type="string", require=true, desc="商户id")
   * @Apidoc\Query("channel_id", type="string", require=true, desc="通道号id")
   * @Apidoc\Query("keyword", type="string", require=true, desc="关键字搜索")
   * @Apidoc\Query("status", type="number", require=true, desc="状态:0-关闭,1开启")
   * @Apidoc\Query("in_status", type="number", require=true, desc="代收状态:0-关闭,1开启")
   * @Apidoc\Query("out_status", type="number", require=true, desc="代付状态:0-关闭,1开启")
   * @Apidoc\Query("source", type="number", require=true, desc="通道来源:0-内部通道,1外接通道")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("pageIndex", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("pageSize", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("url", type="string", desc="文件URL地址")
   */
  public function inExport() {
    if ($this -> member_id) {
        $lists = new MerchantChannelLists();
        $list =$lists->lists();
        $head=[
            ["key"=>"id","title"=>"通道编号","txt"=>""],
            ["key"=>"channel_title","title"=>"通道名称","txt"=>""],
            ["key"=>"in_ratio","title"=>"入金费率","txt"=>""],
            ["key"=>"out_ratio","title"=>"出金费率","txt"=>""],
            ["key"=>"min","title"=>"最小值","txt"=>[]],
            ["key"=>"max","title"=>"最大值","txt"=>[]],
            ["key"=>"status","title"=>"状态","txt"=>["关闭","开启"]],
            ["key"=>"in_per","title"=>"入金每笔扣费","txt"=>[]],
            ["key"=>"out_per","title"=>"出金每笔扣费","txt"=>[]],
            ["key"=>"source","title"=>"通道来源","txt"=>["内部通道","外接通道"]],
            ["key"=>"in_status","title"=>"代收状态","txt"=>["关闭","开启"]],
            ["key"=>"out_status","title"=>"代付状态","txt"=>["关闭","开启"]],
            ["key"=>"remark","title"=>"备注","txt"=>[]],
            ["key"=>"update_by","title"=>"后台修改的用户","txt"=>[]],
            ["key"=>"create_time","title"=>"创建时间","txt"=>[]],
            ["key"=>"update_time","title"=>"更新时间","txt"=>[]],
        ];
        
        $rt=ExcelService::excel($head, $list,"MerchantChannelLists",'商户通道列表'.$lists->pageNo."_".$lists->pageSize."_".$lists->count());
        $data=["url"=>FileService::getFileUrl($rt)];
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
   * @Apidoc\Url("admin/v1/channel/merchant/edit")
   * @Apidoc\Param("id", type="string", desc="主键id")
   * @Apidoc\Param("name", type="string", desc="通道名称")
   * @Apidoc\Param("desc", type="string", desc="描述")
   * @Apidoc\Param("in_ratio", type="string", desc="入金费率")
   * @Apidoc\Param("out_ratio", type="string", desc="出金费率")
   * @Apidoc\Param("min", type="string", desc="最小值")
   * @Apidoc\Param("max", type="string", desc="最大值")
   * @Apidoc\Param("status", type="string", desc="状态:0-关闭,1开启")
   * @Apidoc\Param("in_per", type="string", desc="入金每笔扣费")
   * @Apidoc\Param("out_per", type="string", desc="出金每笔扣费")
   * @Apidoc\Param("source", type="string", desc="通道来源:0-内部通道,1外接通道")
   * @Apidoc\Param("in_per", type="string", desc="入金每笔扣费")
   * @Apidoc\Param("in_status", type="string", desc="代收状态:0-关闭,1开启")
   * @Apidoc\Param("out_status", type="string", desc="代付状态:0-关闭,1开启")
   * @Apidoc\Param("is_bank", type="string", desc="机器人指定银行通知:0-关闭,1指定")
   * @Apidoc\Param("remark", type="string", desc="备注")
   */
  public function edit() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new MerchantChannelValidate())->post()->goCheck('edit');
        $id=input("id",0);
        $Model=MerchantChannel::where(['id' => $id])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'通道不存在');
        }else{
            $data=[
                "in_ratio"=>input("in_ratio",0),
                "out_ratio"=>input("out_ratio",0),
                "video_url"=>input("video_url",""),
                "instr_url"=>input("instr_url",""),
                "min"=>input("min",0),
                "max"=>input("max",0),
                "status"=>input("status",0),
                "type"=>input("type",0),
                "in_per"=>input("in_per",0),
                "out_per"=>input("out_per",0),
                "source"=>input("source",0),
                "in_status"=>input("in_status",0),
                "out_status"=>input("out_status",0),
                "is_bank"=>input("is_bank",0),
                "remark"=>input("remark",""),
                "ip"=>getClientIP(),
                "update_by"=>$this -> member_id
                
            ];
            $Model->save($data);
            return messageReturn(1,"操作成功");
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }
  
  /**
   * @Apidoc\Title("删除")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/channel/merchant/del")
   * @Apidoc\Param("id", type="string", desc="主键id")
   */
  public function del() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new MerchantChannelValidate())->post()->goCheck('del');
        $id=input("id",0);
        $Model=MerchantChannel::where(['id' => $id])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'通道不存在');
        }else{
            $Model->save([
                "update_by"=>$this -> member_id
            ]);
            $Model->delete();
            return messageReturn(1,"操作成功");
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }

  
}