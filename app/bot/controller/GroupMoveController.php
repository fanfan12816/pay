<?php

namespace app\bot\controller;


use app\bot\logic\IndexLogic;
use app\common\service\bot\BotService;
use think\response\Json;
use app\common\model\bot\{BotUser,BotMessage,BotTggroup,BotGroupUser};


/**
 * index//机器人所在群普通群升级为超级群事件 （首次设置公开群链接触发）
 * Class InlineQueryController
 * @package app\bot\controller
 */
class GroupMoveController extends BaseBotModeController
{

    public function index($message){ 

        // 写入日志
        BotService::addLog("GroupMove","","处理开始");
        BotService::addLog("GroupMove","传入数据",$message);
        
        $tgTime = $message['date']; 
        $datem = date("Ym",$tgTime);
        $dated = date("Ymd",$tgTime);
        #$chatType = $message['chat']['type'];
        $chatId = $message['chat']['id'];
        $chatTitle = $message['chat']['title']??"未设定标题";
        $move = $message['migrate_to_chat_id']; 
        
        $jsoninfo = json_encode($message['chat']);
        
        BotTggroup::where("bot",$this->tobot)->where('qunid', $chatId)->update(['del'=>1]);
        BotGroupUser::where("qunid",$chatId)->update(['qunid'=>$move,'quninfo'=>$jsoninfo]);
        
        
        $text = "
        <b>注意：
        群升级为→超级群</b> 
        
        
        <b>为什么如此?： 
        首次设定：公开群组链接</b>
        
        只有群从来没有设置过公开群链接的群才会出现(只会出现1次)请重新设定机器人为管理员！
        ";

        // $tgSend=(new BotService())->send($this->tobot,"/sendMessage?chat_id={$move}&text={$text}"); 
        $reData=[
            "chat_id"=>$move,
            "text"=>"{$text}",
        ];
        $tgSend=(new BotService())->send($this->tobot,"/sendMessage",$reData);
        BotService::addLog("GroupMove","发送返回",$tgSend);
        return 1;
        
        
    }
    
}