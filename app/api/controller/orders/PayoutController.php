<?php

namespace app\api\controller\orders;

use app\api\controller\BaseController;

use hg\apidoc\annotation as Apidoc;

use app\api\validate\{ApiBaseValidate};
use app\api\common\service\{ApiService};

use app\common\model\{Merchant,PayinOrder,PayoutOrder,MerchantRechargeOrder,MerchantWithdrawOrder,MerchantAccountLog,MerchantChannel,Channel,ChannelBank};
use app\common\service\{ConfigService,MchSystemService};
use app\common\jobs\{PayOutJobs};
use think\facade\Cache;
/**
 * @Apidoc\Title("代付对接接口")
 * Author: JackMater
 */
class PayoutController extends BaseController {

    /**
    * @Apidoc\Title("代付支持银行列表")
    * @Apidoc\Desc("代付支持银行列表")
    * @Apidoc\Method("POST")
    * @Apidoc\Url("api/v1/payout/banklists")
    */
    public function banklists() {
        $prefix="payoutBanklists";
        if(empty($this->MchInfo)){
            return ajaxReturn(50005,'接口KEY错误');
        }
        $params = (new ApiBaseValidate())->post()->goCheck('PayoutBanklists');
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
        $inMch=ApiService::inMch($mch_id,$channel_id,2,0);
        if($inMch['code']!==1){
            addLog($prefix,0,[$inMch],$inMch['msg'],$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,$inMch['msg'],$params);
        }
        $Channel=$inMch['data'];
        
        $bankList=[];
        $bankField = 'id,bank_name';
        
        $bankWhere=[
            ['pay_type', '=', 0],
            ['status', '=', 1],
            ['channel_id', '=', $channel_id],
        ];
        $bankList = ChannelBank::field($bankField)
            ->where($bankWhere)
            ->limit(0, 100)
            ->order(['sort' => 'desc', 'id' => 'desc'])
            ->select()
            ->toArray();
        addLog($prefix,0,[$bankList],'银行卡获取成功',$mch_id."_".$channel_id);
        addLog($prefix,2,'','',$mch_id."_".$channel_id);
        return ajaxReturn(1,"操作成功",$bankList);
    }
    
