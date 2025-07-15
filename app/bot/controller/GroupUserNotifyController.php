<?php

namespace app\bot\controller;


use app\bot\logic\IndexLogic;
use app\common\service\bot\BotService;
use think\response\Json;
use app\common\model\bot\{BotUser,BotMessage,BotTggroup,BotGroupUser};


/**
 * index //负责处理：机器人所在群内 新用户入群，退群，被踢出群等消息处理
 * Class GroupUserNotifyController
 * @package app\bot\controller
 */
class GroupUserNotifyController extends BaseBotModeController
{


   
    public function index($chat_member){ 
        BotService::addLog("GroupUserNotify","传入数据",$chat_member,'end');

        $tgTime = $chat_member['date']; 
        $datem = date("Ym",$tgTime);
        $dated = date("Ymd",$tgTime);
        $chatId = $chat_member['chat']['id'];  
        $user = $chat_member['new_chat_member']['user'];
        $userId = $user['id']; 
        $type = $chat_member['new_chat_member']['status'];
        $old = $chat_member['old_chat_member']['status'];
        
        $group_user = BotGroupUser::where('qunid',$chatId)->where('userid',$userId)->find(); 
        
        if($type == "member" && $old=="left"){//新入群
            if(empty($group_user)){
                $sql['qunid']=$chatId;
                $sql['quninfo']=json_encode($chat_member['chat'],JSON_UNESCAPED_UNICODE);
                $sql['userid']=$userId;
                $sql['userinfo']=json_encode($user,JSON_UNESCAPED_UNICODE);
                $sql['ufrom']=json_encode($chat_member['from'],JSON_UNESCAPED_UNICODE);
                $sql['cretae_time']=$tgTime;
                // 写入日志
                BotService::addLog("GroupUserNotify","写入新人入群信息",$sql); 
                BotGroupUser::create($sql);  
            }else{
                BotGroupUser::where('qunid',$chatId)->where('id', $group_user['id'])
                    ->update(["delete_time"=>null,"exit_time"=>null,"userinfo"=>json_encode($user,JSON_UNESCAPED_UNICODE),"ufrom"=>json_encode($chat_member['from'],JSON_UNESCAPED_UNICODE),"cretae_time"=>$tgTime]);  
            } 
            

            BotService::addLog("GroupUserNotify","text","查询群");
            $group = BotTggroup::where('bot',$this->tobot)->where('qunid',$chatId)->find(); 
            BotService::addLog("GroupUserNotify","查询成功",$group); 
            
           //新用户进群
           $Template = []; 
           $username = $user['username']??"未设置";
           $namea = $user['last_name'] ?? "";
           $nameb = $user['first_name'] ?? ""; 
           $_text = "<b>新用户入群通知</b>\n<b>用户：</b>@{$username}\n<b>昵称：</b>{$namea}{$nameb}\n￣￣￣￣￣￣￣￣￣￣￣￣\n<b>{$Template['text']}</b>\n\n￣￣￣￣￣￣￣￣￣￣￣￣"; 
           if($Template['text']){
               $Template['text']=$_text;
           }
               
           if(empty($Template['photo'])){ 
               // (new BotService())->send($this->tobot,"/sendMessage?chat_id={$chatId}&text={$_text}{$group['welcome']}&reply_markup={$Template['reply_markup']}"); 
               $reData=[
                   "chat_id"=>$chatId,
                   "text"=>"{$Template['text']}",
                   "reply_markup"=>$Template['reply_markup'],
               ];
               $tgSend=(new BotService())->send("/sendMessage",$reData);   
           }else{
               // (new BotService())->send($this->tobot,"/sendPhoto?chat_id={$chatId}&caption={$_text}{$group['welcome']}&photo={$group['hyimg']}&reply_markup={$Template['reply_markup']}");     
               $reData=[
                   "chat_id"=>$chatId,
                   "caption"=>"{$Template['text']}",
                   "photo"=>"{$Template['photo']}",
                   "reply_markup"=>$Template['reply_markup'],
               ];
               $tgSend=(new BotService())->send("/sendPhoto",$reData);
           } 
           
            
        }else if($type == "left" ){//被踢出群或退群
           $delmode= BotGroupUser::where('qunid', $chatId)->where('userid', $userId)->update(["tfrom"=>json_encode($chat_member['from'],JSON_UNESCAPED_UNICODE),"exit_time"=>$tgTime]);

           $delmode->delete();
            
            
        }else if($type == "member" && $old=="administrator"){//成员权限变更为普通成员 
        
        
        
        }else if($type == "administrator"){//成员权限变更为管理员 
        
        
        } 
        
    }
    
}