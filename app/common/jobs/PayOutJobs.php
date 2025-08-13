<?php
namespace app\common\jobs;

use app\common\utils\job\{BaseJobs,QueueTrait};
use think\facade\Log;
use app\common\model\{Merchant,PayinOrder,PayoutOrder,MerchantRechargeOrder,MerchantWithdrawOrder,MerchantAccountLog,MerchantChannel,Channel,ChannelBank};
use app\common\service\{ConfigService,MchSystemService,PayoutCallbackService,BotSendService};

/**
 * 代付下单队列
 * Class PayOutJobs
 * php think queue.listen --queue
 * @package app\common\jobs
 */
class PayOutJobs extends BaseJobs
{
    use QueueTrait;

    //代付下单队列
    public function handle($data)
    {
        // dump($data);
        Log::write('代付下单队列开始');
        // return true;
        $prefix="PayOutJobs";
        nweAddLog($prefix,"代付下单队列开始","", 1);
        nweAddLog($prefix,"参数",[$data],0);
        try {
            $order_sn=$data['order_sn']??0;
            $model= PayoutOrder::where(['order_sn' => $order_sn])
            ->findOrEmpty();
            if($model->isEmpty()){
                Log::error('订单不存在');
                nweAddLog($prefix,"订单不存在",[$model], 2);
                return true;
            }
            nweAddLog($prefix,"订单信息",[$model], 0);
            // 判断订单不是测试单进行扣款
            if($model->type!="3"){
                $k_num=floatval($model->amount)+floatval($model->service_charge);
                nweAddLog($prefix,"正式单,进行扣除",[$k_num], 0);
                @$koumoney=(new MchSystemService())->MerchantMoney($model->mch_id,1,3,2,$k_num,$model->order_sn,"代付下单扣除");
                nweAddLog($prefix,"扣除返回",[$koumoney], 0);
                if($koumoney['code']==200){
                    $model->status=0;
                    $model->save();
                    @$botres=BotSendService::payoutSend("",$model);
                    nweAddLog($prefix,"机器人发送信息返回",[$botres], 0);
                }else{
                    $model->remark="余额不足,返回失败";
                    $model->status=3;
                    $model->save();
                    @$cb=PayoutCallbackService::notify($order_sn);
                    nweAddLog($prefix,"扣除失败回调订单失败，通知下游",[$cb], 0);
                }
            }else{
                nweAddLog($prefix,"测试订单,不做扣款","", 0);
                $model->status=0;
                $model->save();
                @$botres=BotSendService::payoutSend("",$model);
                nweAddLog($prefix,"机器人发送信息返回",[$botres], 0);
            }
            
            nweAddLog($prefix,"记录日志返回","", 2);
            return true;

        } catch (\Throwable $e) {
            Log::error('失败,失败原因:' . $e->getMessage());
            nweAddLog($prefix,"失败原因:" . $e->getMessage(),[$e], 2);
        }
        return true;
    }
    

}

?>