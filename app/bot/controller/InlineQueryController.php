<?php

namespace app\bot\controller;


use app\bot\logic\IndexLogic;
use app\common\service\bot\BotService;
use think\response\Json;
use app\common\model\bot\{BotUser,BotMessage,BotTggroup,BotGroupUser};


/**
 * index负责处理：内联消息 也就是 @机器人时 触发这个文件
 * Class InlineQueryController
 * @package app\bot\controller
 */
class InlineQueryController extends BaseBotModeController
{


    
    public function index($inline_query){  
            // 写入日志
        BotService::addLog("inline_query","","内联处理开始");
        BotService::addLog("inline_query","传入数据",$inline_query);

        $bot = $this->tobot;
        $by = "";//$this->BOT['Admin']; 
        $img =  "";//$this->BOT['WEB_URL']."/app/".Request()->plugin."/img/yt".rand(1,3).".png";
        $type='help';
        $value = $inline_query['query'];   
        // 写入日志
        BotService::addLog("inline_query","查询关键字",$type);
        $tgret="";
        switch ($type) {
            
            
            default: 
                // $reply_markup = json_encode([
                //     "inline_keyboard"=>[
                //         [
                //             ["text"=>'成功回调',"callback_data"=>"id"],
                //             ["text"=>'失败回调',"callback_data"=>"id"]
                //         ]
                //     ]
                // ]);
                // $tgret =  (new BotService())->send($this->tobot,'/answerInlineQuery?inline_query_id='.$inline_query['id'].'&cache_time=1&results=[{"type":"article","id":"1","title":"@机器人引用说明","description":"暂未支持该指令\n点击查看详细信息","thumb_url":"'.$img.'","input_message_content":{"message_text":"\n\n*嘿嘿嘿...\n\n@'.$bot.'*","parse_mode":"Markdown","disable_web_page_preview":true},"reply_markup":'.$reply_markup.'}]'); 

                
                break;
        }
         
        // 写入日志
        BotService::addLog("inline_query","默认发送返回",$tgret);
        // 写入日志
        BotService::addLog("inline_query","text","内联查询结束");

        return true; 
    }
    
}