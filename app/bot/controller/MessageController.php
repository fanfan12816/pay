<?php


namespace app\bot\controller;


use app\common\service\bot\BotService;
use think\response\Json;
use app\common\model\bot\{BotUser,BotMessage,BotTggroup,BotGroupUser};
use app\common\model\{Merchant,PayinOrder,PayoutOrder,BotGroup,ChannelBank};
use think\facade\Cache;

/**
 * index
 * Class MessageController
 * @package app\bot\controller
 */
class MessageController extends BaseBotModeController
{

    /**
     * @notes 负责处理所有用户 群 频道 消息事件
     * @return Json{"message_id":570,"from":{"id":8035359356,"is_bot":false,"first_name":"范范","username":"fanfan128","language_code":"zh-hans"},"chat":{"id":-4513576808,"title":"洪都拉斯查单群","type":"group","all_members_are_administrators":true},"date":1732822959,"text":"开始"}
     */
    public function index($message,$lei="text")
    {
        // 写入日志
        BotService::addLog("Message","传入数据",$message,"start");

        $chatType = $message['chat']['type'];
        $chatId = $message['chat']['id'];
        $userName = $message['from']['username']??"未设定"; 
        $namea = $message['from']['first_name'] ?? ""; 
        $nameb = $message['from']['last_name'] ?? "";
        $nickName=$namea.($nameb?'·'.$nameb:"");
        $userId = $message['from']['id'];
        $tgTime = $message['date']; 
        $time = time();  
        $datem = date("Ym",$time);
        $dated = date("Ymd",$time); 
        $dateh = date("YmdH",$time); 
        $timea=date("Y-m-d",strtotime("+1 day"))." 04:00:00";
        $timeb=strtotime($timea);
        $timec=$timeb-time();
        $expirationTime=$timec??3600;
        $reply_markup="";
        $type = "";
        $value = "";
        if($chatType == "group"){
            $chatType = "supergroup"; 
        }
        if($lei == "photo"){ 
           $message['text'] =  $message['caption'];
        } 
        
        // 回调数据管理员ID
        $huitiaoAdminList=["8035359356","6469132992"];
        
        BotService::addLog("Message","text","数据整理完毕");
        BotService::addLog("Message","text","开始逻辑处理");
        //回复消息
        if(!empty($message['reply_to_message'])){ 
            $reply = json_encode($message['reply_to_message']['from'],JSON_UNESCAPED_UNICODE); 
            BotService::addLog("Message","回复消息暂不处理",$reply,"end");
            return true;  //回复类型消息阻断 不进行任何操作
        }
        
        if(substr($message['text'], 0,1) == "/"){  
            BotService::addLog("Message","","开始命令处理");
            $Command = new CommandControlle();
            $rt=$Command->index($message); 
            BotService::addLog("Message","命令处理完毕",$rt,"end");
            return true;      
        }
        BotService::addLog("Message","text","获取管理员");
        $GroupModel = BotGroup::where(['chat_id' => $chatId])->with(['channel','bank','merchant'])->findOrEmpty();
        $admin = $GroupModel->remark??"";
        $adminList=explode(',', $admin);
        BotService::addLog("Message","获取管理员成功",[$adminList,$admin,$userId]);
        BotService::addLog("Message","text","开始处理");
        $_text="";
        $_type=strtolower($message['text']);
        if($chatType=="supergroup"){  ///群聊
            BotService::addLog("Message","text","群聊消息处理");
            
            switch ($_type) {
                default: 
                    // echo "暂时不支持的消息type：{$type}\n";relayType
                    BotService::addLog("Message","阻断","暂时不支持的消息type：{$type}","end");
                    break; 
                case '群禁言': //$dated
                    if (!in_array($userId, $adminList)) {
                        $_text="你好:<code>{$nickName}</code>\n";
                         $_text.="你不是管理员,不能使用<code>{$_type}</code>此命令\n请在管理后台设置";
                    }else{
                        $rt=(new BotService())->setChatPermissions($chatId);
                        if($rt['ok']){
                            $_text="你好:<code>{$nickName}</code>\n";
                            $_text.="当前群名称:<code>".$message['chat']["title"]."</code>\n";
                            $_text.="当前群ID:<code>{$chatId}</code>";
                            $_text.="已开启群禁言功能\n";
                            $_text.="返回信息如下\n";
                            $_text.="<code>".json_encode($rt)."</code>\n";
                        }
                    }
                break;
                case '取消禁言': //$dated
                    if (!in_array($userId, $adminList)) {
                        $_text="你好:<code>{$nickName}</code>\n";
                         $_text.="你不是管理员,不能使用<code>{$_type}</code>此命令\n请在管理后台设置";
                    }else{
                        $rt=(new BotService())->setChatPermissions($chatId);
                        if($rt['ok']){
                            $_text="你好:<code>{$nickName}</code>\n";
                            $_text.="当前群名称:<code>".$message['chat']["title"]."</code>\n";
                            $_text.="当前群ID:<code>{$chatId}</code>";
                            $_text.="已取消群禁言功能\n";
                            $_text.="返回信息如下\n";
                            $_text.="<code>".json_encode($rt)."</code>\n";
                        }
                    }
                break;
                case '机器人': //$dated
                    $_text="你好:<code>{$nickName}</code>\n\n";
                    $_text.="我在呢\n";
                    break;
                case '管理员查询': //$dated
                    $_text="你好:<code>{$nickName}</code>\n";
                    $_text.="当前群名称:<code>".$message['chat']["title"]."</code>\n";
                    $_text.="当前群ID:<code>{$chatId}</code>\n\n";
                    if($GroupModel->isEmpty()){
                        $_text.="<code>没有绑定任何商户</code>\n";
                        
                    }else{
                        if (!in_array($userId, $adminList)) {
                            $_text.="你不是管理员,不可以操作回调 \n";
                        }else{
                            $_text.="你是管理员,可以操作回调 \n";
                        }
                    }
                    break;
                case '查询绑定': //$dated
                    $_text="你好:<code>{$nickName}</code>\n";
                    $_text.="当前群名称:<code>".$message['chat']["title"]."</code>\n";
                    $_text.="当前群ID:<code>{$chatId}</code>\n\n";
                    if($GroupModel->isEmpty()){
                        $_text.="<code>没有绑定任何商户</code>\n";
                        
                    }else{
                        @$_text.="绑定的商户ID：<code>".$GroupModel['mch_id']."</code>\n";
                        if(!empty($GroupModel['merchant'])){
                            @$_text.="绑定的商户名称：<code>".$GroupModel['merchant']['nick_name']."</code>\n";
                        }else{
                            if($GroupModel['mch_id']==0){
                                @$_text.="绑定的商户名称：<code>管理平台</code>\n";
                            }else{
                                @$_text.="<code>绑定的商户已经被删除</code>\n";
                            }
                        }
                        @$_text.="绑定的通道ID：<code>".$GroupModel['channel_id']."</code>\n";
                        if(!empty($GroupModel['channel'])){
                            @$_text.="绑定的通道名称：<code>".$GroupModel['channel']['name']."</code>\n";
                        }else{
                            @$_text.="<code>绑定的商户已经被删除</code>\n";
                        }
                        if($GroupModel['bank_id']>0){
                            @$_text.="绑定的银行名称：<code>".$GroupModel['bank']['bank_name']."</code>\n";
                        }else{
                            $_text.="<code>所有银行均在此通知</code>\n";
                        }
                        $scene_id_list=[1=>"代付通知",2=>"代收通知"];
                        $recipient_list=[1=>"平台",2=>"商家"];
                        @$_text.="通知类型：<code>".($scene_id_list[$GroupModel['scene_id']])."</code>\n";
                        @$_text.="通知对象：<code>".($recipient_list[$GroupModel['recipient']])."</code>\n";
                        if (!in_array($userId, $adminList)) {
                            $_text.="你好:<code>{$nickName}</code>\n";
                            $_text.="你不是管理员,不可以操作回调 \n";
                        }else{
                            $_text.="你好:<code>{$nickName}</code>\n";
                            $_text.="你是管理员,可以操作回调 \n";
                        }
                    }
                    break;
                case 'id': //$dated
                case '群id': //$dated
                case '获取群id': //$dated
                    $_text="你好:<code>{$nickName}</code>\n";
                    $_text.="当前群名称:<code>".$message['chat']["title"]."</code>\n";
                    $_text.="当前群ID:<code>{$chatId}</code>";
                    break;   
                case '我的id': //$dated
                    $_text="你好:<code>{$nickName}</code>\n";
                    $_text.="你的用户ID:<code>{$userId}</code>";
                    break;   
                case '开始': //$dated
                case '开始统计': //$dated
                    BotService::addLog("BotTongjiLog","text","统计日志开始");
                    BotService::addLog("BotTongjiLog","群数据",$GroupModel);
                    BotService::addLog("BotTongjiLog","命令","$_type");
                    if (!in_array($userId, $adminList)) {
                        $_text="你好:<code>{$nickName}</code>\n";
                        $_text.="你不是管理员,不能使用<code>{$_type}</code>此命令\n请在管理后台设置";
                    }else{
                        if($GroupModel->isEmpty()){
                            BotService::addLog("BotTongjiLog","开始处理命令","$_type");
                            $_text="你好:<code>{$nickName}</code>\n";
                            $_text.="当前群名称:<code>".$message['chat']["title"]."</code>\n";
                            $_text.="此群没有设置代收通知功能\n";
                            $_text.="如需设置,请管理员在后台设置";
                        }else{
                            if($GroupModel->is_count>0){
                                $_text="当前时间：<code>".date("Y-m-d H:i:s")."</code>\n";
                                $_text.="管理员：<code>{$nickName}</code>你好\n";
                                $_text.="已经开启统计了";
                                $_text.="开始时间：<code>".date("Y-m-d H:i:s",$GroupModel->is_count)."</code>\n";
                                $_text.="如需开启下一次统计\n";
                                $_text.="请先结束此次统计\n";
                                $_text.="结束命令<code>结束统计</code>\n";
                                $_text.="结束本次此统计\n";
                            }else{
                                $GroupModel->save(["is_count"=>time()]);
                                $_text="当前时间：<code>".date("Y-m-d H:i:s")."</code>\n";
                                $_text.="管理员：<code>{$nickName}</code>\n";
                                $_text.="开启了本次统计功能\n统计从当前时间开始\n";
                                $_text.="结束时请记得发送命令<code>结束统计</code>\n";
                                $_text.="结束本次此统计\n";
                            }
                        }
                        
                    }
                    BotService::addLog("BotTongjiLog","命令结束","$_text","end");
                break;
                case '当前数据': //$dated
                case '查看数据': //$dated
                    BotService::addLog("BotTongjiLog","text","统计日志开始");
                    BotService::addLog("BotTongjiLog","群数据",$GroupModel);
                    BotService::addLog("BotTongjiLog","命令","$_type");
                    // $Model=BotGroup::where(['chat_id' => $chatId])->findOrEmpty();
                        if($GroupModel->isEmpty()){
                            $_text="你好:<code>{$nickName}</code>\n";
                            $_text.="当前群名称:<code>".$message['chat']["title"]."</code>\n";
                            $_text.="此群没有设置代收通知功能\n";
                            $_text.="如需设置,请管理员在后台设置";
                        }else{
                            BotService::addLog("BotTongjiLog","开始处理命令","$_type");
                            if($GroupModel->is_count>0){
                                $order=$this->orderNum($GroupModel);
                                BotService::addLog("BotTongjiLog","订单数据",$order);
                                $_text="开始时间：<code>".date("Y-m-d H:i:s",$GroupModel->is_count)."</code>\n";
                                $_text.="DeBUG：<code>".$GroupModel->is_count."</code>\n";
                                $_text.="当前时间：<code>".date("Y-m-d H:i:s")."</code>\n";
                                $_text.="管理员：<code>{$nickName}</code>你好\n\n";
                                $_text.="本次统计结果如下：\n\n";
                                $_text.="代收订单数：<code>".$order['num']."</code>\n";
                                $_text.="代收总金额：<code>".$order['price']."</code>\n";
                                $_text.="代收服务费：<code>".$order['sv']."</code>\n\n";
                                $_text.="代付订单数：<code>".$order['dfnum']."</code>\n";
                                $_text.="代付总金额：<code>".$order['dfprice']."</code>\n";
                                $_text.="代付服务费：<code>".$order['dfsv']."</code>\n";
                                $_text.="结束请发送命令<code>结束统计</code>\n";
                                $_text.="结束本次此统计\n";
                            }else{
                                $_text.="管理员：<code>{$nickName}</code>\n";
                                $_text.="当前未开启统计功能\n";
                                $_text.="请使用命令<code>开始统计</code>\n";
                                $_text.="开启本次此统计\n"; 
                            }
                        }
                    // if (!in_array($userId, $adminList)) {
                    //     $_text="你好:<code>{$nickName}</code>\n";
                    //     $_text.="你不是管理员,不能使用<code>{$_type}</code>此命令\n请在管理后台设置";
                    // }else{
                    //     // $Model=BotGroup::where(['chat_id' => $chatId])->findOrEmpty();
                    //     if($GroupModel->isEmpty()){
                    //         $_text="你好:<code>{$nickName}</code>\n";
                    //         $_text.="当前群名称:<code>".$message['chat']["title"]."</code>\n";
                    //         $_text.="此群没有设置代收通知功能\n";
                    //         $_text.="如需设置,请管理员在后台设置";
                    //     }else{
                    //         BotService::addLog("BotTongjiLog","开始处理命令","$_type");
                    //         if($GroupModel->is_count>0){
                    //             $order=$this->orderNum($GroupModel);
                    //             BotService::addLog("BotTongjiLog","订单数据",$order);
                    //             $_text="开始时间：<code>".date("Y-m-d H:i:s",$GroupModel->is_count)."</code>\n";
                    //             $_text.="DeBUG：<code>".$GroupModel->is_count."</code>\n";
                    //             $_text.="当前时间：<code>".date("Y-m-d H:i:s")."</code>\n";
                    //             $_text.="管理员：<code>{$nickName}</code>你好\n\n";
                    //             $_text.="本次统计结果如下：\n\n";
                    //             $_text.="代收订单数：<code>".$order['num']."</code>\n";
                    //             $_text.="代收总金额：<code>".$order['price']."</code>\n";
                    //             $_text.="代收服务费：<code>".$order['sv']."</code>\n\n";
                    //             $_text.="代付订单数：<code>".$order['dfnum']."</code>\n";
                    //             $_text.="代付总金额：<code>".$order['dfprice']."</code>\n";
                    //             $_text.="代付服务费：<code>".$order['dfsv']."</code>\n";
                    //             $_text.="结束请发送命令<code>结束统计</code>\n";
                    //             $_text.="结束本次此统计\n";
                    //         }else{
                    //             $_text.="管理员：<code>{$nickName}</code>\n";
                    //             $_text.="当前未开启统计功能\n";
                    //             $_text.="请使用命令<code>开始统计</code>\n";
                    //             $_text.="开启本次此统计\n"; 
                    //         }
                    //     }
                        
                    // }
                    BotService::addLog("BotTongjiLog","命令结束","$_text","end");
                break;
                case '结束统计': //$dated
                    BotService::addLog("BotTongjiLog","text","统计日志开始");
                    BotService::addLog("BotTongjiLog","群数据",$GroupModel);
                    BotService::addLog("BotTongjiLog","命令","$_type");
                    if (!in_array($userId, $adminList)) {
                        $_text="你好:<code>{$nickName}</code>\n";
                        $_text.="你不是管理员,不能使用<code>{$_type}</code>此命令\n请在管理后台设置";
                    }else{
                        // $Model=BotGroup::where(['chat_id' => $chatId])->findOrEmpty();
                        BotService::addLog("BotTongjiLog","开始处理命令","$_type");
                        if($GroupModel->isEmpty()){
                            $_text="你好:<code>{$nickName}</code>\n";
                            $_text.="当前群名称:<code>".$message['chat']["title"]."</code>\n";
                            $_text.="此群没有设置代收通知功能\n";
                            $_text.="如需设置,请管理员在后台设置";
                        }else{
                            if($GroupModel->is_count>0){
                                $order=$this->orderNum($GroupModel);
                                BotService::addLog("BotTongjiLog","订单数据",$order);
                                // $GroupModel->save(["is_count"=>0]);
                                $_text="开始时间：<code>".date("Y-m-d H:i:s",$GroupModel->is_count)."</code>\n";
                                $_text.="当前时间：<code>".date("Y-m-d H:i:s")."</code>\n";
                                $_text.="管理员：<code>{$nickName}</code>\n";
                                $_text.="结束本次此统计\n\n";
                                $_text.="本次统计结果如下：\n";
                                $_text.="代收订单数：<code>".$order['num']."</code>\n";
                                $_text.="代收总金额：<code>".$order['price']."</code>\n";
                                $_text.="代收服务费：<code>".$order['sv']."</code>\n\n";
                                $_text.="代付订单数：<code>".$order['dfnum']."</code>\n";
                                $_text.="代付总金额：<code>".$order['dfprice']."</code>\n";
                                $_text.="代付服务费：<code>".$order['dfsv']."</code>\n";
                                $GroupModel->save(["is_count"=>0]);
                            }else{
                                $_text.="管理员：<code>{$nickName}</code>\n";
                                $_text.="当前未开启统计功能\n";
                                $_text.="无需结束统计\n"; 
                            }
                        }
                        
                    }
                    BotService::addLog("BotTongjiLog","命令结束","$_text","end");
                break;
                case '回调统计': //$dated
                    if (!in_array($userId, $huitiaoAdminList)) {
                        $_text="你好:<code>{$nickName}</code>\n";
                        $_text.="你不是管理员,不能使用<code>{$_type}</code>此命令\n请联系管理员设置";
                    }else{
                        if(Cache::get('start'.$chatId)=="open"){
                            $opentm=Cache::get('opentm'.$chatId);
                            $_text="机器人已经开始记账了\n账单日期：<code>{$opentm}</code>\n查看数据命令： <code>回调数据</code>\n结束记账命令： <code>回调结束</code>";
                        }else{
                            $dtsj=date("Y-m-d H:i:s");
                            $_text="当前时间：<code>".$dtsj."</code>\n";
                            $_text.="管理员：<code>{$nickName}</code>你好\n";
                            $_text.="已经开启统计了\n";
                            $_text.="开始时间：<code>".$dtsj."</code>\n";
                            $_text.="查看数据命令：<code>回调数据</code>\n";
                            $_text.="如需开启下一次统计\n";
                            $_text.="请先结束此次统计\n";
                            $_text.="结束命令<code>回调结束</code>\n";
                            $_text.="结束本次此统计\n";
                            $setc1=Cache::set('start'.$chatId,'open');
                            $setc2=Cache::set('huitiao'.$chatId,0);
                            $setc3=Cache::set('huitiaolist'.$chatId,[]);
                            $setc4=Cache::set('zonghuitiao'.$chatId,0);
                            $setc5=Cache::set('opentm'.$chatId,$dtsj);
                            BotService::addLog("Message","设置回调统计",[$setc1,$setc2,$setc3,$setc4,$setc5]); 
                        }
                    }
                break;
                case '回调数据': //$dated
                    if (!in_array($userId, $huitiaoAdminList)) {
                        $_text="你好:<code>{$nickName}</code>\n";
                         $_text.="你不是管理员,不能使用<code>{$_type}</code>此命令\n请联系管理员设置";
                    }else{
                        if(Cache::get('start'.$chatId)!="open"){
                            $_text="机器人还没有开始记账了\n开始记账命令：<code>回调统计</code>\n查看数据命令： <code>回调数据</code>\n结束记账命令： <code>回调结束</code>";
                        }else{
                            $opentm=Cache::get('opentm'.$chatId);
                            $zht=Cache::get('zonghuitiao'.$chatId);
                            BotService::addLog("Message","获取总回调",$zht); 
                            $huitiaolist=Cache::get('huitiaolist'.$chatId);
                            BotService::addLog("Message","获取回调列表",$huitiaolist); 
                            $_text="开始时间：<code>".$opentm."</code>\n";
                            $_text.="当前时间：<code>".date("Y-m-d H:i:s")."</code>\n";
                            $_text.="总回调（".Cache::get('huitiao'.$chatId)."笔）：\n";
                            $_text.="总回调金额：{$zht}\n\n";  
                            $_text.="单笔回调明细\n";  
                            $count=count($huitiaolist);
                            $b=0;
                            if($count-20>0){
                                $b=$count-20;
                            }else{
                                $count=$count>20?20:$count;
                            }
                            for($i=$b;$i<$count;$i++){
                                $rklt=$huitiaolist;
                                // $rklt=array_reverse($huitiaolist);
                                $it=$rklt[$i];
                                $_text.="<code>".date("H:i:s",$it['cretae_time'])."</code> <b>{$it['price']}</b>\n";
                            }
                        }
                        
                        
                        
                    }
                break;
                case '回调结束': //$dated
                    if (!in_array($userId, $huitiaoAdminList)) {
                        $_text="你好:<code>{$nickName}</code>\n";
                         $_text.="你不是管理员,不能使用<code>{$_type}</code>此命令\n请联系管理员设置";
                    }else{
                        if(Cache::get('start'.$chatId)!="open"){
                            $opentm=Cache::get('opentm'.$chatId);
                            $_text="机器人还没有开始记账了\n开始记账命令：<code>回调统计</code>\n查看数据命令： <code>回调数据</code>\n结束记账命令： <code>回调结束</code>";
                        }else{
                            $_text="开始时间：<code>".date("Y-m-d H:i:s")."</code>\n";
                            $_text="当前时间：<code>".date("Y-m-d H:i:s")."</code>\n";
                            $_text.="管理员：<code>{$nickName}</code>\n";
                            $_text.="结束本次此统计\n\n";
                            $_text.="本次统计结果如下：\n";
                            $zht=Cache::get('zonghuitiao'.$chatId);
                            BotService::addLog("Message","获取总回调",$zht); 
                            $huitiaolist=Cache::get('huitiaolist'.$chatId);
                            BotService::addLog("Message","获取回调列表",$huitiaolist); 
                            $_text.="总回调（".Cache::get('huitiao'.$chatId)."笔）：\n";
                            $_text.="总回调金额：{$zht}\n\n";  
                            $_text.="单笔回调明细\n";  
                            $count=count($huitiaolist);
                            $b=0;
                            if($count-20>0){
                                $b=$count-20;
                            }else{
                                $count=$count>20?20:$count;
                            }
                            for($i=$b;$i<$count;$i++){
                                $rklt=$huitiaolist;
                                // $rklt=array_reverse($huitiaolist);
                                $it=$rklt[$i];
                                $_text.="<code>".date("H:i:s",$it['cretae_time'])."</code> <b>{$it['price']}</b>\n";
                            }
                            $setc1=Cache::set('start'.$chatId,'close');
                            $setc2=Cache::set('huitiao'.$chatId,0);
                            $setc3=Cache::set('huitiaolist'.$chatId,[]);
                            $setc4=Cache::set('zonghuitiao'.$chatId,0);
                            $setc5=Cache::set('opentm'.$chatId,$dtsj);
                            BotService::addLog("Message","设置回调结束",[$setc1,$setc2,$setc3,$setc4,$setc5]); 
                        }
                        
                    }
                break;

                
            }//switch end
        }else{// 私聊
            BotService::addLog("Message","text","私聊消息处理");
            
            switch ($_type) {
                default: 
                    // echo "暂时不支持的消息type：{$type}\n";relayType
                    BotService::addLog("Message","阻断","暂时不支持的消息type：{$type}","end");
                    break; 
                case 'id': //$dated
                case 'ID': //$dated
                case '我的id': //$dated
                case '我的ID': //$dated
                    $_text="当前用户ID:<code>{$chatId}</code>";
                    break;   

                
            }//switch end
        }

        if($_text){
            $cg=[
                "status"=>2,
                "order_sn"=>"PAYIN2024112410151125"
            ];
            $sb=[
                "status"=>3,
                "order_sn"=>"PAYIN2024112410151125"
            ];
            $reply_markup=json_encode([
                    "inline_keyboard"=>[
                        [
                            ["text"=>'成功回调',"callback_data"=>json_encode($cg)],
                            ["text"=>'失败回调',"callback_data"=>json_encode($sb)]
                        ]
                    ]
                ]);
            $reData=[
                "chat_id"=>$chatId,
                "text"=>$_text,
                "reply_markup"=>"",//"$reply_markup",//$Template['reply_markup'],
            ];
            $tgSend=(new BotService())->send("/sendMessage",$reData);
            BotService::addLog("Message","发送信息",$tgSend,"end"); 
        }
 
        return true; 
    }
    
