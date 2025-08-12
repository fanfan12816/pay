<?php

namespace app\api\controller\query;

use app\api\controller\BaseController;

use hg\apidoc\annotation as Apidoc;

use app\api\validate\{ApiBaseValidate};
use app\api\common\service\{ApiService};

use app\common\model\{Merchant,PayinOrder,PayoutOrder,MerchantRechargeOrder,MerchantWithdrawOrder,MerchantAccountLog,MerchantChannel,Channel,ChannelBank};
use app\common\service\ConfigService;
/**
 * @Apidoc\Title("查询接口")
 * Author: JackMater
 */
class QueryController extends BaseController {

    /**
    * @Apidoc\Title("商户信息查询接口")
    * @Apidoc\Desc("商户信息查询接口")
    * @Apidoc\Method("POST")
    * @Apidoc\Url("api/v1/query/merchant")
    */
    public function merchant() {
        $prefix="queryMerchant";
        if(empty($this->MchInfo)){
            return ajaxReturn(50005,'接口KEY错误');
        }
        $params = (new ApiBaseValidate())->post()->goCheck('QueryMerchant');
        $mch_id=intval(input("mch_id",0));
        $rtime=input("time",0);
        $timeNum=time();
        addLog($prefix,1,'','',$mch_id);
        addLog($prefix,0,$params,'接收的参数',$mch_id);
        
        if($mch_id!==$this->MchInfo->id){
            addLog($prefix,0,[$mch_id,$this->MchInfo->id],'密钥不匹配',$mch_id);
            addLog($prefix,2,'','',$mch_id);
            return ajaxReturn(0,'密钥不匹配',$params);
        }
        // 时间戳差异
        $sjc=intval(ConfigService::get('api_time',10));
        if($rtime+$sjc-$timeNum<0){
            addLog($prefix,0,[],'时间戳差异过大',$mch_id);
            addLog($prefix,2,'','',$mch_id);
            return ajaxReturn(0,'时间戳差异过大',$params);
        }
        $data=$params;
        unset($data['sign']);
        $sign=paySign($data,$this->MchInfo->secret_key);
        if($sign!==$params['sign']){
            addLog($prefix,0,[$params['sign'],$sign],'签名不正确',$mch_id);
            addLog($prefix,2,'','',$mch_id);
            return ajaxReturn(0,'签名不正确',$params);
        }
        $rtData=[
            "sn"=>$this->MchInfo->sn,
            "nick_name"=>$this->MchInfo->nick_name,
            "money"=>$this->MchInfo->money,
            "frozen_capital"=>$this->MchInfo->frozen_capital,
            "timezone"=>$this->MchInfo->timezone,
        ];
        addLog($prefix,0,[$rtData,$this->MchInfo],'请求完成',$mch_id);
        addLog($prefix,2,'','',$mch_id);
        return ajaxReturn(1,'操作成功',$rtData);
        
        
    }
    
    /**
    * @Apidoc\Title("通道信息查询")
    * @Apidoc\Desc("通道信息查询")
    * @Apidoc\Method("POST")
    * @Apidoc\Url("api/v1/query/channel")
    */
    public function channel() {
        $prefix="queryChannel";
        if(empty($this->MchInfo)){
            return ajaxReturn(50005,'接口KEY错误');
        }
        $params = (new ApiBaseValidate())->post()->goCheck('QueryinChannel');
        $mch_id=intval(input("mch_id",0));
        $channel_id=input("channel_id",0);
        $rtime=input("time",0);
        $timeNum=time();
        addLog($prefix,1,'','',$mch_id."_".$channel_id);
        addLog($prefix,0,$params,'接收的参数',$mch_id."_".$channel_id);
        
        if($mch_id!==$this->MchInfo->id){
            addLog($prefix,0,[$mch_id,$this->MchInfo->id],'密钥不匹配',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'密钥不匹配',$params);
        }
        // 时间戳差异
        $sjc=intval(ConfigService::get('api_time',10));
        if($rtime+$sjc-$timeNum<0){
            addLog($prefix,0,[],'时间戳差异过大',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'时间戳差异过大',$params);
        }
        $data=$params;
        unset($data['sign']);
        $sign=paySign($data,$this->MchInfo->secret_key);
        if($sign!==$params['sign']){
            addLog($prefix,0,[$params['sign'],$sign],'签名不正确',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'签名不正确',$params);
        }
        $inMch=ApiService::inMch($mch_id,$channel_id,1,0);
        if($inMch['code']!==1){
            addLog($prefix,0,[$inMch],$inMch['msg'],$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,$inMch['msg'],$params);
        }
        $Channel=$inMch['data'];
        
        $rtData=[
            "id"=>$Channel['id'],
            "name"=>$Channel['name'],
            "in_ratio"=>$Channel['in_ratio'],
            "out_ratio"=>$Channel['out_ratio'],
            "min"=>$Channel['min'],
            "max"=>$Channel['max'],
            "in_per"=>$Channel['in_per'],
            "out_per"=>$Channel['out_per'],
            "in_status"=>$Channel['in_status'],
            "out_status"=>$Channel['out_status'],
        ];
        addLog($prefix,0,[$rtData,$Channel],'请求完成',$mch_id."_".$channel_id);
        addLog($prefix,2,'','',$mch_id."_".$channel_id);
        return ajaxReturn(1,'操作成功',$rtData);
    }
    
