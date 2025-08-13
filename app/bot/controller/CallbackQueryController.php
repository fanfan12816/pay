<?php

namespace app\bot\controller;


use app\bot\logic\IndexLogic;
use app\common\service\bot\BotService;
use think\response\Json;
use app\common\model\bot\{BotUser,BotMessage,BotTggroup,BotGroupUser};
use app\common\model\{Merchant,PayinOrder,PayoutOrder,BotGroup};
use app\common\service\{PayinCallbackService,PayoutCallbackService};
use app\common\service\ConfigService;

/**
 * index//负责处理：消息下方的按钮点击事件
 * Class InlineQueryController
 * @package app\bot\controller
 */
class CallbackQueryController extends BaseBotModeController
{

    public function index($message){ 
        
        // 写入日志
        BotService::addLog("CallbackQuery","","按钮开始");
        BotService::addLog("CallbackQuery","传入数据",$message);
        
        $type = $message['data'];
        $value = ""; 
        $tgid = $message['from']['id'];   
        $userName = $message['from']['username']??"未设定"; 
        $namea = $message['from']['first_name'] ?? ""; 
        $nameb = $message['from']['last_name'] ?? "";
        $nickName=$namea.($nameb?'·'.$nameb:"");
        $chat_id=$message['message']['chat']['id'];
        $message_id=$message['message']['message_id'];
        
        $order_sn="";
        $type="";
        $reply_markup="";
        
        $return=json_decode($message['data'],true);
        BotService::addLog("CallbackQuery","解析数据成功",$return);
        if(array_key_exists('status',$return)){
            $type=$return['status'];
            $order_sn=$return['order_sn'];
        }
        if(array_key_exists('t',$return)){
            $type=$return['t'];
            if($type==1){
                $type="payin";
            }
            if($type==2){
                $type="payout";
            }
            $order_sn=$return['sn'];
            $cbstatus=$return['sts'];
            $order_status=$return['osts'];
            if($order_status>2){
                $type = "yesCb";
                BotService::addLog("CallbackQuery","已经回调了",[$tgid,$nickName,$userName]);
            }
        }
        BotService::addLog("CallbackQuery","text","获取管理员");
        $admin = BotGroup::where(['chat_id' => $chat_id]) -> value('remark');
        $adminList=explode(',', $admin);
        BotService::addLog("CallbackQuery","获取管理员成功",[$adminList,$admin,$tgid]);
        if (!in_array($tgid, $adminList)) {
            $type = "notAdmin";
            BotService::addLog("CallbackQuery","不是管理员点击",[$tgid,$nickName,$userName]);
        }
        
        $Order=[];
        $_txt="默认消息";
        if($type==1||$type==2){
            BotService::addLog("CallbackQuery","text","获取订单;type:{$order_sn}");
            // 获取代收订单
            if(!empty($order_sn)){
                $Order=PayinOrder::where(['order_sn' => $order_sn])->findOrEmpty();
                BotService::addLog("CallbackQuery","订单获取成功",$Order);
                if($Order->isEmpty()){
                    $type="orderNot";
                    $_txt="订单不存在";
                }else{
                    if($Order->status>1){
                        $type="orderNot";
                        $_txt="订单已经回调";
                    }
                }
            }else{
                $type="orderNot";
                $_txt="订单号不存在";
            }
        }
        $stlist=["等待付款","待确认","审核成功","审核失败","超时关闭","手动关闭"];
        if($type=="payin"){
            BotService::addLog("CallbackQuery","text","获取代收订单;type:{$order_sn}");
            // 获取代收订单
            if(!empty($order_sn)){
                $Order=PayinOrder::where(['order_sn' => $order_sn])->findOrEmpty();
                BotService::addLog("CallbackQuery","订单获取成功",$Order);
                if($Order->isEmpty()){
                    $type="orderNot";
                    $_txt="订单不存在";
                }else{
                    if($Order->status>1){
                        $type="orderNot";
                        @$_txt="订单已经回调,订单状态：".$stlist[$Order->status];
                    }
                }
            }else{
                $type="orderNot";
                $_txt="订单号不存在";
            }
        }
        if($type=="payout"){
            BotService::addLog("CallbackQuery","text","获取代付订单;type:{$order_sn}");
            // 获取代收订单
            if(!empty($order_sn)){
                $Order=PayoutOrder::where(['order_sn' => $order_sn])->findOrEmpty();
                BotService::addLog("CallbackQuery","订单获取成功",$Order);
                if($Order->isEmpty()){
                    $type="orderNot";
                    $_txt="订单不存在";
                }else{
                    if($Order->status>1){
                        $type="orderNot";
                         @$_txt="订单已经回调,订单状态：".$stlist[$Order->status];
                    }
                }
            }else{
                $type="orderNot";
                $_txt="订单号不存在";
            }
        }
        BotService::addLog("CallbackQuery","text","开始匹配;type:{$type}");
        switch ($type) {
            default: 
                $_txt="{$type},未支持";
                break;
            case 'payin':  
                switch ($cbstatus) {
                    default: 
                        $_txt="{$cbstatus},未支持";
                    break;
                case '2':   
                    BotService::addLog("CallbackQuery","","开始代收匹配[回调成功];订单号:{$order_sn}");
                    
                    BotService::addLog("BotpayinCallback","text","开始成功回调;订单号:{$order_sn}");
                    BotService::addLog("BotpayinCallback","回调用户",[$tgid,$nickName,$userName]);
                    $cbText="已经成功回调";
                    $cb=PayinCallbackService::callback($order_sn,2);
                    BotService::addLog("BotpayinCallback","回调结果",$cb);
                    if(!$cb['code']==200){
                        $cbText=$cb['msg'];
                    }
                    BotService::addLog("BotpayinCallback","text","回调结束","end");
                    
                    $_txt="订单号:{$order_sn};$cbText";
                    $reply_markup=json_encode([
                        "inline_keyboard"=>[
                            [["text"=>$cbText,"callback_data"=>json_encode(["sn"=>$order_sn,"sts"=>2,"osts"=>2,"t"=>$type])]]
                        ]
                    ]);
                    
                    BotService::addLog("CallbackQuery","text","已经回调操作成功");
                break;
                case '3':   
                    BotService::addLog("CallbackQuery","","开始匹配[回调失败];订单号:{$order_sn}");
                    
                    BotService::addLog("BotpayinCallback","text","开始失败回调;订单号:{$order_sn}");
                    BotService::addLog("BotpayinCallback","回调用户",[$tgid,$nickName,$userName]);
                    $cbText="已经失败回调";
                    $cb=PayinCallbackService::callback($order_sn,3);
                    BotService::addLog("BotpayinCallback","回调结果",$cb);
                    if(!$cb['code']==200){
                        $cbText=$cb['msg'];
                    }
                    BotService::addLog("BotpayinCallback","text","回调结束","end");
                    
                    $_txt="订单号:{$order_sn};$cbText";
                    $reply_markup=json_encode([
                        "inline_keyboard"=>[
                            [["text"=>$cbText,"callback_data"=>json_encode(["sn"=>$order_sn,"sts"=>3,"osts"=>3,"t"=>$type])]]
                        ]
                    ]);
                    
                    BotService::addLog("CallbackQuery","text","已经回调操作成功");
                    
                    break;
                }
            break;
            case 'payout':  
                switch ($cbstatus) {
                    default: 
                        $_txt="{$cbstatus},未支持";
                    break;
                case '2':   
                    BotService::addLog("CallbackQuery","","开始代付匹配[回调成功];订单号:{$order_sn}");
                    
                    BotService::addLog("BotpayoutCallback","text","开始成功回调;订单号:{$order_sn}");
                    BotService::addLog("BotpayoutCallback","回调用户",[$tgid,$nickName,$userName]);
                    $cbText="已经成功回调";
                    $cb=PayoutCallbackService::callback($order_sn,2);
                    BotService::addLog("BotpayoutCallback","回调结果",$cb);
                    if(!$cb['code']==200){
                        $cbText=$cb['msg'];
                    }
                    BotService::addLog("BotpayoutCallback","text","回调结束","end");
                    
                    $_txt="订单号:{$order_sn};$cbText";
                    $reply_markup=json_encode([
                        "inline_keyboard"=>[
                            [["text"=>$cbText,"callback_data"=>json_encode(["sn"=>$order_sn,"sts"=>2,"osts"=>2,"t"=>$type])]]
                        ]
                    ]);
                    
                    BotService::addLog("CallbackQuery","text","已经回调操作成功");
                break;
                case '3':   
                    BotService::addLog("CallbackQuery","","开始匹配[回调失败];订单号:{$order_sn}");
                    
                    BotService::addLog("BotpayoutCallback","text","开始失败回调;订单号:{$order_sn}");
                    BotService::addLog("BotpayoutCallback","回调用户",[$tgid,$nickName,$userName]);
                    $cbText="已经失败回调";
                    $cb=PayoutCallbackService::callback($order_sn,3);
                    BotService::addLog("BotpayoutCallback","回调结果",$cb);
                    if(!$cb['code']==200){
                        $cbText=$cb['msg'];
                    }
                    BotService::addLog("BotpayoutCallback","text","回调结束","end");
                    
                    $_txt="订单号:{$order_sn};$cbText";
                    $reply_markup=json_encode([
                        "inline_keyboard"=>[
                            [["text"=>$cbText,"callback_data"=>json_encode(["sn"=>$order_sn,"sts"=>3,"osts"=>3,"t"=>$type])]]
                        ]
                    ]);
                    
                    BotService::addLog("CallbackQuery","text","已经回调操作成功");
                    
                    break;
                }
            break;
            case 'notAdmin':   
                $_txt="不是管理,不能使用";
            break;
            case 'orderNot':  
                BotService::addLog("CallbackQuery","text","处理订单异常{$order_sn}");
                
                $reply_markup=json_encode([
                    "inline_keyboard"=>[
                        [["text"=>$_txt,"callback_data"=>json_encode(["order_sn"=>$order_sn,"status"=>2])]]
                    ]
                ]);
                BotService::addLog("CallbackQuery","text","异常处理成功");
            break;
        }//switch end
        $tgSend="";
        $tgSend1="";
        if(!empty($_txt)){
            $reData=[
                "callback_query_id"=>$message['id'],
                "text"=>$_txt,
                "show_alert"=>0
            ];
            $tgSend=(new BotService())->send("/answerCallbackQuery",$reData);
        }
        if(!empty($reply_markup)){
            $reData=[
                "chat_id"=>$chat_id,
                "message_id"=>$message_id,
                "reply_markup"=>$reply_markup
            ];
            $tgSend1=(new BotService())->send("/editMessageReplyMarkup",$reData);
        }
        BotService::addLog("CallbackQuery","返回数据",[$tgSend,$tgSend1]);
        BotService::addLog("CallbackQuery","","按钮结束");
        return true;
        
        
    }
    
    
}