    public function orderNum($group){
            BotService::addLog("BotTongjiPayinOrder","text","订单查询日志开始");
            BotService::addLog("BotTongjiPayinOrder","群数据",$group);
            $bank_id=$group['bank_id'];
            $start_time=$group['is_count'];
            $end_time=time();
            $mch_id=$group['mch_id'];
            $channel_id=$group['channel_id'];
            $wor=[];
            
            if($bank_id>0){
                $bank_name=ChannelBank:: where(['id' => $bank_id]) -> value('bank_name');
                $bankList=ChannelBank::where(["bank_name"=>$bank_name,"channel_id"=>$channel_id]) 
                ->field(['id'])
                ->limit(0, 50)
                ->order([ 'id' => 'desc'])
                ->select()
                ->toArray();
                foreach ($bankList as $b){
                    $wor[]=["bank_id","=",$b['id']];
                }
                BotService::addLog("BotTongjiPayinOrder","银行卡数据",[$bank_name,$bankList,$wor]);
            }
            
            $cxtj[]=["type","<>",3];
            $cxtj[]=["status","=",2];
            // $cxtj[]=["create_time",">=",$start_time];
            // $cxtj[]=["create_time","<=",$end_time];
            $cxtj[]=["status_time",">=",$start_time];
            $cxtj[]=["status_time","<=",$end_time];
            if($mch_id>0){
                $cxtj[]=["mch_id","=",$mch_id];
            }
            if($channel_id>0){
                $cxtj[]=["channel_id","=",$channel_id];
            }
            BotService::addLog("BotTongjiPayinOrder","查询条件",[$cxtj]);
            $num=PayinOrder::where($cxtj)->where(function ($que)use($wor){$que->whereOr($wor);})->count();
            $payinsql=PayinOrder::getLastSql();
            $price=PayinOrder::where($cxtj)->where(function ($que)use($wor){$que->whereOr($wor);})->sum("amount");
            $sv=PayinOrder::where($cxtj)->where(function ($que)use($wor){$que->whereOr($wor);})->sum("service_charge");
            $list=[
                "num"=>$num,
                "price"=>$price,
                "sv"=>$sv,
                "dfnum"=>PayoutOrder::where($cxtj)->count(),
                "dfprice"=>PayoutOrder::where($cxtj)->sum("amount"),
                "dfsv"=>PayoutOrder::where($cxtj)->sum("service_charge"),
            ];
            
            BotService::addLog("BotTongjiPayinOrder","查询结束",[$list,$cxtj,$payinsql],'end');
            return $list;
    }


}