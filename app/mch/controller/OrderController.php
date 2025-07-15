<?php

namespace app\mch\controller;

use think\facade\Cache;
use app\MchController;
use app\common\model\{Merchant,PayinOrder,PayoutOrder,MerchantRechargeOrder,MerchantWithdrawOrder,MerchantAccountLog,MerchantChannel,Channel,ChannelBank};
use think\captcha\facade\Captcha;
use hg\apidoc\annotation as Apidoc;
use app\mch\validate\OrdersValidate;
use think\exception\ValidateException;
use app\common\cache\MchAccountSafeCache;
use app\common\service\ConfigService;
use app\mch\lists\{TestPayinLists,TestPayoutLists,PayinLists,PayoutLists,RechargeLists,WithdrawLists,AccountLogLists,PayinBankLists,PayoutBankLists,ChannelLists};
use app\common\service\MchSystemService;
use app\common\service\{PayinCallbackService,PayoutCallbackService};
use app\common\service\{FileService,ExcelService};

/**
 * @Apidoc\Title("订单管理")
 * Author: JackMater
 */
class OrderController extends MchController {
  
  
  
  /**
   * @Apidoc\Title("代收回调")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/order/payinCallback")
   * @Apidoc\Param("order_sn", type="string", desc="订单平台编号")
   * @Apidoc\Param("status", type="int", desc="状态 2-审核成功,3-审核失败")
   */
  public function payinCallback() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $order_sn=input('order_sn'); # 订单编号
        if(empty($order_sn)){
            return messageReturn(404,'订单不能为空');
        }
        $status=input('status',2); # 订单编号
        $Model=PayinOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'订单不存在');
        }else{
            if($Model->mch_id!=$this -> mchid){
                return messageReturn(0,'该订单不属于本商户,禁止操作');
            }
            if($Model->type!=3){
                return messageReturn(0,'该订单不是测试订单,禁止操作');
            }
            if($Model->status>1){
                return messageReturn(0,'订单状态异常,不可以进行回调');
            }
            $cb=PayinCallbackService::callback($order_sn,$status);
            if($cb['code']==200){
                return messageReturn(200,$cb['msg']);
            }
            
            return messageReturn(0,$cb['msg']);
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }
  /**
   * @Apidoc\Title("代收通知")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/order/payinNotifier")
   * @Apidoc\Param("order_sn", type="string", desc="订单平台编号")
   */
  public function payinNotifier() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $order_sn=input('order_sn'); # 订单编号
        if(empty($order_sn)){
            return messageReturn(404,'订单不能为空');
        }
        $Model=PayinOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'订单不存在');
        }else{
            if($Model->status<1){
                return messageReturn(0,'订单状态异常,不可以进行回调');
            }
            $cb=PayinCallbackService::notify($order_sn);
            if($cb['code']==200){
                return messageReturn(200,$cb['msg']);
            }
            return messageReturn(0,$cb['msg']);
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }
  
  /**
   * @Apidoc\Title("代付回调")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/order/payoutCallback")
   * @Apidoc\Param("order_sn", type="string", desc="订单平台编号")
   * @Apidoc\Param("status", type="int", desc="状态 2-审核成功,3-审核失败")
   */
  public function payoutCallback() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $order_sn=input('order_sn'); # 订单编号
        if(empty($order_sn)){
            return messageReturn(404,'订单不能为空');
        }
        $status=input('status',2); # 订单编号
        $Model=PayoutOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'订单不存在');
        }else{
            if($Model->mch_id!=$this -> mchid){
                return messageReturn(0,'该订单不属于本商户,禁止操作');
            }
            if($Model->type!=3){
                return messageReturn(0,'该订单不是测试订单,禁止操作');
            }
            if($Model->status>1){
                return messageReturn(0,'订单状态异常,不可以进行回调');
            }
            $cb=PayoutCallbackService::callback($order_sn,$status);
            if($cb['code']==200){
                return messageReturn(200,$cb['msg']);
            }
            
            return messageReturn(0,$cb['msg']);
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }
  /**
   * @Apidoc\Title("代付通知")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/order/payoutNotifier")
   * @Apidoc\Param("order_sn", type="string", desc="订单平台编号")
   */
  public function payoutNotifier() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $order_sn=input('order_sn'); # 订单编号
        if(empty($order_sn)){
            return messageReturn(404,'订单不能为空');
        }
        $Model=PayoutOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'订单不存在');
        }else{
            if($Model->status<1){
                return messageReturn(0,'订单状态异常,不可以进行回调');
            }
            $cb=PayoutCallbackService::notify($order_sn);
            if($cb['code']==200){
                return messageReturn(200,$cb['msg']);
            }
            return messageReturn(0,$cb['msg']);
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }
  
  /**
   * @Apidoc\Title("撤回提现申请")
   * @Apidoc\Desc("")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/order/withdrewClose")
   * @Apidoc\Param("order_sn", type="string", require=true, desc="平台订单号")
   * 
   */
  public function withdrewClose () {
    # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        $order_sn=input('order_sn'); # 订单编号
        if(empty($order_sn)){
            return messageReturn(404,'订单不能为空');
        }
        $Model=MerchantWithdrawOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
            return ajaxReturn(404,'订单不存在');
        }else{
            if($Model->status>0){
                return messageReturn(500,'订单状态已经改变,不能关闭了');
            }
            $Model->status=3;
            $Model->update_time=time();
            $Model->save();
            
            $User = Merchant::where(['id' => $this -> mchid])->findOrEmpty();
            $User->frozen_capital-=$Model->order_amount;
            $User->save();
            
            return messageReturn(200,'操作成功');
        }
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("代收关闭")
   * @Apidoc\Desc("")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/order/payinClose")
   * @Apidoc\Param("order_sn", type="string", require=true, desc="平台订单号")
   * 
   */
  public function payinClose () {
    # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        $order_sn=input('order_sn'); # 订单编号
        if(empty($order_sn)){
            return messageReturn(404,'订单不能为空');
        }
        $Model=PayinOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
            return ajaxReturn(404,'订单不存在');
        }else{
            if($Model->status>0){
                return messageReturn(500,'订单状态已经改变,不能关闭了');
            }
            $Model->status=5;
            $Model->status_time=time();
            $Model->update_time=time();
            $Model->save();
            
            return messageReturn(200,'操作成功');
        }
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("代付关闭")
   * @Apidoc\Desc("")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/order/payoutClose")
   * @Apidoc\Param("order_sn", type="string", require=true, desc="平台订单号")
   * 
   */
  public function payoutClose () {
    # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        $order_sn=input('order_sn'); # 订单编号
        if(empty($order_sn)){
            return messageReturn(404,'订单不能为空');
        }
        $Model=PayoutOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
            return messageReturn(404,'订单不存在');
        }else{
            if($Model->status>0){
                return messageReturn(500,'订单状态已经改变,不能关闭了');
            }
            $Model->status=5;
            $Model->status_time=time();
            $Model->update_time=time();
            $Model->save();
            
            if($Model->type!="3"){
                $koumoney=MchSystemService::MerchantMoney($this -> mchid,1,3,1,$Model->amount+$Model->service_charge,$Model->order_sn,"关闭代付订单退回");
                if($koumoney['code']!=200){
                    return messageReturn(500,$koumoney['msg'],[$koumoney,$User]);
                }
            }
            
            return messageReturn(200,'操作成功');
        }
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  
  /**
   * @Apidoc\Title("新增充值")
   * @Apidoc\Desc("新增充值")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/order/addRecharge")
   * @Apidoc\Param("type", type="number", require=true, desc="充值类型 1-余额充值")
   * @Apidoc\Param("pay_type", type="number", require=true, desc="支付类型 1-USDT 2-银行卡 3-余额支付")
   * @Apidoc\Param("amount", type="float", require=true, desc="金额")
   * 
   */
  public function addRecharge () {
    # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        
        $params = (new OrdersValidate())->post()->goCheck('addRecharge');
        $pay_type=$params['pay_type'];
        $type=$params['type'];
        $amount=$params['amount'];
        
        $order_sn=generate_sn(MerchantRechargeOrder::class, 'order_sn',"CZ");
        $data=[
            "mch_id"=> $this -> mchid,   
            "order_sn"=>$order_sn,
            "type"=>$type,
            "pay_type"=>$pay_type,
            "order_amount"=>$amount,
            'update_time'=>time(),
            'ip'=>getClientIP()
        ];
        
        $model=MerchantRechargeOrder::create($data);
        return messageReturn(200,'操作成功',$model);
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("新增提现申请")
   * @Apidoc\Desc("新增提现申请")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/order/addWithdraw")
   * @Apidoc\Param("type", type="number", require=true, desc="提现类型 1-余额提现")
   * @Apidoc\Param("pay_type", type="number", require=true, desc="到账类型 1-TRC20")
   * @Apidoc\Param("wallet_address", type="string", require=true, desc="usdt地址")
   * @Apidoc\Param("amount", type="float", require=true, desc="金额")
   * 
   */
  public function addWithdraw () {
    # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        $params = (new OrdersValidate())->post()->goCheck('addWithdraw');
        $pay_type=$params['pay_type'];
        $type=$params['type'];
        $amount=floatval($params['amount']);
        $wallet_address=$params['wallet_address'];
        
        $User = Merchant::where(['id' => $this -> mchid])->findOrEmpty();
        if($type==1){
            if($amount>($User->money-$User->frozen_capital)){
                return messageReturn(300,'余额不足以提现');
            }
            
            $User->frozen_capital+=$amount;
            $User->save();
        }
        
        $order_sn=generate_sn(MerchantWithdrawOrder::class, 'order_sn',"TX");
        $data=[
            "mch_id"=> $this -> mchid,   
            "order_sn"=>$order_sn,
            "type"=>$type,
            "pay_type"=>$pay_type,
            "order_amount"=>$amount,
            "wallet_address"=>$wallet_address,
            'update_time'=>time(),
            'ip'=>getClientIP()
        ];
        
        $model=MerchantWithdrawOrder::create($data);
        return messageReturn(200,'操作成功',$model);
          
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("代收下单")
   * @Apidoc\Desc("代收下单")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/order/checkin")
   * @Apidoc\Param("channel_id", type="number", require=true, desc="订单所属通道id")
   * @Apidoc\Param("pay_type", type="number", require=true, desc="订单支付类型 1-银行卡 2-钱包")
   * @Apidoc\Param("bank_id", type="number", require=true, desc="银行卡id")
   * @Apidoc\Param("image", type="string", require=true, desc="交易凭证")
   * @Apidoc\Param("amount", type="float", require=true, desc="金额")
   * 
   */
  public function checkin() {
    # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        $params = (new OrdersValidate())->post()->goCheck('checkin');
        $channel_id=$params['channel_id'];
        $pay_type=$params['pay_type'];
        $bank_id=$params['bank_id'];
        $image=$params['image'];
        $amount=$params['amount'];
        // $amount=floatval($amount);
        $User = Merchant::where(['id' => $this -> mchid])->findOrEmpty();
        $Channel = MerchantChannel::where(['channel_id' =>$channel_id,'mch_id'=>$this -> mchid])->findOrEmpty();
        if($Channel['in_status']!=1){
            return messageReturn(300,'该商户的代收通道未开启');
        }
        if($amount>$Channel['max']||$amount<$Channel['min']){
            // return ajaxReturn(300,'代收金额超限');
        }
        $service_charge=$amount*$Channel['in_ratio']+$Channel['in_per'];//服务费
        $reality_amount=$amount-$service_charge;
        $order_sn=generate_sn(PayinOrder::class, 'order_sn',"PAYIN");
        $type=$User->debug==1?3:2;
        $status=$image?1:0;
        $timeNum=time();
        $expire_time=3*(60*60)+time();
        $ip=getClientIP();
        $data=[
            "mch_id"=> $this -> mchid,   
            "order_sn"=> $order_sn,   
            "mch_sn"=> $order_sn,  
            "channel_id"=>$channel_id,
            "type"=>$type,
            "amount"=>$amount,
            "reality_amount"=>$reality_amount,
            "service_charge"=>$service_charge,
            "bank_id"=>$bank_id,
            "status"=>$status,
            "image"=>$image,
            "status_time"=>$timeNum,
            "request_time"=>$timeNum,
            "expire_time"=>$expire_time,
            'timezone'=>$User['timezone'],
            'update_time'=>$timeNum,
            "ip"=>$ip,
            
            
        ];
        $model=PayinOrder::create($data);
        // return returnDataLists(new TestPayinLists());
        return messageReturn(200,'操作成功',$model);
 
     
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }

  /**
   * @Apidoc\Title("代付下单")
   * @Apidoc\Desc("代付下单")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/order/checkout")
   * @Apidoc\Param("channel_id", type="number", require=true, desc="订单所属通道id")
   * @Apidoc\Param("pay_type", type="number", require=true, desc="订单支付类型 1-银行卡 2-钱包")
   * @Apidoc\Param("bank_name", type="string", require=true, desc="银行卡名称/钱包名称")
   * @Apidoc\Param("user_name", type="string", require=false, desc="持卡人名称")
   * @Apidoc\Param("bank_num", type="string", require=true, desc="银行卡号码/钱包卡号")
   * @Apidoc\Param("iban", type="string", require=false, desc="iban")
   * @Apidoc\Param("amount", type="float", require=true, desc="金额")
   */
  public function checkout() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $params = (new OrdersValidate())->post()->goCheck('checkout');
        $channel_id=$params['channel_id'];
        $pay_type=$params['pay_type'];
        $bank_name=$params['bank_name'];
        $user_name=$params['user_name'];
        $bank_num=$params['bank_num'];
        $iban=$params['iban']??"";
        $amount=$params['amount'];
        // $amount=floatval($amount);
        $User = Merchant::where(['id' => $this -> mchid])->findOrEmpty();
        $Channel = MerchantChannel::where(['channel_id' =>$channel_id,'mch_id'=>$this -> mchid])->findOrEmpty();
        if($Channel['out_status']!=1){
            return messageReturn(300,'该商户的代付通道未开启');
        }
        if($amount>$Channel['max']||$amount<$Channel['min']){
            return messageReturn(300,'代付金额超限');
        }
        $service_charge=$amount*$Channel['out_ratio']+$Channel['out_per'];//服务费
        $reality_amount=$amount;
        $order_sn=generate_sn(PayoutOrder::class, 'order_sn',"PAYOUT");
        $type=$User->debug==1?3:2;
        $status=0;
        $timeNum=time();
        $expire_time=3*(60*60)+time();
        $ip=getClientIP();
        if($type!=3){
            $koumoney=MchSystemService::MerchantMoney($this -> mchid,1,3,2,$amount+$service_charge,$order_sn,"手动下代付单扣除");
            if($koumoney['code']!=200){
                return messageReturn(500,$koumoney['msg'],[$koumoney,$User]);
            }
        }
        $data=[
            "mch_id"=> $this -> mchid,   
            "order_sn"=> $order_sn,   
            "mch_sn"=> $order_sn,  
            "channel_id"=>$channel_id,
            "type"=>$type,
            "amount"=>$amount,
            "reality_amount"=>$reality_amount,
            "service_charge"=>$service_charge,
            "bank_name"=>$bank_name,
            "user_name"=>$user_name,
            "bank_num"=>$bank_num,
            "iban"=>$iban,
            "status"=>$status,
            "status_time"=>$timeNum,
            "request_time"=>$timeNum,
            "expire_time"=>$expire_time,
            'timezone'=>$User['timezone'],
            'update_time'=>$timeNum,
            "ip"=>$ip,
            
            
        ];
        // return ajaxReturn(400,'debug',$data);
        $model=PayoutOrder::create($data);
        // return returnDataLists(new TestPayinLists());
        return messageReturn(200,'操作成功',$model);
        return returnDataLists(new TestPayoutLists());
        // return ajaxReturn(200,'操作成功');
 
      
    } else {
      # 未登录
      return ajaxReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("测试代收订单列表")
   * @Apidoc\Desc("测试代收订单列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/testPayinLists")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="平台订单号")
   * @Apidoc\Query("mch_sn", type="string", require=true, desc="商户订单号")
   * @Apidoc\Query("channel_id", type="number", require=true, desc="订单所属通道id")
   * @Apidoc\Query("type", type="number", require=true, desc="订单类型,1-商户订单 2-手工下单")
   * @Apidoc\Query("pay_type", type="number", require=true, desc="订单支付类型 1-银行卡 2-钱包")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待付款;1-确认中;2-审核成功,3-审核失败,4-订单超时已关闭,5-订单手动关闭")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={PayinOrder::class})
   */
  public function testPayinLists() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        
        return returnDataLists(new TestPayinLists());
        return ajaxReturn(200,'操作成功');
 
     
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }

  /**
   * @Apidoc\Title("测试代付订单列表")
   * @Apidoc\Desc("测试代付订单列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/testPayoutLists")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待审核;1-已审核;2-审核失败")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={PayoutOrder::class})
   */
  public function testPayoutLists() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        
        return returnDataLists(new TestPayoutLists());
        // return ajaxReturn(200,'操作成功');
 
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("代收订单列表导出")
   * @Apidoc\Desc("代收订单列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/payinExport")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="平台订单号")
   * @Apidoc\Query("mch_sn", type="string", require=true, desc="商户订单号")
   * @Apidoc\Query("channel_id", type="number", require=true, desc="订单所属通道id")
   * @Apidoc\Query("type", type="number", require=true, desc="订单类型,1-商户订单 2-手动下单")
   * @Apidoc\Query("pay_type", type="number", require=true, desc="订单支付类型 1-银行卡 2-钱包")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待付款;1-确认中;2-审核成功,3-审核失败,4-订单超时已关闭,5-订单手动关闭")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={PayinOrder::class})
   */
  public function payinExport() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        
        $lists = new PayinLists();
        $list =$lists->lists();
        $head=[
            ["key"=>"mch_id","title"=>"商户编号","txt"=>""],
            ["key"=>"channel_id","title"=>"通道编号","txt"=>""],
            ["key"=>"order_sn","title"=>"平台订单编号","txt"=>""],
            ["key"=>"mch_sn","title"=>"商户订单编号","txt"=>""],
            ["key"=>"type","title"=>"订单类型","txt"=>["","商户订单","手工补单","沙盒订单"]],
            ["key"=>"payer_name","title"=>"付款人姓名","txt"=>[]],
            ["key"=>"amount","title"=>"订单金额","txt"=>[]],
            ["key"=>"reality_amount","title"=>"到账金额","txt"=>[]],
            ["key"=>"service_charge","title"=>"服务费","txt"=>[]],
            ["key"=>"pay_type","title"=>"支付类型","txt"=>["","银行卡","钱包"]],
            ["key"=>"status","title"=>"状态","txt"=>["待付款","确认中","审核成功","审核失败","订单超时已关闭","订单手动关闭"]],
            ["key"=>"ip","title"=>"IP","txt"=>[]],
            ["key"=>"remark","title"=>"备注","txt"=>[]],
            ["key"=>"update_by","title"=>"后台修改的用户","txt"=>[]],
            ["key"=>"create_time","title"=>"创建时间","txt"=>[]],
            ["key"=>"update_time","title"=>"更新时间","txt"=>[]],
        ];
        
        $rt=ExcelService::excel($head, $list,"MchPayinLists",'代收列表'.$lists->pageNo."_".$lists->pageSize."_".$lists->count());
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(200,'操作成功',$data);
     
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("代收订单列表")
   * @Apidoc\Desc("代收订单列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/payinLists")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="平台订单号")
   * @Apidoc\Query("mch_sn", type="string", require=true, desc="商户订单号")
   * @Apidoc\Query("channel_id", type="number", require=true, desc="订单所属通道id")
   * @Apidoc\Query("type", type="number", require=true, desc="订单类型,1-商户订单 2-手动下单")
   * @Apidoc\Query("pay_type", type="number", require=true, desc="订单支付类型 1-银行卡 2-钱包")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待付款;1-确认中;2-审核成功,3-审核失败,4-订单超时已关闭,5-订单手动关闭")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={PayinOrder::class})
   */
  public function payinLists() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        
        return returnDataLists(new PayinLists());
 
     
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }

  /**
   * @Apidoc\Title("代付订单列表")
   * @Apidoc\Desc("代付订单列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/payoutLists")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("mch_sn", type="string", require=true, desc="商户订单号")
   * @Apidoc\Query("channel_id", type="number", require=true, desc="订单所属通道id")
   * @Apidoc\Query("type", type="number", require=true, desc="订单类型,1-商户订单 2-手动下单")
   * @Apidoc\Query("pay_type", type="number", require=true, desc="订单支付类型 1-银行卡 2-钱包")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待审核;1-已审核;2-审核失败")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={PayoutOrder::class})
   */
  public function payoutLists() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        
        return returnDataLists(new PayoutLists());
        // return ajaxReturn(200,'操作成功');
 
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }

  /**
   * @Apidoc\Title("代付订单导出")
   * @Apidoc\Desc("代付订单列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/payoutExport")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("mch_sn", type="string", require=true, desc="商户订单号")
   * @Apidoc\Query("channel_id", type="number", require=true, desc="订单所属通道id")
   * @Apidoc\Query("type", type="number", require=true, desc="订单类型,1-商户订单 2-手动下单")
   * @Apidoc\Query("pay_type", type="number", require=true, desc="订单支付类型 1-银行卡 2-钱包")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待审核;1-已审核;2-审核失败")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={PayoutOrder::class})
   */
  public function payoutExport() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        
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
        
        $rt=ExcelService::excel($head, $list,"MchPayoutLists",'代付列表'.$lists->pageNo."_".$lists->pageSize."_".$lists->count());
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(200,'操作成功',$data);
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }

  /**
   * @Apidoc\Title("充值列表")
   * @Apidoc\Desc("充值列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/rechargeLists")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待审核;1-已审核;2-审核失败")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={MerchantRechargeOrder::class})
   */
  public function rechargeLists() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        
        return returnDataLists(new RechargeLists());
        // return ajaxReturn(200,'操作成功');
 
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("充值导出")
   * @Apidoc\Desc("充值列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/rechargeExport")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待审核;1-已审核;2-审核失败")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={MerchantRechargeOrder::class})
   */
  public function rechargeExport() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $lists = new RechargeLists();
        $list =$lists->lists();
        $head=[
            ["key"=>"mch_id","title"=>"商户编号","txt"=>""],
            ["key"=>"order_sn","title"=>"订单编号","txt"=>""],
            ["key"=>"type","title"=>"充值类型","txt"=>["","余额充值","备付金充值"]],
            ["key"=>"pay_type","title"=>"支付类型","txt"=>["","USDT","银行卡","余额支付"]],
            ["key"=>"status","title"=>"审核状态","txt"=>["待审核","已审核","审核失败"]],
            ["key"=>"order_amount","title"=>"充值金额","txt"=>""],
            ["key"=>"rate","title"=>"汇率","txt"=>[]],
            ["key"=>"service_charge","title"=>"手续费","txt"=>[]],
            ["key"=>"reality_amount","title"=>"到账金额","txt"=>[]],
            ["key"=>"ip","title"=>"IP","txt"=>[]],
            ["key"=>"remark","title"=>"备注","txt"=>[]],
            ["key"=>"update_by","title"=>"后台修改的用户","txt"=>[]],
            ["key"=>"create_time","title"=>"创建时间","txt"=>[]],
            ["key"=>"update_time","title"=>"更新时间","txt"=>[]],
        ];
        
        $rt=ExcelService::excel($head, $list,"MchRechargeLists",'充值列表'.$lists->pageNo."_".$lists->pageSize."_".$lists->count());
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(200,'操作成功',$data);
 
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  

  /**
   * @Apidoc\Title("提现列表")
   * @Apidoc\Desc("提现列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/withdrawLists")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待审核;1-已审核;2-审核失败 3-取消提现")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={MerchantRechargeOrder::class})
   */
  public function withdrawLists() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        
        return returnDataLists(new WithdrawLists());
        // return ajaxReturn(200,'操作成功');
 
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }

  /**
   * @Apidoc\Title("提现列表导出")
   * @Apidoc\Desc("提现列表导出")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/withdrawExport")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待审核;1-已审核;2-审核失败 3-取消提现")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={MerchantRechargeOrder::class})
   */
  public function withdrawExport() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
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
        
        $rt=ExcelService::excel($head, $list,"MchWithdrawLists",'提现列表'.$lists->pageNo."_".$lists->pageSize."_".$lists->count());
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(200,'操作成功',$data);
 
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  
  /**
   * @Apidoc\Title("流水明细表")
   * @Apidoc\Desc("流水明细表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/accountLogLists")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("source_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("change_object", type="number", require=true, desc="变动对象;[1=余额,2=备付金]")
   * @Apidoc\Query("change_type", type="number", require=true, desc="变动类型;[1=充值,2=提现,3=代付,4=代收,5=兑换6=后台操作]")
   * @Apidoc\Query("action", type="number", require=true, desc="动作 1-增加 2-减少")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待审核;1-已审核;2-审核失败")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={MerchantAccountLog::class})
   */
  public function accountLogLists() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        
        return returnDataLists(new AccountLogLists());
        // return ajaxReturn(200,'操作成功');
 
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("流水明细导出")
   * @Apidoc\Desc("流水明细表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/accountLogExport")
   * @Apidoc\Query("order_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("source_sn", type="string", require=true, desc="订单号")
   * @Apidoc\Query("change_object", type="number", require=true, desc="变动对象;[1=余额,2=备付金]")
   * @Apidoc\Query("change_type", type="number", require=true, desc="变动类型;[1=充值,2=提现,3=代付,4=代收,5=兑换6=后台操作]")
   * @Apidoc\Query("action", type="number", require=true, desc="动作 1-增加 2-减少")
   * @Apidoc\Query("status", type="number", require=true, desc="审核状态:0-待审核;1-已审核;2-审核失败")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={MerchantAccountLog::class})
   */
  public function accountLogExport() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        // return ajaxReturn(200,'操作成功');
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
        
        $rt=ExcelService::excel($head, $list,"MchAccountLogLists",'流水记录列表'.$lists->pageNo."_".$lists->pageSize."_".$lists->count());
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(200,'操作成功',$data);
 
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("通道列表")
   * @Apidoc\Desc("获取商户绑定的通道列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/channelLists")
   * @Apidoc\Query("channel_name", type="string", require=false,default="", desc="通道名称")
   * @Apidoc\Query("source", type="number", require=false,default="", desc="通道来源:0-内部通道,1外接通道")
   * @Apidoc\Query("status", type="number", require=false,default="1", desc="状态:0-关闭,1开启")
   * @Apidoc\Query("out_status", type="number", require=false,default="", desc="代付状态:0-关闭,1开启")
   * @Apidoc\Query("in_status", type="number", require=false,default="", desc="代收状态:0-关闭,1开启")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={Channel::class})
   */
  public function channelLists() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        
        return returnDataLists(new ChannelLists());
        // return ajaxReturn(200,'操作成功');
 
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("获取代收银行卡")
   * @Apidoc\Desc("获取代收银行卡")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/payinBankLists")
   * @Apidoc\Query("channel_id", type="number", require=true,default="1", desc="通道号id")
   * @Apidoc\Query("money", type="float", require=true,default="1", desc="金额")
   * @Apidoc\Query("type", type="number", require=true,default="1", desc="订单支付类型 1-银行卡 2-钱包")
   * @Apidoc\Query("keyword", type="string", require=true,default="1", desc="关键字搜索")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={ChannelBank::class})
   */
  public function payinBankLists() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $channel_id=$this->request->get('channel_id/d');
        if(empty($channel_id)){
            return ajaxReturn(404,'通道号不能为空');
        }
        return returnDataLists(new PayinBankLists());
        // return ajaxReturn(200,'操作成功');
 
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("获取代付银行卡")
   * @Apidoc\Desc("获取代付银行卡")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/order/payinBankLists")
   * @Apidoc\Query("channel_id", type="number", require=true,default="1", desc="通道号id")
   * @Apidoc\Query("type", type="number", require=true,default="1", desc="订单支付类型 1-银行卡 2-钱包")
   * @Apidoc\Query("keyword", type="string", require=true,default="1", desc="关键字搜索")
   * @Apidoc\Query("page_no", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("page_size", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={ChannelBank::class})
   */
  public function payoutBankLists() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $channel_id=$this->request->get('channel_id/d');
        if(empty($channel_id)){
            return ajaxReturn(404,'通道号不能为空');
        }
        return returnDataLists(new PayoutBankLists());
        // return ajaxReturn(200,'操作成功');
 
      
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  

}