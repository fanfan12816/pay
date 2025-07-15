<?php

namespace app\api\controller\orders;

use app\api\controller\BaseController;

use hg\apidoc\annotation as Apidoc;

use app\api\validate\{ApiBaseValidate};
use app\api\common\service\{ApiService};

use app\common\model\{Merchant,PayinOrder,PayoutOrder,MerchantRechargeOrder,MerchantWithdrawOrder,MerchantAccountLog,MerchantChannel,Channel,ChannelBank,Language};
use app\common\service\ConfigService;
/**
 * @Apidoc\Title("代收对接接口")
 * Author: JackMater
 */
class PayinController extends BaseController {

    /**
    * @Apidoc\Title("代收下单")
    * @Apidoc\Desc("代收下单")
    * @Apidoc\Method("POST")
    * @Apidoc\Url("api/v1/payin/transactions")
    */
    public function transactions() {
        $prefix="payinTransactions";
        if(empty($this->MchInfo)){
            return ajaxReturn(50005,'接口KEY错误');
        }
        $params = (new ApiBaseValidate())->post()->goCheck('PayinTransactions');
        $mch_id=intval(input("mch_id",0));
        $channel_id=input("channel_id",0);
        $amount=input("amount",0);
        $mch_sn=input("mch_sn",'');
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
        $sjc=intval(ConfigService::get('api_time',30));
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
        $inMch=ApiService::inMch($mch_id,$channel_id,1,$amount);
        if($inMch['code']!==1){
            addLog($prefix,0,[$inMch],$inMch['msg'],$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,$inMch['msg'],$params);
        }
        $Channel=$inMch['data'];
        
        $Model = PayinOrder::where(['mch_id' => $mch_id,"channel_id"=>$channel_id,"mch_sn"=>$mch_sn])->findOrEmpty();
        if($Model->isEmpty()){
            $order_sn=generate_sn(PayinOrder::class, 'order_sn',"PAYIN");
            $expire_time=input('expire_time',"");
            if(empty($expire_time)){
                $h=intval(ConfigService::get('expire_time',3));
                if($h<0){
                    $h=3;
                }
                $expire_time=$h*(60*60)+$timeNum;
            }else{
                $expire_time=strtotime($expire_time);
            }
            $ip=getClientIP();
            $type=$this->MchInfo->debug==1?3:1;
            $service_charge=$amount*$Channel['in_ratio']+$Channel['in_per'];//服务费
            $reality_amount=$amount-$service_charge;
            
            // 随机小数
            $is_random=intval(ConfigService::get('payin_random',0));
            if($is_random==1){
                $payin_random_td=ConfigService::get('payin_random_td',"");
                $payin_random_num=intval(ConfigService::get('payin_random_num',10000));
                $randomList=explode(',', $payin_random_td);
                addLog($prefix,0,[$is_random,$payin_random_td,$randomList,$payin_random_num,$order_sn],'开启了随机小数',$mch_id."_".$channel_id);
                if(in_array($channel_id, $randomList)){
                    $random=rand(1,99);
                    $random=$random>9?$random:'0'.$random;
                    $amount=$amount.'.'.$random;
                    addLog($prefix,0,[$random,$amount,$order_sn],'设置该通道的随机小数',$mch_id."_".$channel_id);
                }
            }
            $indata=[
                "mch_id"=>$mch_id,
                "order_sn"=>$order_sn,
                "mch_sn"=>$mch_sn,
                "channel_id"=>$channel_id,
                "type"=>$type,
                "notify_url"=>input('notify_url',""),
                "amount"=>$amount,
                "reality_amount"=>$reality_amount,
                "service_charge"=>$service_charge,
                "request_time"=>$timeNum,
                "status_time"=>$timeNum,
                "request_time"=>$timeNum,
                "expire_time"=>$expire_time,
                "timezone"=>$this->MchInfo->timezone,
                "payer_name"=>input('payer_name',""),
                "back_url"=>input('back_url',""),
                "sign"=>input('sign',""),
                "extra"=>input('attach',""),
                "ip"=>$ip
            ];
            $payModel=PayinOrder::create($indata);
            $cashier_url=ConfigService::get('cashier_url','');
            $theme=Language::where(['mch_id' => $mch_id,"channel_id"=>$channel_id])->value("theme");
            if(empty($cashier_url)){
               $cashier_url ="收银台未配置地址";
            }else{
                $cashier_url=$cashier_url."/".$theme."/#/".$order_sn;
            }
            $rtData=[
                "mch_sn"=>$mch_sn,
                "order_sn"=>$order_sn,
                "pay_pageurl"=>$cashier_url
            ];
            addLog($prefix,0,[$rtData,$payModel],'代收请求完成',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(1,'操作成功',$rtData);
        }else{
            addLog($prefix,0,[$Model],'订单已存在',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            
            return ajaxReturn(0,'订单已存在');
        }
    }
 

}