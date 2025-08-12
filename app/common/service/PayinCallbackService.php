<?php


declare(strict_types=1);

namespace app\common\service;

use app\common\model\{Merchant,PayinOrder,MerchantChannel};
use app\common\service\{MchSystemService};
use app\common\service\bot\BotService;
use think\facade\Cache;

class PayinCallbackService
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
        $prefix="PayinCallback";
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
        $Model=PayinOrder::field("*")->with(['channel','bank'])->where(['order_sn' => $order_sn])->findOrEmpty();
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
                if($status==2){
                    $reality_amount=$Model->reality_amount;
                    MchSystemService::addLog($prefix,0,[$reality_amount],'增加余额');
                    $addMoney=MchSystemService::MerchantMoney($Model->mch_id,1,4,1,$reality_amount,$Model->order_sn, "代收订单回调成功增加");
                    if($addMoney['code']!=200){
                        MchSystemService::addLog($prefix,0,"",'余额增加失败');
                        MchSystemService::addLog($prefix,2);
                        return ["code"=>0,"msg"=>"余额增加失败"];
                    }
                    MchSystemService::addLog($prefix,0,[$addMoney],'增加余额成功');
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
            @$zf=self::botzf($Model);
            // @$bt=$this->botSend($payModel);
            MchSystemService::addLog($prefix,0,[$zf],'转发返回');
            
            
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
        $prefix="PayinNotify";
        MchSystemService::addLog($prefix,1);
        MchSystemService::addLog($prefix,0,[$order_sn],'通知传入数据');
        if(empty($order_sn)){
             MchSystemService::addLog($prefix,0,"",'平台订单编号为空');
             MchSystemService::addLog($prefix,2);
             return ["code"=>0,"msg"=>"平台订单编号为空"];
        }
        MchSystemService::addLog($prefix,0,"",'获取订单信息');
        $Model=PayinOrder::where(['order_sn' => $order_sn])->findOrEmpty();
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
                "proof"=>$Model->image,
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
            $rt=str_replace("\"","", $rt);
            $rt=str_replace("'","", $rt);
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
    
    public static function botzf($orders=[]){
        $prefix="PayinBotZhuanfa";
        BotService::addLog($prefix,"","开始转发发送消息");
        BotService::addLog($prefix,"订单信息",$orders);
        // return 123;
        if(empty($orders['order_sn'])){
            BotService::addLog($prefix,"订单有误",[],'end');
            return false;
        }
        BotService::addLog($prefix,"订单号",$orders["order_sn"]);
        $list = [
            ["min"=>100,"max"=>1000,"bank"=>[
                    ["name"=>"popular","chat_id"=>"-4778736717"],
                    ["name"=>"bhd","chat_id"=>"-4635247231"],
                    ["name"=>"Banreservas","chat_id"=>"-4777264436"],
                ]
            ], 
            ["min"=>1000,"max"=>5000,"bank"=>[
                    ["name"=>"popular","chat_id"=>"-4795769303"],
                    ["name"=>"bhd","chat_id"=>"-4790582498"],
                    ["name"=>"Banreservas","chat_id"=>"-4766124215"],
                ]
            ], 
            ["min"=>5000,"max"=>20000,"bank"=>[
                    ["name"=>"popular","chat_id"=>"-4769884081"],
                    ["name"=>"bhd","chat_id"=>"-4666474706"],
                    ["name"=>"Banreservas","chat_id"=>"-4699422505"],
                ]
            ], 
            ["min"=>20000,"max"=>50000,"bank"=>[
                    ["name"=>"popular","chat_id"=>"-4725011035"],
                    ["name"=>"bhd","chat_id"=>"-4704955868"],
                    ["name"=>"Banreservas","chat_id"=>"-4714941230"],
                ]
            ], 
            ["min"=>50000,"max"=>510000,"bank"=>[
                    ["name"=>"popular","chat_id"=>"-4635391611"],
                    ["name"=>"bhd","chat_id"=>"-4760911178"],
                    ["name"=>"Banreservas","chat_id"=>"-4791129533"],
                ]
            ], 
        ];
        $amount=floatval($orders['amount']);
        $bank=$orders['bank'];
        if($orders['status']!=2){
            BotService::addLog($prefix,"订单不是成功的",$orders['status'],"end");
            return false;
        }
        if(empty($bank)){
            BotService::addLog($prefix,"银行信息有误",$bank,"end");
            return false;
        }
        @$create_time=date("Y-m-d H:i:s",$orders['create_time']);
        @$tpcreate_time=date("Y-m-d H:i:s",$orders['update_time']);
        foreach ($list as $v){
            // BotService::addLog($prefix,"匹配金额",[$amount,$v]);
            if($amount>=$v['min']&&$amount<$v['max']){
                BotService::addLog($prefix,"匹配金额成功",[$amount,$v]);
                foreach ($v["bank"] as $b){
                    // BotService::addLog($prefix,"判断银行",[$b,$v["bank"],$bank]);
                    if(strtolower($b["name"])==strtolower($bank['bank_name'])){
                        BotService::addLog($prefix,"判断银行成功",[$b,$v["bank"]]);
                        @$_text.="<b>转发时间</b>：<code>".date("Y-m-d H:i:s")."</code>\n";
                        @$_text.="<b>备注</b>：<code>回调转发</code>\n";
                        @$_text.="<b>商户编号</b>：<code>{$orders['mch_id']}</code>\n";
                        @$_text.="<b>通道编号</b>：<code>{$orders['channel_id']}</code>\n";
                        @$_text.="<b>商户订单号</b>：<code>{$orders['mch_sn']}</code>\n";
                        @$_text.="<b>平台订单号</b>：<code>{$orders['order_sn']}</code>\n";
                        @$_text.="<b>订单金额</b>：<code>{$orders['amount']}</code>\n";
                        @$_text.="<b>用户姓名</b>：<code>{$orders['payer_name']}</code>\n";
                        @$_text.="<b>选择银行</b>：<code>{$orders['bank']['bank_name']}</code>\n";
                        @$_text.="<b>选择银行卡号</b>：<code>{$orders['bank']['bank_num']}</code>\n";
                        @$_text.="<b>订单创建时间</b>：<code>{$create_time}</code>\n";
                        @$_text.="<b>图片上传时间</b>：<code>{$tpcreate_time}</code>\n";
                        @$_text.="<em>订单支付截图</em>：\n";
                        $chatId=$b['chat_id'];
                        $reData=[
                            "chat_id"=>$chatId,
                            "caption"=>$_text,
                            "photo"=>$orders['image'],
                            "show_caption_above_media"=>true,
                            "reply_markup"=>'',
                        ];
                        $tgSend=(new BotService())->send("/sendPhoto",$reData);
                        BotService::addLog($prefix,"发送图片信息返回",$tgSend['ok']);
                        if(!$tgSend['ok']){
                            $_text.="<a href='".$Model->image."'>".$it['extra']['image']['txt']."</a>\n";
                            $reData=[
                                "chat_id"=>$it['chat_id'],
                                "text"=>$_text,
                                "reply_markup"=>'',
                            ];
                            $tgSend=(new BotService())->send("/sendMessage",$reData);
                            BotService::addLog($prefix,"发送文字信息返回",$tgSend['ok']);
                        }
                        if(Cache::get('start'.$chatId)=="open"){
                            $price=floatval($orders['amount']);
                             $xiafaItem=["qunid"=>$chatId,"price"=>$price,"order_sn"=>$orders['order_sn'],"type"=>0,"text"=>$message['text'],"orders"=>json_encode($orders,JSON_UNESCAPED_UNICODE),"cretae_time"=>time()];
                             BotService::addLog($prefix,"处理入款数据",[]); 
                            $rkb=Cache::inc('huitiao'.$chatId);
                            BotService::addLog($prefix,"入款笔数加1",$rkb); 
                            $rkb=Cache::set('zonghuitiao'.$chatId,(Cache::get('zonghuitiao'.$chatId)+$price));
                            BotService::addLog($prefix,"总入款加{$price}",[$rkb]); 
                            $rklist=Cache::get('huitiaolist'.$chatId);
                            array_push($rklist,$xiafaItem);
                            $rkb=Cache::set('huitiaolist'.$chatId,$rklist);
                            BotService::addLog($prefix,"添加入款列表",[$rkb]);
                        }
                    }
                }
            }
        }
        BotService::addLog($prefix,"处理完成","","end");
        return true;
    }
   
}