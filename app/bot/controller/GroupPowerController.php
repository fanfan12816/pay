<?php

namespace app\bot\controller;


use app\bot\logic\IndexLogic;
use app\common\service\bot\BotService;
use think\response\Json;
use app\common\model\bot\{BotUser,BotMessage,BotTggroup,BotGroupUser};


/**
 * index////负责处理：机器人自身进群，退群，被踢出群，以及被升级为管理员，权限变更 等事件处理
 * Class InlineQueryController
 * @package app\bot\controller
 */
class GroupPowerController extends BaseBotModeController
{


   
    public function index($message){  
        
        // 写入日志
        BotService::addLog("GroupPower","传入数据",$message,"start");

        // var_dump($message);
        $chatType = $message['chat']['type'];
        $chatId = $message['chat']['id'];
        $user = $message['from'];
        $type = $message['new_chat_member']['status']; 
        if($chatType == "group"){
            $chatType = "supergroup";  
        } 
        
     
        
        if(empty($type)){
            BotService::addLog("GroupPower","power阻断 没有type",$message,"end");
            return; 
        } 
        
         if($chatType == "private"){//私人
            #停用屏蔽
            if($type == 'kicked'){
                //  Db::name('account_tg')->where("bot",$this->tobot)->where('tgid', $chatId)->update(['del'=>1]);
                $del=BotUser::where("bot",$this->tobot)->where('user_id', $chatId)->update(["delete_time"=>time()]); 
                BotService::addLog("GroupPower","删除机器人事件",$del,"end");
                
            }else{
                BotService::addLog("GroupPower","机器人私人其他事件",$message,"end");
            }
            return 1;
            
        }else if($chatType == "supergroup"){//群组  
            $bot = $message['new_chat_member']['user']['username'];
            if($bot != $this->tobot){
                BotService::addLog("GroupPower","阻断-踢人事件",$message,"end");
                return;
            }
             
        
        
            #退出群 被踢出群消息
            if($type == 'left' || $type == 'kicked'){ 
                //  Db::name('bot_group')->where("bot",$this->tobot)->where("groupid",$chatId)->update(['del'=>1,'admin'=>0,'send'=>0]); 
                 $del=BotTggroup::where("bot",$this->tobot)->where('qunid', $chatId)->update(["delete_time"=>time()]); 
                 BotService::addLog("GroupPower","阻断-机器人退群或被提出群",$del,"end");
                 return true;
            } 
            
            // $model  =  new \plugin\keepbot\app\controller\Template;
            // $model = $model->qunadmin($chatId); 
            
            #通用的群列表 数据增加 
            $bot_group = BotTggroup::where("bot",$this->tobot)->where('qunid', $chatId)->find();
            if(empty($bot_group)){
                
                $group['bot']=$this->tobot; 
                $group['qunid']=$chatId; 
                $group['admin']=0; 
                $group['user_id']=$user['id']??""; 
                $group['quntitle']=$message['chat']['title']??"未设置群标题"; 
                $group['type']=$message['chat']['type'];
                // 写入日志
                BotService::addLog("GroupPower","机器人加群",$group);
                BotTggroup::create($group);    
            }else{
                $rt=BotTggroup::where("bot",$this->tobot)->where('qunid', $chatId)->update(["user_id"=>$user['id'],'admin'=>0]); 
                BotService::addLog("GroupPower","修改群状态",$rt);
            } 
            
            BotService::addLog("GroupPower","是否是新加入群",$bot_group);

            if(empty($bot_group)){//为空 或 del=1 都发送该消息    
                $text = ""; //固定默认消息
                BotService::addLog("GroupPower","text","查询入群发送信息");
                $Template = []; 
                if(empty($Template['reply_markup'])){   
                    $Template['reply_markup'] = json_encode([
                    "inline_keyboard"=>[   
                        [["text"=>'☎️私聊机器人',"url"=>"https://t.me/{$this->tobot}"],["text"=>'📜查看说明',"url"=>"https://t.me/{$this->tobot}?start=help"]]
                        ]
                    ]); 
                }   
                if(empty($Template['text'])){
                     $Template['text'] ="
                     加入群组:<b>{$message['chat']['title']}</b>\n群组ID:<code>{$chatId}</code>
                     ";
                }  
                // $tgSend=(new BotService())->send($this->tobot,"/sendMessage?chat_id={$chatId}&text={$Template['text']}&reply_markup={$Template['reply_markup']}");  
                $reData=[
                    "chat_id"=>$chatId,
                    "text"=>"{$Template['text']}",
                    "reply_markup"=>"",//$Template['reply_markup'],
                ];
                $tgSend=(new BotService())->send("/sendMessage",$reData);
                
                // 写入日志
                BotService::addLog("GroupPower","机器人加群发送信息",$tgSend,"end");
                
                return 1;
            }
            
 
            
             
            BotService::addLog("GroupPower","text","不是新群继续处理");
             
            switch ($type) {
                default: 
                    BotService::addLog("GroupPower","text","其他内容阻断{$type}");
                    return 1;
                    break; 
                    
                case 'member':
                    // Db::name('bot_group')->where("bot",$this->tobot)->where("groupid",$chatId)->update(['del'=>0,'admin'=>0]);              
                    $rt=BotTggroup::where("bot",$this->tobot)->where('qunid', $chatId)->update(["delete_time"=>time()]); 
                    BotService::addLog("GroupPower","member处理",$rt);
                    break;
                    
                case 'administrator':
                    BotService::addLog("GroupPower","text","成为管理员");
                    $Template  =  [];
                    BotService::addLog("GroupPower","模版消息",$Template);
                    
                    $rt=BotTggroup::where("bot",$this->tobot)->where('qunid', $chatId)->update(['admin'=>1]); 
                    BotService::addLog("GroupPower","修改管理员状态",$rt,"end");
                    break;
            }
            return 1;
            
        }else if($chatType == "channel"){ //频道
            BotService::addLog("GroupPower","阻断-暂未开启频道",$message,"end");
            
            
        } 
        
    }
    
}