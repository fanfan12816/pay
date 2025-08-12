<?php

namespace app\admin\controller\order;

use app\AdminController;
use app\common\model\{Merchant,PayoutOrder,MerchantChannel,Channel,ChannelBank};
use app\common\service\MchSystemService;
use hg\apidoc\annotation as Apidoc;

use app\admin\lists\order\PayoutLists;
use app\admin\validate\order\PayoutValidate;
use app\common\service\ConfigService;
use think\exception\ValidateException;
use app\common\service\{FileService,ExcelService};
use app\common\service\{PayoutCallbackService};
/**
 * @Apidoc\Title("a订单-代付订单管理")
 * Author: JackMater
 */

class PayoutController extends AdminController {
  
  /**
   * @Apidoc\Title("列表")
   * @Apidoc\Desc("列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/order/payout/lists")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="平台订单号")
   * @Apidoc\Query("mch_sn", type="string", require=true, desc="商户订单号")
   * @Apidoc\Query("channel_id", type="number", require=true, desc="订单所属通道id")
   * @Apidoc\Query("mch_id", type="number", require=true, desc="商户id")
   * * @Apidoc\Query("bank_name", type="string", require=true, desc="银行卡名称")
   * @Apidoc\Query("bank_num", type="number", require=true, desc="银行卡号码")
   * @Apidoc\Query("user_name", type="string", require=true, desc="银行卡用户名")
   * @Apidoc\Query("type", type="number", require=true, desc="订单类型,1-商户订单 2-手动下单 3-测试订单")
   * @Apidoc\Query("pay_type", type="number", require=true, desc="订单支付类型 1-银行卡 2-钱包")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待付款;1-确认中;2-审核成功,3-审核失败,4-订单超时已关闭,5-订单手动关闭")
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
   * @Apidoc\Returned(ref={PayoutOrder::class})
   */
  public function lists() {
    //   return messageReturn(1,'开发中');
    # 如果请求头中有携带token
    if ($this -> member_id) {
        return returnDataListsAdmin(new PayoutLists(),1);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }
  /**
   * @Apidoc\Title("导出")
   * @Apidoc\Desc("导出")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/order/payout/inExport")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="平台订单号")
   * @Apidoc\Query("mch_sn", type="string", require=true, desc="商户订单号")
   * @Apidoc\Query("channel_id", type="number", require=true, desc="订单所属通道id")
   * @Apidoc\Query("mch_id", type="number", require=true, desc="商户id")
   * @Apidoc\Query("type", type="number", require=true, desc="订单类型,1-商户订单 2-手动下单 3-测试订单")
   * @Apidoc\Query("pay_type", type="number", require=true, desc="订单支付类型 1-银行卡 2-钱包")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待付款;1-确认中;2-审核成功,3-审核失败,4-订单超时已关闭,5-订单手动关闭")
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
        
        $lists = new PayoutLists();
        $list =$lists->lists();
        $head=[
            ["key"=>"mch_id","title"=>"商户编号","txt"=>""],
            ["key"=>"channel_id","title"=>"通道编号","txt"=>""],
            ["key"=>"order_sn","title"=>"平台订单编号","txt"=>""],
            ["key"=>"mch_sn","title"=>"商户订单编号","txt"=>""],
            ["key"=>"type","title"=>"订单类型","txt"=>["","商户订单","手工补单","沙盒订单"]],
            ["key"=>"amount","title"=>"订单金额","txt"=>[]],
            ["key"=>"service_charge","title"=>"服务费","txt"=>[]],
            ["key"=>"bank_name","title"=>"银行名称","txt"=>[]],
            ["key"=>"user_name","title"=>"付款人名称","txt"=>[]],
            ["key"=>"bank_num","title"=>"银行卡号码","txt"=>[]],
            ["key"=>"iban","title"=>"预留字段","txt"=>[]],
            ["key"=>"user_phone","title"=>"电话号码","txt"=>[]],
            ["key"=>"status","title"=>"状态","txt"=>["待付款","确认中","审核成功","审核失败","订单超时已关闭","订单手动关闭"]],
            ["key"=>"ip","title"=>"IP","txt"=>[]],
            ["key"=>"remark","title"=>"备注","txt"=>[]],
            ["key"=>"update_by","title"=>"后台修改的用户","txt"=>[]],
            ["key"=>"create_time","title"=>"创建时间","txt"=>[]],
            ["key"=>"update_time","title"=>"更新时间","txt"=>[]],
        ];
        foreach ($list as &$v) {
            // code...
            // $v['bank_num_wb']='银行卡号码'."-".$v['bank_num'];
            $v['bank_num']=$v['bank_num']." ";
        }
        $rt=ExcelService::excel($head, $list,"PayoutLists",'代付列表'.$lists->pageNo."_".$lists->pageSize."_".$lists->count());
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(1,'操作成功',$data);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }

  /**
   * @Apidoc\Title("回调")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/order/payout/callback")
   * @Apidoc\Param("order_sn", type="string", desc="订单平台编号")
   * @Apidoc\Param("status", type="int", desc="状态 2-审核成功,3-审核失败")
   */
  public function callback() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new PayoutValidate())->post()->goCheck('callback');
        $order_sn=$params['order_sn'];
        $status=$params['status'];
        $Model=PayoutOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'订单不存在');
        }else{
            if($Model->status>1){
                return messageReturn(0,'订单状态异常,不可以进行回调');
            }
            $Model->save([
                "update_by"=>$this -> member_id
            ]);
            $cb=PayoutCallbackService::callback($order_sn,$status);
            if($cb['code']==200){
                return messageReturn(1,$cb['msg']);
            }
            return messageReturn(0,$cb['msg']);
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }

  /**
   * @Apidoc\Title("关闭")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/order/payout/close")
   * @Apidoc\Param("order_sn", type="string", desc="订单平台编号")
   */
  public function close() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new PayoutValidate())->post()->goCheck('close');
        $order_sn=$params['order_sn'];
        $status=5;
        $Model=PayoutOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'订单不存在');
        }else{
            if($Model->status>1){
                return messageReturn(0,'订单状态异常,不可以进行关闭');
            }
            $Model->save([
                "update_by"=>$this -> member_id
            ]);
            $cb=PayoutCallbackService::callback($order_sn,$status);
            if($cb['code']==200){
                return messageReturn(1,$cb['msg']);
            }
            return messageReturn(0,$cb['msg']);
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }

  /**
   * @Apidoc\Title("通知")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/order/payout/notifier")
   * @Apidoc\Param("order_sn", type="string", desc="订单平台编号")
   */
  public function notifier() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new PayoutValidate())->post()->goCheck('notifier');
        $order_sn=$params['order_sn'];
        $Model=PayoutOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'订单不存在');
        }else{
            if($Model->status<2){
                return messageReturn(0,'订单状态异常,不可以进行回调');
            }
            $Model->save([
                "update_by"=>$this -> member_id
            ]);
            $cb=PayoutCallbackService::notify($order_sn);
            if($cb['code']==200){
                return messageReturn(1,$cb['msg']);
            }
            return messageReturn(0,$cb['msg']);
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
   * @Apidoc\Url("admin/v1/order/payout/del")
   * @Apidoc\Param("order_sn", type="string", desc="订单平台编号")
   */
  public function del() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new PayoutValidate())->post()->goCheck('del');
        $order_sn=$params['order_sn'];
        $Model=PayoutOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'订单不存在');
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