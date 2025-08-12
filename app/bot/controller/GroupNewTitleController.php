<?php

namespace app\bot\controller;


use app\bot\logic\IndexLogic;
use app\common\service\bot\BotService;
use think\response\Json;
use app\common\model\bot\{BotUser,BotMessage,BotTggroup,BotGroupUser};


/**
 * index////负责处理：机器人所在的群标题名称改变时触发 
 * Class InlineQueryController
 * @package app\bot\controller
 */
class GroupNewTitleController extends BaseBotModeController
{


   
    public function index($message){
        // 写入日志
        BotService::addLog("GroupNewTitle","","处理开始");
        BotService::addLog("GroupNewTitle","传入数据",$message);

        $tgTime = $message['date']; 
        $datem = date("Ym",$tgTime);
        $dated = date("Ymd",$tgTime);
        $chatType = $chatId = $message['chat']['type']; 
        $chatId = $message['chat']['id']; 
        $newTitle = $message['new_chat_title'];
        
        
        if($chatType == "group"){
            $chatType = "supergroup";  
        } 
        
        
        switch ($chatType) {
            default:
                // echo "不支持的群频道修改标题消息类型\n";
                BotService::addLog("GroupNewTitle","text","不支持的群频道修改标题消息类型");
                break;
                
            case 'supergroup':
                $qun=BotTggroup::where("bot",$this->tobot)->where('qunid', $chatId)->update(["quntitle"=>$newTitle]); 
                BotService::addLog("GroupNewTitle","修改群名称",$qun);
                break;
            
            
            case 'channel':
                BotService::addLog("GroupNewTitle","text","不支持修改群频道标题");
                // Db::name('bot_channel')->where('pid', $chatId)->update(["title"=>$newTitle]); 
                break; 
        }
        return 1;
        
    }
    
}