<?php


namespace app\bot\controller;


use app\bot\logic\IndexLogic;
use app\common\service\bot\BotService;
use think\response\Json;
use app\common\model\bot\{BotUser,BotMessage,BotGroup,BotGroupUser};
use app\common\service\ConfigService;

/**
 * index 
 * Class TemplateController
 * @package app\bot\controller
 */
class TemplateController extends BaseBotModeController
{

    
    public function qunadmin($chatId):array{
        $res =[];//BotService::getChatAdministrators($this->tobot,$chatId);
        $text = "";
        $list=$res['"result"']??[];
        foreach ($list as $value) {  
            if(empty($value['user']['username'])){//未设置用户名或者注销了的号过滤掉
                continue;
            }
            $text .= "@".$value['user']['username']." ";   
        } 
        return ["code"=>1,"msg"=>"获取成功","text"=>$text];
    }
    
    
    public function help():array{//start help
        $text = "
        <b>机器人使用说明：</b>
        暂未添加说明
        
        ";
        $text = preg_replace('/\n[^\S\n]*/i', "\n", $text);
        return ["code"=>1,"msg"=>"获取成功","text"=>urlencode($text)];
    
    }
    
    
    


    #通用按钮模板：名称name，私人&群聊，1命令 2事件，自定义传参字符（比如：start=xxx 后面的自定义）
    public function reply_markup($command,$chatType,$type=2,$startval=""):array{    
        BotService::addLog("reply_markup","","处理开始");
        $rqData=[
            "command"=>$command,
            "chatType"=>$chatType,
            "type"=>$type,
            "startval"=>$startval
        ];
        BotService::addLog("reply_markup","传入数据",$rqData);
        
        $commands = [];//BotCommands::where("bot_id",$this->tobot)->where("chatType",$chatType)->where("command",$command)->where("type",$type)->find();
      
        if(empty($commands)){ 
            $rest=["code"=>0,"msg"=>"获取失败,没有添加对应数据","text" =>"","reply_markup" =>""];
            BotService::addLog("reply_markup","默认返回数据",$rest);
            return $rest; 
        }
    
        $so =[]; 
        array_push($so,'comId');
        array_push($so,'=');
        array_push($so,$commands['id']);  
        array_push($so,'type');
        array_push($so,'=');
        array_push($so,$commands['reply_markup']);  
        $so = array_chunk($so,2);//拆分  
        BotService::addLog("reply_markup","查询指令数据",$so);
        $markup = BotMarkup::where([$so])->order('sortId asc')->select();
        if($markup->isEmpty()){
            $rest=["code"=>0,"msg"=>"获取失败,事件没有添加对应按钮","photo"=>"{$commands['photo']}","text" =>$commands['text'],"reply_markup" =>""]; 
            BotService::addLog("reply_markup","返回数据",$rest);
            return $rest; 
        }
        BotService::addLog("reply_markup","查询指令成功",$markup);
        // $tgApi=ConfigService::get('telegram', 'api', 'https://api.telegram.org/');
        // // return $tgApi;
        // $bot=BotService::getBotDetail($this->tobot,"bot");

        // $token=$bot['bot_token']??"";
        $keyboard[$commands['reply_markup']]=[];
        $d1 = array();
            foreach ($markup as $value) {   
                if(empty($value['class']) && $commands['reply_markup']!="keyboard"){ //keyboard 时允许class 空
                    continue;   
                } 
                if(!array_key_exists($value['aid'],$d1)){//行
                    $d1[$value['aid']] = [];
                } 
                if($value['type'] == "inline_keyboard"){//消息下方按钮
                    $d2= array();
                    $d2['text'] = $value['text'];  
                    if($value['class'] == "web_app" || $value['class'] == "login_url"){
                        $class['url']=$value[$value['class']]; //构建json
                        $d2[$value['class']] = $class; //二次json插入
                    }else if($value['class'] == "excel"){
                        $d2["class"] = "url";
                        $d2["url"] = "https://t.me/{$this->tobot}?start={$startval}"; 
                    }else if($value['class'] == "group"){
                        $d2["class"] = "url";
                        $d2["url"] = "https://t.me/{$this->tobot}?startgroup=true"; 
                    }else if($value['class'] == "lianxiren"){
                        $d2["class"] = "url";
                        $d2["url"] = "https://t.me/{$value['url']}"; 
                    }else{  
                        $d2[$value['class']] = $value[$value['class']];//对应字段的值
                    } 
                    array_push($d1[$value['aid']],$d2); 
                    
                     
                    
                }else{
                    array_push($d1[$value['aid']],["text"=>$value['text']]);//回复键盘按钮
                } 
            }
            
        $keyboard[$commands['reply_markup']] = array_values($d1); 
        $reply_markup = json_encode($keyboard);
        $_text = preg_replace('/\n[^\S\n]*/i', "\n", $commands['text']);
        $_text = urlencode($_text); 
        $rest=["code"=>1,"msg"=>"获取成功","text"=>"{$_text}","photo"=>"{$commands['photo']}","reply_markup" =>$reply_markup ];   
        BotService::addLog("reply_markup","返回数据",$rest);
        return $rest;
      
      
    } 
}