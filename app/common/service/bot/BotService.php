<?php


namespace app\common\service\bot;

use app\common\service\ConfigService;
use app\common\service\FileService;
use app\common\model\bot\{BotMessage,BotTggroup};

class BotService
{
    public static $tgApi = "";
    public static $token = "";
    public static $bot = "xinyibot";
    function __construct() {
       self::$tgApi = ConfigService::get('telegram_api','https://api.telegram.org/');
       self::$token = ConfigService::get('bot_token','');
   }
    /**
     * @notes 查看更新钩子
     * @param $id 机器人ID
     * @param $type  getWebhookInfo=>查看回调，deleteWebhook=》删除回调，设置回调=》setWebhook
     * */
    public static function webhook($type="getWebhookInfo")
    {
        // return [self::$token,self::$bot,self::$tgApi];
        if(self::$token){
            $url = self::$tgApi . 'bot' . self::$token . '/'.$type;
            $data = [
                'url' => FileService::getFileUrl(). 'bot/telegram?bot='.self::$bot.'&sign=' . md5(self::$token),
                'max_connections'=>50,
                'allowed_updates'=>[
                    "update_id",
                    "message",
                    "edited_message",
                    "channel_post",
                    "edited_channel_post",
                    "inline_query",
                    "chosen_inline_result",
                    "callback_query",
                    "shipping_query",
                    "pre_checkout_query",
                    "poll",
                    "poll_answer",
                    "my_chat_member",
                    "chat_member",
                    "chat_join_request"
                ],
            ];
            if($type=="deleteWebhook"){
                $url.="?drop_pending_updates=true";
                $data=[];
            }
            $result = BotService::posturl($url, $data);
            return $result;
        }else{
            return false;
        }
    }
    
    //logOut  close
    public static function botStatus($type="getMe")
    {
        if(self::$token){
            $url = self::$tgApi . 'bot' . self::$token . '/'.$type ;
            if($type=="deleteWebhook"){
                $url.="?drop_pending_updates=true";
            }
            $result = BotService::geturl($url);
            return $result;
        }else{
            return false;
        }
    }
    
    // 机器人通用方法
    public static function telegramFun($type="getMe",$data=[]){
        if(self::$token){
            $url = self::$tgApi . 'bot' . self::$token . '/'. $type ;
            $result = BotService::posturl($url,$data);
            return $result;
        }else{
            return false;
        }
    }
    
    // 设置机器人的名称
    public static function setMyName($data=[]){
        if(self::$token){
            $url = $tgApi . 'bot' . self::$token . '/setMyName' ;
            $result = BotService::posturl($url,$data);
            return $result;
        }else{
            return false;
        }
    }
    
