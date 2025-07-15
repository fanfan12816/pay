<?php

namespace app\admin\controller\channel;

use app\AdminController;
use app\common\model\{Merchant,MerchantChannel,Channel,ChannelBank};
use app\common\service\MchSystemService;
use hg\apidoc\annotation as Apidoc;

use app\admin\lists\channel\BankLists;
use app\admin\validate\channel\BankValidate;
use app\common\service\ConfigService;
use think\exception\ValidateException;
use app\common\service\{FileService,ExcelService};
/**
 * @Apidoc\Title("a通道-银行卡管理")
 * Author: JackMater
 */

class BankController extends AdminController {
  
  /**
   * @Apidoc\Title("列表")
   * @Apidoc\Desc("列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/channel/bank/lists")
   * @Apidoc\Query("keyword", type="string", require=true, desc="关键字搜索")
   * @Apidoc\Query("channel_id", type="string", require=true, desc="通道号id")
   * @Apidoc\Query("pay_type", type="number", require=true, desc="类型:0=收,1代付")
   * @Apidoc\Query("type", type="number", require=true, desc="类型:1银行卡,2钱包")
   * @Apidoc\Query("status", type="number", require=true, desc="状态:0-关闭,1开启")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("pageIndex", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("pageSize", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={ChannelBank::class})
   */
  public function lists() {

    //   return messageReturn(1,'开发中');
    # 如果请求头中有携带token
    if ($this -> member_id) {
        return returnDataListsAdmin(new BankLists(),1);
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
   * @Apidoc\Url("admin/v1/channel/bank/inExport")
   * @Apidoc\Query("keyword", type="string", require=true, desc="关键字搜索")
   * @Apidoc\Query("channel_id", type="string", require=true, desc="通道号id")
   * @Apidoc\Query("pay_type", type="number", require=true, desc="类型:0=收,1代付")
   * @Apidoc\Query("type", type="number", require=true, desc="类型:1银行卡,2钱包")
   * @Apidoc\Query("status", type="number", require=true, desc="状态:0-关闭,1开启")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("pageIndex", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("pageSize", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("url", type="string", desc="文件url地址")
   */
  public function inExport() {
    if ($this -> member_id) {
        $lists = new BankLists();
        $list =$lists->lists();
        $head=[
            ["key"=>"channel_id","title"=>"通道编号","txt"=>[]],
            ["key"=>"pay_type","title"=>"收款类型","txt"=>["代收","代付"]],
            ["key"=>"type","title"=>"卡片类型","txt"=>["","银行","钱包"]],
            ["key"=>"desc","title"=>"描述","txt"=>[]],
            ["key"=>"bank_name","title"=>"银行卡名称","txt"=>[]],
            ["key"=>"user_name","title"=>"持卡人名称","txt"=>[]],
            ["key"=>"bank_num","title"=>"银行卡号码","txt"=>[]],
            ["key"=>"iban","title"=>"拓展字段","txt"=>[]],
            ["key"=>"iban","title"=>"拓展字段","txt"=>[]],
            ["key"=>"min","title"=>"最小值","txt"=>[]],
            ["key"=>"max","title"=>"最大值","txt"=>[]],
            ["key"=>"status","title"=>"状态","txt"=>["关闭","开启"]],
            ["key"=>"ip","title"=>"IP","txt"=>[]],
            ["key"=>"remark","title"=>"备注","txt"=>[]],
            ["key"=>"update_by","title"=>"后台修改的用户","txt"=>[]],
            ["key"=>"create_time","title"=>"创建时间","txt"=>[]],
            ["key"=>"update_time","title"=>"更新时间","txt"=>[]],
        ];
        
        $rt=ExcelService::excel($head, $list,"BankLists",'银行卡列表'.$lists->pageNo."_".$lists->pageSize."_".$lists->count());
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(1,'操作成功',$data);
     
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
  }

  /**
   * @Apidoc\Title("新增")
   * @Apidoc\Desc("开发者")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/channel/bank/add")
   * @Apidoc\Param("channel_id", type="string", desc="通道id")
   * @Apidoc\Param("pay_type", type="string", desc="收款类型:0=代收,1代付")
   * @Apidoc\Param("type", type="string", desc="类型:1银行卡,2钱包")
   * @Apidoc\Param("desc", type="string", desc="描述")
   * @Apidoc\Param("bank_name", type="string", desc="银行卡名称/钱包名称")
   * @Apidoc\Param("user_name", type="string", desc="持卡人名称")
   * @Apidoc\Param("bank_num", type="string", desc="银行卡号码/钱包卡号")
   * @Apidoc\Param("iban", type="string", desc="iban")
   * @Apidoc\Param("image", type="string", desc="图片地址")
   * @Apidoc\Param("min", type="string", desc="最小值")
   * @Apidoc\Param("max", type="string", desc="最大值")
   * @Apidoc\Param("status", type="string", desc="状态:0-关闭,1开启")
   * @Apidoc\Param("remark", type="string", desc="备注")
   * @Apidoc\Param("sort", type="string", desc="排序")
   */
  public function add() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new BankValidate())->post()->goCheck('add');
        $ip=getClientIP();
        $data=[
            "channel_id"=>input("channel_id",""),
            "pay_type"=>input("pay_type",0),
            "type"=>input("type",0),
            "desc"=>input("desc",""),
            "bank_name"=>input("bank_name",""),
            "user_name"=>input("user_name",""),
            "bank_num"=>input("bank_num",""),
            "iban"=>input("iban",""),
            "image"=>input("image",""),
            "min"=>input("min",0),
            "max"=>input("max",0),
            "status"=>input("status",0),
            "sort"=>input("sort",0),
            "remark"=>input("remark",""),
            "ip"=>$ip,
            "update_by"=>$this -> member_id
            
        ];
        $model=ChannelBank::create($data);
        return messageReturn(1,"操作成功");
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }

  /**
   * @Apidoc\Title("编辑")
   * @Apidoc\Desc("开发中")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/channel/bank/edit")
   * @Apidoc\Param("id", type="string", desc="主键id")
   * @Apidoc\Param("channel_id", type="string", desc="通道id")
   * @Apidoc\Param("pay_type", type="string", desc="收款类型:0=代收,1代付")
   * @Apidoc\Param("type", type="string", desc="类型:1银行卡,2钱包")
   * @Apidoc\Param("desc", type="string", desc="描述")
   * @Apidoc\Param("bank_name", type="string", desc="银行卡名称/钱包名称")
   * @Apidoc\Param("user_name", type="string", desc="持卡人名称")
   * @Apidoc\Param("bank_num", type="string", desc="银行卡号码/钱包卡号")
   * @Apidoc\Param("iban", type="string", desc="iban")
   * @Apidoc\Param("image", type="string", desc="图片地址")
   * @Apidoc\Param("min", type="string", desc="最小值")
   * @Apidoc\Param("max", type="string", desc="最大值")
   * @Apidoc\Param("status", type="string", desc="状态:0-关闭,1开启")
   * @Apidoc\Param("remark", type="string", desc="备注")
   * @Apidoc\Param("sort", type="string", desc="排序")
   */
  public function edit() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new BankValidate())->post()->goCheck('edit');
        $id=input("id",0);
        $Model=ChannelBank::where(['id' => $id])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'银行卡不存在');
        }else{
            $data=[
                "channel_id"=>input("channel_id",""),
                "pay_type"=>input("pay_type",0),
                "type"=>input("type",0),
                "desc"=>input("desc",""),
                "bank_name"=>input("bank_name",""),
                "user_name"=>input("user_name",""),
                "bank_num"=>input("bank_num",""),
                "iban"=>input("iban",""),
                "image"=>input("image",""),
                "min"=>input("min",0),
                "max"=>input("max",0),
                "status"=>input("status",0),
                "sort"=>input("sort",0),
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
   * @Apidoc\Url("admin/v1/channel/bank/del")
   * @Apidoc\Param("id", type="string", desc="主键id")
   */
  public function del() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new BankValidate())->post()->goCheck('del');
        $id=input("id",0);
        $Model=ChannelBank::where(['id' => $id])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'银行卡不存在');
        }else{
            $Model->save([
                "ip"=>getClientIP(),
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