<?php

namespace app\api\controller\auto;

use app\api\controller\BaseController;

use hg\apidoc\annotation as Apidoc;

use app\api\validate\{ApiBaseValidate};
use app\api\common\service\{ApiService};

use app\common\model\{PayinOrder};
use app\common\service\ConfigService;
use app\common\service\{PayinCallbackService};
/**
 * @Apidoc\Title("查询接口")
 * Author: JackMater
 */
class AutoController extends BaseController {

    
    /**
    * @Apidoc\Title("代收订单查询接口")
    * @Apidoc\Desc("代收订单查询接口")
    * @Apidoc\Method("POST")
    * @Apidoc\Url("api/v1/query/payin")
    */
    public function payin() {
        $prefix="AutoPayin";
        $prefixEnd="";
        $order_sn=input("order_sn",0);
        $rtime=input("time",0);
        $sign=input("sign","");
        $timeNum=time();
        
        addLog($prefix,1,'','',$prefixEnd);
        addLog($prefix,0,input(),'接收的参数',$prefixEnd);
        if(empty($order_sn)||empty($rtime)||empty($sign)){
            addLog($prefix,0,[$order_sn,$rtime,$sign],'参数不正确',$prefixEnd);
            addLog($prefix,2,'','',$prefixEnd);
            return ajaxReturn(0,'参数不正确',[$order_sn,$rtime,$sign]);
        }
        // 时间戳差异
        $sjc=10;
        if($rtime+$sjc-$timeNum<0){
            addLog($prefix,0,[$rtime,$timeNum],'时间戳差异过大',$prefixEnd);
            addLog($prefix,2,'','',$prefixEnd);
            return ajaxReturn(0,'时间戳差异过大',[$rtime,$timeNum]);
        }
        
        $bdsign=md5(substr($rtime, 0, 5).$order_sn.substr($rtime, 5));
        
        if($sign!==$bdsign){
            addLog($prefix,0,[$bdsign,$sign],'签名不正确',$prefixEnd);
            addLog($prefix,2,'','',$prefixEnd);
            return ajaxReturn(0,'签名不正确',[$bdsign,$sign]);
        }
        
        $field = 'order_sn,mch_sn,channel_id,type,notice_count,is_notice,notice_back,amount,reality_amount,service_charge,pay_type,payer_name,status,remark,extra as attach,bank_id,mch_id,image,create_time,image';
        $order=PayinOrder::field($field)->with(['channel','bank'])->where(["order_sn"=>$order_sn])->findOrEmpty();
        if($order->isEmpty()){
            addLog($prefix,0,[$order],'订单号错误',$prefixEnd);
            addLog($prefix,2,'','',$prefixEnd);
            return ajaxReturn(0,'订单号有误,无该订单');
        }
        addLog($prefix,0,[$order],'请求完成',$prefixEnd);
        addLog($prefix,2,'','',$prefixEnd);
        @$order['bank_num']=$order->bank->bank_num;
        return ajaxReturn(1,'操作成功',$order);
    }
    
    public function callback() {
        # 如果请求头中有携带token
        $prefix="AutoCallback";
        $prefixEnd="";
        $order_sn=input("order_sn",0);
        $rtime=input("time",0);
        $sign=input("sign","");
        $timeNum=time();
        $status=2;
        addLog($prefix,1,'','',$prefixEnd);
        addLog($prefix,0,input(),'接收的参数',$prefixEnd);
        if(empty($order_sn)||empty($rtime)||empty($sign)){
            addLog($prefix,0,[$order_sn,$rtime,$sign],'参数不正确',$prefixEnd);
            addLog($prefix,2,'','',$prefixEnd);
            return ajaxReturn(0,'参数不正确',[$order_sn,$rtime,$sign]);
        }
        // 时间戳差异
        $sjc=10;
        if($rtime+$sjc-$timeNum<0){
            addLog($prefix,0,[$rtime,$timeNum],'时间戳差异过大',$prefixEnd);
            addLog($prefix,2,'','',$prefixEnd);
            return ajaxReturn(0,'时间戳差异过大',[$rtime,$timeNum]);
        }
        
        $bdsign=md5(substr($rtime, 0, 5).$order_sn.substr($rtime, 5));
        
        if($sign!==$bdsign){
            addLog($prefix,0,[$bdsign,$sign],'签名不正确',$prefixEnd);
            addLog($prefix,2,'','',$prefixEnd);
            return ajaxReturn(0,'签名不正确',[$bdsign,$sign]);
        }
        $Model=PayinOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        addLog($prefix,0,[$Model],'获取订单',$prefixEnd);
        if($Model->isEmpty()){
           return messageReturn(0,'订单不存在');
        }else{
            if($Model->status>1){
                return messageReturn(0,'订单状态异常,不可以进行回调');
            }
            $cb=PayinCallbackService::callback($order_sn,$status);
            addLog($prefix,0,[$cb],'回调返回',$prefixEnd);
            if($cb['code']==200){
                return messageReturn(1,$cb['msg']);
            }
            addLog($prefix,2,'','',$prefixEnd);
            return messageReturn(0,$cb['msg']);
        }
  }
  
    public function failback() {
        # 如果请求头中有携带token
        $prefix="AutoFailback";
        $prefixEnd="";
        $order_sn=input("order_sn",0);
        $rtime=input("time",0);
        $sign=input("sign","");
        $remark=input("remark","");
        $timeNum=time();
        $status=3;
        addLog($prefix,1,'','',$prefixEnd);
        addLog($prefix,0,input(),'接收的参数',$prefixEnd);
        if(empty($order_sn)||empty($rtime)||empty($sign)){
            addLog($prefix,0,[$order_sn,$rtime,$sign],'参数不正确',$prefixEnd);
            addLog($prefix,2,'','',$prefixEnd);
            return ajaxReturn(0,'参数不正确',[$order_sn,$rtime,$sign]);
        }
        // 时间戳差异
        $sjc=10;
        if($rtime+$sjc-$timeNum<0){
            addLog($prefix,0,[$rtime,$timeNum],'时间戳差异过大',$prefixEnd);
            addLog($prefix,2,'','',$prefixEnd);
            return ajaxReturn(0,'时间戳差异过大',[$rtime,$timeNum]);
        }
        
        $bdsign=md5(substr($rtime, 0, 5).$order_sn.substr($rtime, 5));
        
        if($sign!==$bdsign){
            addLog($prefix,0,[$bdsign,$sign],'签名不正确',$prefixEnd);
            addLog($prefix,2,'','',$prefixEnd);
            return ajaxReturn(0,'签名不正确',[$bdsign,$sign]);
        }
        $Model=PayinOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        addLog($prefix,0,[$Model],'获取订单',$prefixEnd);
        if($Model->isEmpty()){
           return messageReturn(0,'订单不存在');
        }else{
            if($Model->status>1){
                return messageReturn(0,'订单状态异常,不可以进行回调');
            }
            // $Model->remark=$remark;
            $Model->save(["remark"=>$remark]);
            
            $cb=PayinCallbackService::callback($order_sn,$status);
            addLog($prefix,0,[$cb],'回调返回',$prefixEnd);
            if($cb['code']==200){
                return messageReturn(1,$cb['msg']);
            }
            addLog($prefix,2,'','',$prefixEnd);
            return messageReturn(0,$cb['msg']);
        }
  }
}