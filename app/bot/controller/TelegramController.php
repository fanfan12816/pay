<?php

namespace app\bot\controller;


use app\common\service\bot\BotService;
use think\response\Json;
use app\common\model\bot\{BotUser,BotMessage,BotTggroup,BotGroupUser};
use think\facade\Cache;



/**
 * index
 * Class IndexController
 * @package app\bot\controller
 */
class TelegramController extends BaseBotController
{


    public array $notNeedLogin = ['index'];


    /**
     * @notes 首页数据
     * @return Json
     */
    public function index()
    {
        $data = $this->request->all();  
        @$update_id=$data['update_id']??"";
        
        // 写入日志
        BotService::addLog("receive","接收机器人数据",$data,"start");
        
        if(!$update_id){
            // 写入日志
            BotService::addLog("receive","没有update_id",$data,"end");
            return 1;
        }
        $cacheKey="update_id_".$update_id;
        // 判断是否重复通知

        if(Cache::get($cacheKey)){
            BotService::addLog("receive","重复通知",$data,"end");
            return 1;  
        }

        Cache::set($cacheKey,$update_id, 3600);

        BotService::addLog("receive","text","写入聊天记录");
        @$addbotmsg=BotMessage::create([
            'update_id' => $data["update_id"],
            'message' => json_encode($data,JSON_UNESCAPED_UNICODE),
        ]);
        // 写入聊天记录
        BotService::addLog("receive","写入聊天回调",$addbotmsg);

        // 写入日志
        BotService::addLog("receive","text","检查用户数据");
        
        // return;
        #这里检查用户数据 
        if(!empty($data['message']['chat']['type'])){
            $chatId = $data['message']['chat']['id'];
            
             if($data['message']['chat']['type'] =="supergroup" || $data['message']['chat']['type'] =="group"){
                 #检查群
                BotService::addLog("receive","text","开始查群");
                $group =  BotTggroup::where("bot",$this->tobot)->where('qunid', $chatId)->find();
                BotService::addLog("receive","检查群完毕",$group);
                 if(empty($group)){
                    $group['bot']=$this->tobot; 
                    $group['qunid']=$chatId; 
                    $group['admin']=0; 
                    $group['quntitle']=$data['message']['chat']['title']??"未设置群标题"; 
                    $group['type']=$data['message']['chat']['type'];
                    // 写入日志
                    BotService::addLog("receive","写入新群",$group);
                    $add=BotTggroup::create($group);
                    BotService::addLog("receive","写入新群完毕",$$add);
                 } 
                #检查群成员 
                BotService::addLog("receive","text","开始检查群成员");
                $user_id =  $data['message']['from']['id'];
                $group_user = BotGroupUser::where("qunid",$chatId)->where("userid",$user_id)->find();
                BotService::addLog("receive","检查群成员完毕",$group_user);
                if(empty($group_user)){
                    $group_user['qunid']=$chatId;
                    $group_user['quninfo']=json_encode($data['message']['chat'],JSON_UNESCAPED_UNICODE);
                    $group_user['userid']=$user_id;
                    $group_user['userinfo']=json_encode($data['message']['from'],JSON_UNESCAPED_UNICODE); 
                    $group_user['end_time']=$data['message']['date'];
                    $group_user['cretae_time']=$data['message']['date'];
                    // 写入日志
                    BotService::addLog("receive","开始写入群成员",$group_user);
                    $add=BotGroupUser::create($group_user);  
                    BotService::addLog("receive","写入群成员完毕",$add);
                }else{
                    BotService::addLog("receive","text","开始检查群成员是否改名");
                    @$tgx = $data['message']['from']['first_name'] ?? "";
                    @$tgx .= $data['message']['from']['last_name'] ?? "";
                    @$sqx = $group_user['userinfo']['first_name'] ?? "";
                    @$sqx .= $group_user['userinfo']['last_name'] ?? ""; 
                    
                    if($tgx != $sqx){ 
                       BotService::addLog("receive","text","开始检查群成员改名了");
                       $username = $data['message']['from']['username'] ?? "未设置用户名"; 
                       $text = "<b>用户修改昵称提示</b>
                       用户名：@{$username}
                       原昵称：<b>{$sqx}</b>
                       新昵称：<b>{$tgx}</b>
                       \n请注意规避风险,谨防假冒管理员" ;
                       BotGroupUser::where("qunid",$chatId)->where("userid",$user_id)->update(['userinfo'=>json_encode($data['message']['from'],JSON_UNESCAPED_UNICODE)]);
                        //   (new BotService())->send($this->tobot,"/sendMessage?chat_id={$chatId}&text={$text}"); 
                        $reData=[
                            "chat_id"=>$chatId,
                            "text"=>"{$text}"
                        ];
                        $tgSend=(new BotService())->send($this->tobot,"/sendMessage",$reData); 
                        BotService::addLog("receive","发送改名通知",$tgSend);
                    }   
                    BotService::addLog("receive","text","群成员改名查询完毕");
                }
                 
                BotService::addLog("receive","text","群检查结束");
             }else if($data['message']['chat']['type'] == "private"){
                 #检查用户
                 BotService::addLog("receive","text","私聊检查开始");
                 BotService::addLog("receive","text","开始检查用户");
                 $user =  BotUser::where("bot",$this->tobot)->where('user_id', $chatId)->find();
                 BotService::addLog("receive","查询用户完毕","{$user}");
                 if(empty($user)){ 
                     
                     @$user['bot']=$this->tobot; 
                     @$user['user_id']=$chatId; 
                     @$user['username']=$data['message']['from']['username']??"未设置用户名";  
                     @$user['name']=$data['message']['from']['first_name']??""; 
                     @$user['name'].=$data['message']['from']['last_name']??"";  
                     @$user['language_code']=$data['message']['from']['language_code']??""; 
                     @$user['userinfo']=json_encode($data['message']['from'],JSON_UNESCAPED_UNICODE); 
                     // 写入日志
                     BotService::addLog("receive","写入用户数据开始",$user);
                     $add=BotUser::create($user);   
                     BotService::addLog("receive","写入用户完毕","{$add}");
                    
                 }
                 BotService::addLog("receive","text","用户检查结束");
                 
             } 
        }
        // 写入日志
        BotService::addLog("receive","text","检查群、用户、全部结束");
        #检查结束
        BotService::addLog("receive","text","开始跳转事件");
        
        #----------
        if(!empty($data['message']['text'])){ //文本消息
            BotService::addLog("receive","","文本处理开始");
            $act = new MessageController();
            @$rt=$act->index($data['message']); 
            BotService::addLog("receive","文本处理",$rt,"end");
            return 1; 
        }else if(!empty($data['message']['photo'])){ //图片消息
            BotService::addLog("receive","","图片消息开始");
            if(empty($data['message']['caption'])){//没有说明文字阻断 
                BotService::addLog("receive","没有说明文字阻断",$data,"end");
                return 1;  
            }  
            $act = new MessageController();
            @$rt=$act->index($data['message'],"photo"); 
            BotService::addLog("receive","图片消息处理",$rt,"end");
            return 1; 
        }else if(!empty($data['inline_query'])){  //@内联查询事件 
            BotService::addLog("receive","","内联查询事件开始");
            $inline_query = new InlineQueryController();
            @$rt=$inline_query->index($data['inline_query']); 
            BotService::addLog("receive","内联查询事件处理",$rt,"end");
            return 1;    
        }else if(!empty($data['callback_query']['data'])){  //按钮点击事件
            BotService::addLog("receive","","按钮点击事件处理开始");
            $callback_query = new CallbackQueryController();
            @$rt=$callback_query->index($data['callback_query']); 
            BotService::addLog("receive","按钮点击事件处理",$rt,"end");
            return 1;    
        }else if(!empty($data['my_chat_member']['new_chat_member']['status'])){ //机器人群事件
            #$type = $data['my_chat_member']['new_chat_member']['status']; 
            BotService::addLog("receive","","机器人群事件开始");
            $group_power = new GroupPowerController();
            @$rt=$group_power->index($data['my_chat_member']);  
            BotService::addLog("receive","机器人群事件处理",$rt,"end");
            return 1; 
        }else if(!empty($data['chat_member']['new_chat_member']['user'])){ //通用群事件
            BotService::addLog("receive","","通用群事件处理开始");
            $group_new = new GroupUserNotifyController();
            @$rt=$group_new->index($data['chat_member']); 
            BotService::addLog("receive","通用群事件处理",$rt,"end");
            return 1;  
        }else if(!empty($data['message']['new_chat_title'])){ //群修改标题事件
            BotService::addLog("receive","","群修改标题事件处理开始");
            $group_newTitle = new GroupNewTitleController();
            @$rt=$group_newTitle->index($data['message']); 
            BotService::addLog("receive","群修改标题事件处理",$rt,"end");
            return 1;  
        }else if(!empty($data['channel_post']['new_chat_title'])){ //频道修改标题事件
            BotService::addLog("receive","","频道修改标题事件开始");
            $group_newTitle = new GroupNewTitleController();
            @$rt=$group_newTitle->index($data['channel_post']); 
            BotService::addLog("receive","频道修改标题事件处理",$rt,"end");
            return 1;  
        }else if(!empty($data['callback_query']['game_short_name'])){  //按钮点击事件
            BotService::addLog("receive","","按钮点击事件处理开始");
            $callback_game = new CallbackGameController();
            @$rt=$callback_game->index($data['callback_query']); 
            BotService::addLog("receive","按钮点击事件处理",$rt,"end");
            return 1;    
        }else if(!empty($data['message']['migrate_to_chat_id'])){ //群升级消息
            BotService::addLog("receive","","群升级消息处理开始");
            $group_move = new GroupMoveController();
            @$rt=$group_move->index($data['message']); 
            BotService::addLog("receive","群升级消息处理",$rt,"end");
            return 1;    
        }
        
        
        
        BotService::addLog("receive","【".$data['bot']."】以上消息不支持[receive]",$data,"end");
        // echo "--------【".$request->plugin."】以上消息不支持------------\n"; 
        return 1; 
    }


}