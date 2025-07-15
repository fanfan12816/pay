<?php


declare(strict_types=1);

namespace app\api\common\service;

use app\common\model\{Merchant,PayinOrder,PayoutOrder,MerchantRechargeOrder,MerchantWithdrawOrder,MerchantAccountLog,MerchantChannel,Channel,ChannelBank};

class ApiService
{

    /**
     * @notes 验证通道,返回通道信息
     * @param $mch_id  商户编号
     * @param $channel_id 通道编号
     * @param $status 验证类型 1 代收 2 代付
     * @param $amount 金额
     */
    public static function inMch(int $mch_id, $channel_id, $status=0, $amount=0)
    {   
        $prefix="inMchChannel";
        addLog($prefix,1,'','',$mch_id."_".$channel_id);
        if(empty($mch_id)||empty($channel_id)){
            addLog($prefix,0,[$mch_id,$channel_id],'参数不存在',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ["code"=>400,"msg"=>"Parameter does not exist"];
            // return ["code"=>400,"msg"=>"参数不存在"];
        }
        
        $Model = MerchantChannel::where(['mch_id' => $mch_id,"channel_id"=>$channel_id])->findOrEmpty();
        if($Model->isEmpty()){
            addLog($prefix,0,[$Model],'通道不存在',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            
            return ["code"=>401,"msg"=>"Channel does not exist"];
            // return ["code"=>401,"msg"=>"通道不存在"];
        }
        
        if($Model->status!=1){
            addLog($prefix,0,[$Model],'通道未开启',$mch_id."_".$channel_id);
            addLog($prefix,2,'','',$mch_id."_".$channel_id);
            return ["code"=>402,"msg"=>"Channel is not open"];
            // return ["code"=>402,"msg"=>"通道未开启"];
        }
        if($status==1){
            if($Model->in_status!=1){
                addLog($prefix,0,[$Model],'代收通道未开启',$mch_id."_".$channel_id);
                addLog($prefix,2,'','',$mch_id."_".$channel_id);
                return ["code"=>402,"msg"=>"The collection channel is not opened"];
                // return ["code"=>402,"msg"=>"代收通道未开启"];
            }
        }
        if($status==2){
            if($Model->out_status!=1){
                addLog($prefix,0,[$Model],'代付通道未开启',$mch_id."_".$channel_id);
                addLog($prefix,2,'','',$mch_id."_".$channel_id);
                return ["code"=>403,"msg"=>"The payment channel is not opened"];
                // return ["code"=>403,"msg"=>"代付通道未开启"];
            }
        }
        if($amount!=0){
            if($Model->min>$amount){
                addLog($prefix,0,[$Model],'不能低于通道最低值',$mch_id."_".$channel_id);
                addLog($prefix,2,'','',$mch_id."_".$channel_id);
                return ["code"=>404,"msg"=>"Cannot be lower than the channel minimum value"];
                // return ["code"=>404,"msg"=>"不能低于通道最低值"];
            }
            if($Model->max<$amount){
                addLog($prefix,0,[$Model],'不能高于通道最大值',$mch_id."_".$channel_id);
                addLog($prefix,2,'','',$mch_id."_".$channel_id);
                return ["code"=>405,"msg"=>"cannot be higher than the channel maximum"];
                // return ["code"=>405,"msg"=>"不能高于通道最大值"];
            }
        }
        $c=Channel::where(["id"=>$channel_id])->findOrEmpty();
        $Model->name=$c->name;
        addLog($prefix,0,[$Model],'验证成功',$mch_id."_".$channel_id);
        addLog($prefix,2,'','',$mch_id."_".$channel_id);
        return ["code"=>1,"msg"=>"验证通过","data"=>$Model];
        
        
    }
    
}