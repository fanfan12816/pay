<?php

namespace app\bot\controller;


use app\bot\logic\IndexLogic;
use app\common\service\bot\BotService;
use think\response\Json;
use app\common\model\bot\{BotUser,BotMessage,BotTggroup,BotGroupUser};


/**
 * index////负责处理：游戏game 按钮点击事件
 * Class InlineQueryController
 * @package app\bot\controller
 */
class CallbackGameController extends BaseBotModeController
{


    public function index($message){ 
         // 写入日志
         BotService::addLog("CallbackGame","","处理开始");
         BotService::addLog("CallbackGame","传入数据",$message);
        $chatId = $message['from']['id'];
        //$game = $message['game_short_name'];
        // $tgSend=(new BotService())->send($this->tobot,"/answerCallbackQuery?callback_query_id={$message['id']}&text=很好&show_alert=0");
        // $reData=[
        //     "callback_query_id"=>$message['id'],
        //     "text"=>"很好",
        //     "show_alert"=>0
        // ];
        // (new BotService())->send($this->tobot,"/answerCallbackQuery",$reData);
        // $tgSend=(new BotService())->send($this->tobot,"/answerCallbackQuery?callback_query_id={$message['id']}&url=https://bot.jizhangbot.com/?uid={$chatId}");
        return;
        
    }
}