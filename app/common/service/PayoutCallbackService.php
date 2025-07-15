<?php


declare(strict_types=1);

namespace app\common\service;

use app\common\model\{Merchant,PayoutOrder,MerchantChannel};
use app\common\service\{MchSystemService};

class PayoutCallbackService
{
    /**
     * @notes 回调方法
     * @param $order_sn  平台订单编号
     * @param $status 状态
     * @param $change_type 变动类型
     * @param $action 增加还是减少
     * @param $change_amount 变动金额
     * @param $source_sn 关联ID
     * @param $remark 备注
     */
    public static function callback($order_sn="",$status="")
    {
        $prefix="PayoutCallback";
        MchSystemService::addLog($prefix,1);
        MchSystemService::addLog($prefix,0,[$order_sn,$status],'回调传入数据');
        if(empty($order_sn)){
             MchSystemService::addLog($prefix,0,"",'平台订单编号为空');
             MchSystemService::addLog($prefix,2);
             return ["code"=>0,"msg"=>"平台订单编号为空"];
        }
        if(empty($status)){
             MchSystemService::addLog($prefix,0,"",'回调状态为空');
             MchSystemService::addLog($prefix,2);
             return ["code"=>0,"msg"=>"回调状态为空"];
        }
        MchSystemService::addLog($prefix,0,"",'获取订单信息');
        $Model=PayoutOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
            MchSystemService::addLog($prefix,0,[$Model],'订单为空');
            MchSystemService::addLog($prefix,2);
            return ["code"=>0,"msg"=>"订单为空"];
        }else{
            MchSystemService::addLog($prefix,0,[$Model],'获取订单成功');
            if($Model->status>1){
                MchSystemService::addLog($prefix,0,"",'订单状态已经改变,不能回调了');
                MchSystemService::addLog($prefix,2);
                return ["code"=>0,"msg"=>"订单状态已经改变,不能回调了"];
            }
            if($Model->type!="3"){
                if($status>2){
                    $reality_amount=$Model->amount+$Model->service_charge;
                    MchSystemService::addLog($prefix,0,[$reality_amount],'退回代付余额');
                    $addMoney=MchSystemService::MerchantMoney($Model->mch_id,1,3,1,$reality_amount,$Model->order_sn, "代付失败,退回余额");
                    if($addMoney['code']!=200){
                        MchSystemService::addLog($prefix,0,"",'余额增加失败');
                        MchSystemService::addLog($prefix,2);
                        return ["code"=>0,"msg"=>"余额增加失败"];
                    }
                    MchSystemService::addLog($prefix,0,[$addMoney],'余额增加成功');
                }
            }else{
                MchSystemService::addLog($prefix,0,"",'测试订单,不改变余额');
            }
            MchSystemService::addLog($prefix,0,"",'更新订单状态');
            $Model->status=$status;
            $Model->status_time=time();
            $Model->update_time=time();
            $Model->save();
            MchSystemService::addLog($prefix,0,[$Model],'更新订单状态成功');
            
            @$rt=self::notify($order_sn);
            
            MchSystemService::addLog($prefix,0,[$rt],'通知返回');
            MchSystemService::addLog($prefix,2);
            
            return ["code"=>200,"msg"=>"回调成功"];
        }
    }
   
    /**
     * @notes 通知下游
     * @param $prefix文件类型
     * @param string $start 0 正常记录,1开始,2结束
     * @param null $data
     * @param null  $tt 标题
     * @return array|int|mixed|string
     */
    public static function notify($order_sn="")
    {
        $prefix="PayoutNotify";
        MchSystemService::addLog($prefix,1);
        MchSystemService::addLog($prefix,0,[$order_sn],'通知传入数据');
        if(empty($order_sn)){
             MchSystemService::addLog($prefix,0,"",'平台订单编号为空');
             MchSystemService::addLog($prefix,2);
             return ["code"=>0,"msg"=>"平台订单编号为空"];
        }
        MchSystemService::addLog($prefix,0,"",'获取订单信息');
        $Model=PayoutOrder::where(['order_sn' => $order_sn])->findOrEmpty();
        if($Model->isEmpty()){
            MchSystemService::addLog($prefix,0,[$Model],'订单为空');
            MchSystemService::addLog($prefix,2);
            return ["code"=>0,"msg"=>"订单为空"];
        }else{
            MchSystemService::addLog($prefix,0,[$Model],'获取订单成功');
            $statusTxt=["待付款","确认中","审核成功","审核失败","订单超时已关闭","订单手动关闭"];
            $status=$Model->status;
            $notify_url=$Model->notify_url;
            $data=[
                "status"=>$status,
                "order_sn"=>$Model->order_sn,
                "mch_sn"=>$Model->mch_sn,
                "notice_count"=>$Model->notice_count+1,
                "amount"=>$Model->amount,
                "pay_type"=>$Model->pay_type,
                "remark"=>$Model->remark,
                "attach"=>$Model->extra,
                "time"=>time(),
            ];
            $User = Merchant::where(['id' => $Model->mch_id])->findOrEmpty();
            $token=$User->secret_key;
            $data['sign'] = paySign($data,$token);
            $dt=[
                "code"=>1,
                "data"=>$data,
                "message"=>$statusTxt[$status]
            ];
            MchSystemService::addLog($prefix,0,[$dt],'回调信息');
            $rt=posturl($notify_url,$dt,$token);
            MchSystemService::addLog($prefix,0,[$rt],'回调信息返回');
            MchSystemService::addLog($prefix,2);
            $Model->notice_back=$rt;
            $Model->notice_count+=1;
            if(empty($rt)){
                $Model->is_notice=2;
                $Model->update_time=time();
                $Model->sign_back=$data['sign'];
                $Model->save();
                return ["code"=>200,"msg"=>"通知返回失败"];
            }
            if(strtolower($rt)=="success"){
                $Model->is_notice=1;
                $Model->update_time=time();
                $Model->sign_back=$data['sign'];
                $Model->save();
                return ["code"=>200,"msg"=>"通知返回成功"];
            }else{
                $Model->is_notice=2;
                $Model->update_time=time();
                $Model->sign_back=$data['sign'];
                $Model->save();
                return ["code"=>200,"msg"=>"通知返回失败"];
            }
        }
    }
   
   
}