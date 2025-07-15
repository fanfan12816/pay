<?php

namespace app\admin\controller\order;

use app\AdminController;
use app\common\model\{Merchant,MerchantAccountLog};
use app\common\service\MchSystemService;
use hg\apidoc\annotation as Apidoc;

use app\admin\lists\order\AccountLogLists;
use app\common\service\ConfigService;
use think\exception\ValidateException;
use app\common\service\{FileService,ExcelService};
/**
 * @Apidoc\Title("a订单-流水管理")
 * Author: JackMater
 */

class AccountLogController extends AdminController {
  
  /**
   * @Apidoc\Title("列表")
   * @Apidoc\Desc("列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/order/accountLog/lists")
   * @Apidoc\Query("mch_id", type="number", require=true, desc="商户id")
   * @Apidoc\Query("keyword", type="string", require=true, desc="关键字搜索")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("change_object", type="number", require=true, desc="变动对象 1-余额充值 ")
   * @Apidoc\Query("change_type", type="number", require=true, desc="变动类型;[1=充值,2=提现,3=代付,4=代收,5=兑换6=后台操作] ")
   * @Apidoc\Query("action", type="number", require=true, desc="动作 1-增加 2-减少")
   * @Apidoc\Query("source_sn", type="string", require=true, desc="关联单号")
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
   * @Apidoc\Returned("data", type="array", desc="订单列表")
   * @Apidoc\Returned("mch_nick_name", type="array", desc="商户名称")
   * @Apidoc\Returned(ref={MerchantAccountLog::class})
   */
  public function lists() {
    //   return messageReturn(1,'开发中');
    # 如果请求头中有携带token
    if ($this -> member_id) {
        return returnDataListsAdmin(new AccountLogLists(),1);
        
     
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
   * @Apidoc\Url("admin/v1/order/accountLog/inExport")
   * @Apidoc\Query("mch_id", type="number", require=true, desc="商户id")
   * @Apidoc\Query("keyword", type="string", require=true, desc="关键字搜索")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("change_object", type="number", require=true, desc="变动对象 1-余额充值 ")
   * @Apidoc\Query("change_type", type="number", require=true, desc="变动类型;[1=充值,2=提现,3=代付,4=代收,5=兑换6=后台操作] ")
   * @Apidoc\Query("action", type="number", require=true, desc="动作 1-增加 2-减少")
   * @Apidoc\Query("source_sn", type="string", require=true, desc="关联单号")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("pageIndex", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("pageSize", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("url", type="string", desc="文件url地址")
   */
  public function inExport() {
    //   return messageReturn(1,'开发中');
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $lists = new AccountLogLists();
        $list =$lists->lists();
        $head=[
            ["key"=>"order_sn","title"=>"流水号","txt"=>""],
            ["key"=>"mch_id","title"=>"商户编号","txt"=>""],
            ["key"=>"change_object","title"=>"变动对象","txt"=>["","余额","备付金"]],
            ["key"=>"change_type","title"=>"变动类型","txt"=>["","充值","提现","代付","代收","兑换","后台操作"]],
            ["key"=>"action","title"=>"变动类型","txt"=>["","增加","减少"]],
            ["key"=>"left_amount","title"=>"变动前数量","txt"=>[]],
            ["key"=>"change_amount","title"=>"变动数量","txt"=>[]],
            ["key"=>"right_amount","title"=>"变动后数量","txt"=>[]],
            ["key"=>"source_sn","title"=>"关联单号","txt"=>[]],
            ["key"=>"ip","title"=>"IP","txt"=>[]],
            ["key"=>"remark","title"=>"备注","txt"=>[]],
            ["key"=>"create_time","title"=>"创建时间","txt"=>[]],
            ["key"=>"update_time","title"=>"更新时间","txt"=>[]],
        ];
        
        $rt=ExcelService::excel($head, $list,"AccountLogLists",'流水记录列表'.$lists->pageNo."_".$lists->pageSize."_".$lists->count());
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(1,'操作成功',$data);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }
  
}