    /**
    * @Apidoc\Title("代收订单查询接口")
    * @Apidoc\Desc("代收订单查询接口")
    * @Apidoc\Method("POST")
    * @Apidoc\Url("api/v1/query/payin")
    */
    public function payin() {
        $prefix="queryPayin";
        if(empty($this->MchInfo)){
            return ajaxReturn(50005,'接口KEY错误');
        }
        $params = (new ApiBaseValidate())->post()->goCheck('QueryinPayin');
        $mch_id=intval(input("mch_id",0));
        $channel_id=input("channel_id",0);
        $order_sn=input("order_sn",0);
        $rtime=input("time",0);
        $timeNum=time();
        addLog($prefix,1,'','',$mch_id."_".$channel_id);
        addLog($prefix,0,$params,'接收的参数',$mch_id."_".$channel_id);
        
        if($mch_id!==$this->MchInfo->id){
            addLog($prefix,0,[$mch_id,$this->MchInfo->id],'密钥不匹配',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'密钥不匹配',$params);
        }
        // 时间戳差异
        $sjc=intval(ConfigService::get('api_time',10));
        if($rtime+$sjc-$timeNum<0){
            addLog($prefix,0,[],'时间戳差异过大',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'时间戳差异过大',$params);
        }
        $data=$params;
        unset($data['sign']);
        $sign=paySign($data,$this->MchInfo->secret_key);
        if($sign!==$params['sign']){
            addLog($prefix,0,[$params['sign'],$sign],'签名不正确',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'签名不正确',$params);
        }
        $inMch=ApiService::inMch($mch_id,$channel_id,1,0);
        if($inMch['code']!==1){
            addLog($prefix,0,[$inMch],$inMch['msg'],$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,$inMch['msg'],$params);
        }
        $Channel=$inMch['data'];
        $field = 'order_sn,mch_sn,channel_id,type,notice_count,is_notice,notice_back,amount,reality_amount,service_charge,pay_type,payer_name,status,remark,extra as attach';
        $order=PayinOrder::field($field)->where(['mch_id' => $mch_id,"channel_id"=>$channel_id,"order_sn|mch_sn"=>$order_sn])->findOrEmpty();
        if($order->isEmpty()){
            addLog($prefix,0,[$order],'订单号错误',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'订单号有误,无该订单');
        }
        addLog($prefix,0,[$order],'请求完成',$mch_id."_".$channel_id);
        addLog($prefix,2,'','',$mch_id."_".$channel_id);
        return ajaxReturn(1,'操作成功',$order);
    }
    
    /**
    * @Apidoc\Title("代付订单查询接口")
    * @Apidoc\Desc("代付订单查询接口")
    * @Apidoc\Method("POST")
    * @Apidoc\Url("api/v1/query/payin")
    */
    public function payout() {
        $prefix="queryPayout";
        if(empty($this->MchInfo)){
            return ajaxReturn(50005,'接口KEY错误');
        }
        $params = (new ApiBaseValidate())->post()->goCheck('QueryinPayout');
        $mch_id=intval(input("mch_id",0));
        $channel_id=input("channel_id",0);
        $order_sn=input("order_sn",0);
        $rtime=input("time",0);
        $timeNum=time();
        addLog($prefix,1,'','',$mch_id."_".$channel_id);
        addLog($prefix,0,$params,'接收的参数',$mch_id."_".$channel_id);
        
        if($mch_id!==$this->MchInfo->id){
            addLog($prefix,0,[$mch_id,$this->MchInfo->id],'密钥不匹配',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'密钥不匹配',$params);
        }
        // 时间戳差异
        $sjc=intval(ConfigService::get('api_time',10));
        if($rtime+$sjc-$timeNum<0){
            addLog($prefix,0,[],'时间戳差异过大',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'时间戳差异过大',$params);
        }
        $data=$params;
        unset($data['sign']);
        $sign=paySign($data,$this->MchInfo->secret_key);
        if($sign!==$params['sign']){
            addLog($prefix,0,[$params['sign'],$sign],'签名不正确',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'签名不正确',$params);
        }
        $inMch=ApiService::inMch($mch_id,$channel_id,1,0);
        if($inMch['code']!==1){
            addLog($prefix,0,[$inMch],$inMch['msg'],$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,$inMch['msg'],$params);
        }
        $Channel=$inMch['data'];
        $field = 'order_sn,mch_sn,channel_id,type,notice_count,is_notice,notice_back,amount,service_charge,pay_type,bank_name,user_name,bank_num,iban as field,user_phone,status,remark,extra as attach';
        $order=PayoutOrder::field($field)->where(['mch_id' => $mch_id,"channel_id"=>$channel_id,"order_sn|mch_sn"=>$order_sn])->findOrEmpty();
        if($order->isEmpty()){
            addLog($prefix,0,[$order],'订单号错误',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'订单号有误,无该订单');
        }
        addLog($prefix,0,[$order],'请求完成',$mch_id."_".$channel_id);
        addLog($prefix,2,'','',$mch_id."_".$channel_id);
        return ajaxReturn(1,'操作成功',$order);
    }

}