    // 获取群
    public static function getBotGroupDetail($id,$t="bot")
    {
        if($id){
            $where=[];
            switch ($t) {
                case 'id':
                    $where=["id" => $id];
                break;
                case 'bot':
                    $where=['bot' => $id];
                break;
                case 'botid':
                    $where=['qunid' => $id];
                break;
                default:
                    $where=['bot' => $id];
                break;
            }
            $bot = BotTggroup::where($where)->find();
            return $bot;
        }else{
            return false;
        }
    }
    
    
    public static function geturl($url){
            $headerArray =array("Content-type:application/json;","Accept:application/json");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArray);
            $output = curl_exec($ch);
            curl_close($ch);
            $output = json_decode($output,true);
            return $output;
    }
     
     
    public static function posturl($url,$data){
            $data  = json_encode($data);    
            $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json");
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            curl_close($curl);
            return json_decode($output,true);
    }
    
    // 机器人日志写入
    public static function addLog($log="base",$title="",$text="",$status="")
    {
        $logPath=substr(str_replace('\\','/',dirname(__FILE__)),0,stripos(str_replace('\\','/',dirname(__FILE__)),'/app'));
        // return $logPath;
        // 机器人回调日志
        $dtTmp = date('Ymd');
        $shi = date("H",time());
        $path=$logPath."/runtime/telegram/log/$dtTmp/$log/";
        if (!file_exists($path)) {
            mkdir($path,0777,true);
        } 
        $file = fopen($path."$shi.log","a+");

        if(!is_string($text)){
            $text=json_encode($text,JSON_UNESCAPED_UNICODE);
        }
        if($status=="start"){
            fwrite($file, "--------------------------------[".date('Y-m-d H:i:s')."]--------------------------------\n");
            fwrite($file, "-------------------------------------日志写入开始------------------------------------\n");
            fwrite($file, "-------------------------------------------------------------------------------------\n\n");
        }
        
        if($title=="text"){
            fwrite($file, "\n[title][".date('Y-m-d H:i:s') ."]$text \n");
        }else{
            if($text){
                fwrite($file, "--------------------------$title--------------------------\n");
                fwrite($file, "[".date('Y-m-d H:i:s')."]\n\n");
                if(!is_string($text)){
                    $text=json_encode($text,JSON_UNESCAPED_UNICODE);
                }
                fwrite($file, $text);
                fwrite($file, "\n--------------------------E----N----D--------------------------\n");
            }
        }
        
        if($status=="end"){
            fwrite($file, "\n-------------------------------------------------------------------------------------\n");
            fwrite($file, "-------------------------------------日志写入结束------------------------------------\n");
            fwrite($file, "--------------------------------[".date('Y-m-d H:i:s')."]--------------------------------\n\n\n");
        }
    }
    
    //查询消息记录
    public static function getMessage($id)
    {
        if($id){
            $msg=BotMessage::where(['update_id' => $id])->find();
            return $msg;
        }else{
            return false;
        }
    }
    // 禁止聊天
    public static function setChatPermissions($chat_id)
    {
        $group=BotService::getBotGroupDetail($chat_id);
        $isChat=$group['is_chat']??0;
        if(self::$token){
            $url = self::$tgApi . 'bot' . self::$token . '/setChatPermissions';
            $data = [
                "chat_id"=>$chat_id,
                "permissions"=>[
                    "can_send_messages"=>$isChat==0?false:true,
                    "can_send_audios"=>$isChat==0?false:true,
                    "can_send_documents"=>$isChat==0?false:true,
                    "can_send_photos"=>$isChat==0?false:true,
                    "can_send_videos"=>$isChat==0?false:true,
                    "can_send_video_notes"=>$isChat==0?false:true,
                    "can_send_voice_notes"=>$isChat==0?false:true,
                    "can_send_polls"=>$isChat==0?false:true,
                    "can_send_other_messages"=>$isChat==0?false:true,
                    "can_add_web_page_previews"=>$isChat==0?false:true,
                    "can_change_info"=>$isChat==0?false:true,
                    "can_invite_users"=>$isChat==0?false:true,
                    "can_pin_messages"=>$isChat==0?false:true,
                    "can_manage_topics"=>$isChat==0?false:true,
                ],
            ];
            $result = BotService::posturl($url, $data);
            return $result;
        }else{
            return false;
        }
    }
    
    // 发送机器人消息
    
    public static function send($path,$reData)
    {
        BotService::addLog("send","传入参数",["path"=>$path,"text"=>$reData],"start");

        if(self::$token){
            // $reData=BotService::getSendData($reData);
            $reData['parse_mode']="html";
            if(empty($reData['reply_markup'])){
                $reData['reply_markup']="";
            }
            $url = self::$tgApi . 'bot' . self::$token . $path;
            BotService::addLog("send","发送数据",$reData);
            $result = BotService::posturl($url,$reData);
            BotService::addLog("send","返回数据",$result,"end");
            return $result;
        }else{
            BotService::addLog("send","阻断",["TOKEN不存在",self::$token],"end");
            return false;
        }
        
    }
    

}