    /**
    * @Apidoc\Title("代付批量下单")
    * @Apidoc\Desc("代付批量下单")
    * @Apidoc\Method("POST")
    * @Apidoc\Url("api/v1/payout/transactions")
    */
    public function bulkOrders() {
        $prefix="payoutBulkOrders";
        if(empty($this->MchInfo)){
            return ajaxReturn(50005,'Interface KEY error');
            // return ajaxReturn(50005,'接口KEY错误');
        }
        // return input("mch_id",0);
        $params = (new ApiBaseValidate())->post()->goCheck('payoutBulkOrders');
        $mch_id=intval(input("mch_id",0));
        $channel_id=input("channel_id",0);
        $data=input("data",[]);
        $rtime=input("time",0);
        $timeNum=time();
        addLog($prefix,1,'','',$mch_id."_".$channel_id);
        addLog($prefix,0,$params,'接收的参数',$mch_id."_".$channel_id);
        $maxnum=200;
        if(count($data)>$maxnum){
            addLog($prefix,0,[count($data)],'订单数量超过',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'批量下单目前一次不能超过'.$maxnum.'条',$params);
        }
        if($mch_id!==$this->MchInfo->id){
            addLog($prefix,0,[$mch_id,$this->MchInfo->id],'密钥不匹配',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'Key mismatch',$params);
            // return ajaxReturn(0,'密钥不匹配',$params);
        }
        // 时间戳差异
        $sjc=intval(ConfigService::get('api_time',10));
        if($rtime+$sjc-$timeNum<0){
            addLog($prefix,0,[],'时间戳差异过大',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'Timestamp difference is too large',$params);
            // return ajaxReturn(0,'时间戳差异过大',$params);
        }
        $signdata=[
            "mch_id"=>$mch_id,
            "channel_id"=>$channel_id,
            "time"=>$rtime,
            "count"=>count($data)
        ];
        $sign=paySign($signdata,$this->MchInfo->secret_key);
        if($sign!==$params['sign']){
            addLog($prefix,0,[$params['sign'],$sign],'签名不正确',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,'Incorrect signature',$params);
            // return ajaxReturn(0,'签名不正确',$params);
        }
        $returnList=[];
        $errnum=0;
        $successnum=0;
        $ip=getClientIP();
        foreach($data as $k => $v){
            $mch_sn=$v['mch_sn']??"";
            $amount=$v['amount']??"";
            $notify_url=$v['notify_url']??"";
            $bank_name=$v['bank_name']??"";
            $bank_num=$v['bank_num']??"";
            $user_name=$v['user_name']??"";
            $user_phone=$v['user_phone']??"";
            $field=$v['field']??"";
            $attach=$v['attach']??"";
            $expire_time=$v['expire_time']??"";
            if(empty($mch_sn)){
                $returnList[]=[
                    "code"=>0,
                    "msg"=>"商户订单号不能为空",
                    "data"=>[
                        "params"=>$v
                    ],
                ];
                $errnum+=1;
                continue;
            }
            if(empty($amount)){
                $returnList[]=[
                    "code"=>0,
                    "msg"=>"订单金额不能为空",
                    "data"=>[
                        "params"=>$v
                    ],
                ];
                $errnum+=1;
                continue;
            }
            if(empty($notify_url)){
                $returnList[]=[
                    "code"=>0,
                    "msg"=>"通知地址不能为空",
                    "data"=>[
                        "params"=>$v
                    ],
                ];
                $errnum+=1;
                continue;
            }
            if(empty($bank_name)){
                $returnList[]=[
                    "code"=>0,
                    "msg"=>"收款银行名称不能为空",
                    "data"=>[
                        "params"=>$v
                    ],
                ];
                $errnum+=1;
                continue;
            }
            if(empty($bank_num)){
                $returnList[]=[
                    "code"=>0,
                    "msg"=>"收款银行卡号不能为空",
                    "data"=>[
                        "params"=>$v
                    ],
                ];
                $errnum+=1;
                continue;
            }
            if(empty($user_name)){
                $returnList[]=[
                    "code"=>0,
                    "msg"=>"收款人名称不能为空",
                    "data"=>[
                        "params"=>$v
                    ],
                ];
                $errnum+=1;
                continue;
            }
            $inMch=ApiService::inMch($mch_id,$channel_id,1,$amount);
            if($inMch['code']!==1){
                $returnList[]=[
                    "code"=>0,
                    "msg"=>$inMch['msg'],
                    "data"=>[
                        "params"=>$v
                    ],
                ];
                $errnum+=1;
                continue;
            }
            $Channel=$inMch['data'];
            $Model = PayoutOrder::where(["mch_sn"=>$mch_sn])->findOrEmpty();
            if($Model->isEmpty()){
                $order_sn=generate_sn(PayoutOrder::class, 'order_sn',"PAYOUT");
                if(empty($expire_time)){
                    $h=intval(ConfigService::get('expire_time',3));
                    if($h<0){
                        $h=3;
                    }
                    $expire_time=$h*(60*60)+$timeNum;
                }else{
                    $expire_time=strtotime($expire_time);
                }
                
                $type=$this->MchInfo->debug==1?3:1;
                
                $service_charge=$amount*$Channel['out_ratio']+$Channel['out_per'];//服务费
                
                // if($type!="3"){
                //     $koumoney=(new MchSystemService())->MerchantMoney($mch_id,1,3,2,$amount+$service_charge,$order_sn,"代付下单扣除");
                //     if($koumoney['code']!=200){
                //         $returnList[]=[
                //             "code"=>0,
                //             "msg"=>$koumoney['msg'],
                //             "data"=>[
                //                 "params"=>$v
                //             ],
                //         ];
                //         $errnum+=1;
                //         continue;
                //     }
                // }
                $indata=[
                    "mch_id"=>$mch_id,
                    "order_sn"=>$order_sn,
                    "mch_sn"=>$mch_sn,
                    "channel_id"=>$channel_id,
                    "type"=>$type,
                    "amount"=>$amount,
                    "notify_url"=>$notify_url,
                    "service_charge"=>$service_charge,
                    "bank_name"=>$bank_name,
                    "user_name"=>$user_name,
                    "bank_num"=>$bank_num,
                    "iban"=>$field,
                    "user_phone"=>$user_phone,
                    "status_time"=>$timeNum,
                    "status"=>6,
                    "request_time"=>$timeNum,
                    "expire_time"=>$expire_time,
                    "timezone"=>$this->MchInfo->timezone,
                    "sign"=>$sign,
                    "extra"=>$attach,
                    "ip"=>$ip
                ];
                $payModel=PayoutOrder::create($indata);
                
                $rtData=[
                    "mch_sn"=>$mch_sn,
                    "order_sn"=>$order_sn,
                ];
                $returnList[]=[
                    "code"=>1,
                    "msg"=>"下单成功",
                    "data"=>[
                        "params"=>$v,
                        "mch_sn"=>$mch_sn,
                        "order_sn"=>$order_sn,
                    ]
                ];
                $successnum+=1;
                addLog($prefix,0,[$payModel],'单条代付请求完成',$mch_id."_".$channel_id);
                if($type!="3"){
                    // 扣除代付队列
                    PayOutJobs::dispatch(['order_sn'=>$order_sn]);
                }
            }else{
                $returnList[]=[
                    "code"=>0,
                    "msg"=>"订单已存在",
                    "data"=>[
                        "params"=>$v,
                        "mch_sn"=>$Model->mch_sn,
                        "order_sn"=>$Model->order_sn,
                    ]
                ];
                $errnum+=1;
                // return ajaxReturn(0,'订单已存在');
            }
           
        }
        $rtData=["error"=>$errnum,"success"=>$successnum,"resData"=>$returnList];
        addLog($prefix,2,$rtData,'处理结果',$mch_id."_".$channel_id);
        return ajaxReturn(1,'success',$rtData);
    }
    /**
    * @Apidoc\Title("代收下单")
    * @Apidoc\Desc("代收下单")
    * @Apidoc\Method("POST")
    * @Apidoc\Url("api/v1/payout/transactions")
    */
    public function transactions() {
        $prefix="payoutTransactions";
        if(empty($this->MchInfo)){
            return ajaxReturn(50005,'接口KEY错误');
        }
        $params = (new ApiBaseValidate())->post()->goCheck('PayoutTransactions');
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
        $inMch=ApiService::inMch($mch_id,$channel_id,1,$amount);
        if($inMch['code']!==1){
            addLog($prefix,0,[$inMch],$inMch['msg'],$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(0,$inMch['msg'],$params);
        }
        $Channel=$inMch['data'];
        $ip=getClientIP();
        $Model = PayoutOrder::where(['mch_id' => $mch_id,"channel_id"=>$channel_id,"mch_sn"=>$mch_sn])->findOrEmpty();
        if($Model->isEmpty()){
            $order_sn=generate_sn(PayoutOrder::class, 'order_sn',"PAYOUT");
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
            
            $type=$this->MchInfo->debug==1?3:1;
            
            $service_charge=$amount*$Channel['out_ratio']+$Channel['out_per'];//服务费
            
            // if($type!="3"){
            //     $koumoney=(new MchSystemService())->MerchantMoney($mch_id,1,3,2,$amount+$service_charge,$order_sn,"代付下单扣除");
            //     if($koumoney['code']!=200){
            //         addLog($prefix,0,[$koumoney,$this->MchInfo],$koumoney['msg'],$mch_id."_".$channel_id);
            //         addLog($prefix,2,'','',$mch_id."_".$channel_id);
            //         Cache::delete($cacheKey); 
            //         return ajaxReturn(0,$koumoney['msg'],$this->MchInfo);
            //     }
            // }
            $bank_name=input('bank_name',"");
            $indata=[
                "mch_id"=>$mch_id,
                "order_sn"=>$order_sn,
                "mch_sn"=>$mch_sn,
                "channel_id"=>$channel_id,
                "type"=>$type,
                "amount"=>$amount,
                "notify_url"=>input('notify_url',""),
                "service_charge"=>$service_charge,
                "bank_name"=>$bank_name,
                "user_name"=>input('user_name',""),
                "bank_num"=>input('bank_num',""),
                "iban"=>input('field',""),
                "user_phone"=>input('user_phone',""),
                "status_time"=>$timeNum,
                "status"=>6,
                "request_time"=>$timeNum,
                "expire_time"=>$expire_time,
                "timezone"=>$this->MchInfo->timezone,
                "payer_name"=>input('payer_name',""),
                "sign"=>input('sign',""),
                "extra"=>input('attach',""),
                "ip"=>$ip
            ];
            $payModel=PayoutOrder::create($indata);
            
            $rtData=[
                "mch_sn"=>$mch_sn,
                "order_sn"=>$order_sn,
            ];
            addLog($prefix,0,[$rtData,$payModel],'代付请求完成',$mch_id."_".$channel_id);
            
            if($type!="3"){
                // 扣除代付队列
                PayOutJobs::dispatch(['order_sn'=>$order_sn]);
            }

            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ajaxReturn(1,'操作成功',$rtData);
        }else{
            addLog($prefix,0,[$Model],'订单已存在',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            // Cache::delete($cacheKey); 
            return ajaxReturn(0,'订单已存在');
        }
    }
 

}