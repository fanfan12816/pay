<?php

namespace app\admin\controller\order;

use app\AdminController;
use app\common\model\{Merchant,MerchantWithdrawOrder};
use app\common\service\MchSystemService;
use hg\apidoc\annotation as Apidoc;

use app\admin\lists\order\WithdrawLists;
use app\admin\validate\order\WithdrawValidate;
use app\common\service\ConfigService;
use think\exception\ValidateException;
use app\common\service\{FileService,ExcelService};
/**
 * @Apidoc\Title("a订单-提现订单管理")
 * Author: JackMater
 */

class WithdrawController extends AdminController {
  
  /**
   * @Apidoc\Title("列表")
   * @Apidoc\Desc("列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/order/withdraw/lists")
   * @Apidoc\Query("mch_id", type="number", require=true, desc="商户id")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("type", type="number", require=true, desc="充值类型 1-余额体现 ")
   * @Apidoc\Query("pay_type", type="number", require=true, desc="支付类型 1-USDT 2-银行卡  ")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待审核;1-已审核;2-审核失败")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("pageIndex", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("pageSize", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("data", type="array", desc="订单列表")
   * @Apidoc\Returned("mch_nick_name", type="array", desc="商户名称")
   * @Apidoc\Returned(ref={MerchantWithdrawOrder::class})
   */
  public function lists() {
    //   return messageReturn(1,'开发中');
    # 如果请求头中有携带token
    if ($this -> member_id) {
        return returnDataListsAdmin(new WithdrawLists(),1);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }
  /**
   * @Apidoc\Title("导出")
   * @Apidoc\Desc("导出")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/order/withdraw/inExport")
   * @Apidoc\Query("mch_id", type="number", require=true, desc="商户id")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("type", type="number", require=true, desc="充值类型 1-余额体现 ")
   * @Apidoc\Query("pay_type", type="number", require=true, desc="支付类型 1-USDT 2-银行卡  ")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待审核;1-已审核;2-审核失败")
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
        $lists = new WithdrawLists();
        $list =$lists->lists();
        $head=[
            ["key"=>"mch_id","title"=>"商户编号","txt"=>""],
            ["key"=>"order_sn","title"=>"订单编号","txt"=>""],
            ["key"=>"type","title"=>"类型","txt"=>["","余额提现","备付金提现"]],
            ["key"=>"pay_type","title"=>"到账类型","txt"=>["","TRC20"]],
            ["key"=>"wallet_address","title"=>"钱包地址","txt"=>""],
            ["key"=>"status","title"=>"审核状态","txt"=>["待审核","已审核","审核失败","取消提现"]],
            ["key"=>"order_amount","title"=>"充值金额","txt"=>""],
            ["key"=>"pay_time","title"=>"审核时间","txt"=>[]],
            ["key"=>"rate","title"=>"汇率","txt"=>[]],
            ["key"=>"service_charge","title"=>"手续费","txt"=>[]],
            ["key"=>"reality_amount","title"=>"到账金额","txt"=>[]],
            ["key"=>"ip","title"=>"IP","txt"=>[]],
            ["key"=>"remark","title"=>"备注","txt"=>[]],
            ["key"=>"update_by","title"=>"后台修改的用户","txt"=>[]],
            ["key"=>"create_time","title"=>"创建时间","txt"=>[]],
            ["key"=>"update_time","title"=>"更新时间","txt"=>[]],
        ];
        
        $rt=ExcelService::excel($head, $list,"WithdrawLists",'提现列表'.$lists->pageNo."_".$lists->pageSize."_".$lists->count());
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(1,'操作成功',$data);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }

  /**
   * @Apidoc\Title("审核")
   * @Apidoc\Desc("开发中")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/order/withdraw/check")
   * @Apidoc\Param("order_sn", type="string", desc="订单平台编号")
   * @Apidoc\Param("status", type="string", desc="审核状态1-已审核;2-审核失败")
   * @Apidoc\Param("rate", type="string", desc="当前汇率")
   * @Apidoc\Param("service_charge", type="string", desc="手续费")
   * @Apidoc\Param("remark", type="string", desc="备注")
   */
  public function check() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $status=input('status',0);
        if($status==1){
            $params = (new WithdrawValidate())->post()->goCheck('checkSuccess');
        }else{
            $params = (new WithdrawValidate())->post()->goCheck('checkFail');
        }
        $order_sn=input('order_sn',0);
        $remark=input('remark',"");
        $Model=MerchantWithdrawOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'订单不存在');
        }else{
            if($Model->status!=0){
                return messageReturn(0,'订单状态异常,不可以进行回调');
            }
            $service_charge=floatval(input('service_charge',0));
            $rate=floatval(input('rate',0));
            if($rate==0){
                $reality_amount=0;
            }else{
                $reality_amount=($Model->order_amount-$service_charge)/$rate;
            }
            
            $User = Merchant::where(['id' => $Model->mch_id])->findOrEmpty();
            if($status==1){
                $money=MchSystemService::MerchantMoney($Model->mch_id,1,2,2,$Model->order_amount,$order_sn,'提现扣除-'.$remark,$this -> member_id);
    
                if($money['code']!=200){
                        return messageReturn(500,$money['msg'],[$money]);
                }
            }
            if($status==1||$status==2){    
                $User->frozen_capital-=$Model->order_amount;
                
                $User->save();
            }
            
            $Model->save([
                "status"=>$status,
                "reality_amount"=>$reality_amount,
                "rate"=>$rate,
                "service_charge"=>$service_charge,
                "remark"=>$remark,
                "update_by"=>$this -> member_id,
                "pay_time"=>time(),
            ]);
            return messageReturn(1,"成功");
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
   * @Apidoc\Url("admin/v1/order/withdraw/del")
   * @Apidoc\Param("order_sn", type="string", desc="订单平台编号")
   */
  public function del() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new WithdrawValidate())->post()->goCheck('del');
        $order_sn=$params['order_sn'];
        $Model=MerchantWithdrawOrder::where(['order_sn' => $order_sn])->findOrEmpty();
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