<?php


namespace app\bot\controller;


use app\common\service\bot\BotService;
use think\response\Json;
use app\common\model\bot\{BotUser,BotMessage,BotTggroup,BotGroupUser};
use app\common\service\ConfigService;

/**
 * index 负责处理：所有菜单命令事件(包括私聊 群聊) 只要消息内容是以：/ 开头的消息这个文件接收处理
 * Class CommandControlle
 * @package app\bot\controller
 */
class CommandControlle extends BaseBotModeController
{
      
    
    public function index($message){  
         // 写入日志
        BotService::addLog("Command","传入数据",$message,"start");
         
        $chatType = $message['chat']['type']; //会话类型 私人 群组 频道
        $chatId = $message['chat']['id'];//会话聊天ID
        $tgid = $message['from']['id'];//用户ID  
        
        if($chatType == "group"){
            $chatType = "supergroup";  
        } 
        
        preg_match('/\/(\w+)\s*(.*)/i', $message['text'], $com); 
        if(count($com) != 3){ 
            return true;
        } 
        
         
        
        $type = $com[1]; //正则取得的菜单命令内容
        $value = $com[2];
         
         
        
        BotService::addLog("Command","text","开始处理命令=>{$type}");
        switch ($type) {  
            default:   //没有case对应命令时采用模式读数据库模式
                BotService::addLog("Command","不支持命令-{$type}",$message,"end");
                return 1;
            break;
            case 'start':   
                $namea = $message['from']['first_name'] ?? ""; 
                $nameb = $message['from']['last_name'] ?? "";
                if($chatType=="supergroup"){
                    return 1;
                }
                $reData=[
                    "chat_id"=>$message['chat']['id'],
                    "text"=>"你好：<b>{$namea}·{$nameb}</b>\n你的飞机ID号是：<code>{$tgid}</code>",
                    "reply_markup"=>$Template['reply_markup'],
                    // "reply_to_message_id"=>$message['message_id']
                ];
                $tgSend=(new BotService())->send("/sendMessage",$reData);  
                
                return 1;
            break;
            
                                
        }
    }
    
}