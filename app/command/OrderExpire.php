<?php

namespace app\command;

use app\common\model\{Merchant,PayinOrder,PayoutOrder,MerchantRechargeOrder,MerchantWithdrawOrder,MerchantAccountLog,MerchantChannel,Channel,ChannelBank};
use app\common\service\{FileService,ExcelService,ConfigService};
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Log;
use app\common\service\{PayinCallbackService,PayoutCallbackService};


class OrderExpire extends Command
{
    protected function configure()
    {
        $this->setName('order_expire')
            ->setDescription('订单过期处理');
    }


    protected function execute(Input $input, Output $output)
    {
        try {
            $output -> writeln('开始处理');
            $output -> writeln(date("Y-m-d H:i:s"));
            $output -> writeln('开始处理超时代付');
            self::handlePayinOrder();
            $output -> writeln('超时代付处理结束');
            $output -> writeln(date("Y-m-d H:i:s"));
            $output -> writeln('代收开始处理');
            self::handleInformPayin();
            $output -> writeln('代收处理结束');
            $output -> writeln(date("Y-m-d H:i:s"));
            $output -> writeln('代付开始处理');
            self::handleInformPayout();
            $output -> writeln('代付处理结束');
            $output -> writeln('处理结束');
            return true;
        } catch (\Exception $e) {
            $output -> writeln('处理失败,失败原因:' . $e->getMessage());
            // Log::write('订单退款状态查询失败,失败原因:' . $e->getMessage());
            return false;
        }
    }


    /**
     * @notes 处理代收订单
     */
    public function handlePayinOrder()
    {
        $w=[];
        $w[]=["status","=",0];
        $w[]=["expire_time","<",time()];
        $list = PayinOrder::where($w) -> select();
        foreach ($list as $v){
            echo "执行ID:".$v['id']."\n";
            $cb=PayinCallbackService::callback($v['order_sn'],4);
            echo "执行返回:".json_encode($cb,JSON_UNESCAPED_UNICODE)."\n";
        }
        echo "执行完成\n";
    }
   
    /**
     * @notes 代收通知
     */
    public function handleInformPayin()
    {
        $w=[];
        $w[]=["status",">",1];
        $w[]=["notice_count","<",5];
        $w[]=["is_notice","<>",1];
        $t=time()-(60*10);
        $w[]=["update_time","<",$t];
        $list = PayinOrder::where($w) -> select();
        foreach ($list as $v){
            echo "执行ID:".$v['id']."\n";
            $cb=PayinCallbackService::notify($v['order_sn']);
            echo "执行返回:".json_encode($cb,JSON_UNESCAPED_UNICODE)."\n";
        }
        echo "执行完成\n";
    }

    /**
     * @notes 代收通知
     */
    public function handleInformPayout()
    {
        $w=[];
        $w[]=["status",">",1];
        $w[]=["notice_count","<",5];
        $w[]=["is_notice","<>",1];
        $t=time()-(60*10);
        $w[]=["update_time","<",$t];
        $list = PayoutOrder::where($w) -> select();
        foreach ($list as $v){
            echo "执行ID:".$v['id']."\n";
            $cb=PayoutCallbackService::notify($v['order_sn']);
            echo "执行返回:".json_encode($cb,JSON_UNESCAPED_UNICODE)."\n";
        }
        echo "执行完成\n";
    }

}