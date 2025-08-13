<?php


declare(strict_types=1);

namespace app\common\service;

use app\common\model\{PayoutOrder,PayinOrder,BotGroup,Merchant,MerchantAccountLog};
use app\common\service\bot\BotService;
use app\common\service\{ConfigService};
class BotSendService
{

    // 代付发送机器信息
    public static function payoutSend($order_sn,$Model=[]){
            $prefix="bot_payoutSend";
            BotService::addLog($prefix,"","代付开始发送消息");
            if(!empty($order_sn)){
                BotService::addLog($prefix,"订单号",$order_sn);
                $Model= PayoutOrder::where(['order_sn' => $order_sn])
                ->findOrEmpty();
                if($Model->isEmpty()){
                    BotService::addLog($prefix,"订单不存在",$Model,'end');
                    return false;
                }
            }
            BotService::addLog($prefix,"订单信息",$Model);
            // 设置订单转换信息
            // $typeTxt=["","商户订单","手动下单","测试订单" ];
            $typeTxt=["","Merchant Order","Manual Order","Test Order" ];
            $Model['typeTxt']=$typeTxt[$Model['type']];
            $Model['imageTime']=date("Y-m-d H:i:s");
            $Model['create_time']=date("Y-m-d H:i:s",$Model['create_time']);
                // 设置机器人下方按钮
                $reply_markup=json_encode([
                    "inline_keyboard"=>[
                        [
                            ["text"=>'成功回调',"callback_data"=>json_encode(["sts"=>2,"sn"=>$Model->order_sn,"osts"=>$Model->status,"t"=>2])],
                            ["text"=>'失败回调',"callback_data"=>json_encode(["sts"=>3,"sn"=>$Model->order_sn,"osts"=>$Model->status,"t"=>2])],
                            ["text"=>'复制文本',"copy_text"=>["text"=>$Model->bank_name."\n".$Model->user_name."\n".$Model->bank_num."\n".$Model->iban."\n".$Model->amount]]
                        ]
                    ]
                ]);
                
                $botConfig=BotGroup::where(["mch_id"=>$Model->mch_id,"channel_id"=>$Model->channel_id,"recipient"=>2,"scene_id"=>1]) 
                ->limit(0, 10)
                ->order([ 'id' => 'desc'])
                ->select()
                ->toArray();
                $sql1=BotGroup::getLastSql();
                BotService::addLog($prefix,"获取商户机器人配置",[$botConfig,$sql1]);
                $_text="";
                foreach ($botConfig as &$it){
                    foreach($it['extra'] as $k => $v){
                        if($v['show']==1){
                            $t=$v['en']?$v['en']:$v['zh'];
                            $it['extra'][$k]['txt']=$t;
                            if($k!='image'){
                                $_text.="{$t}：<code>".$Model[$k]."</code>\n";
                            }
                        }
                    }
                    // $_text.="银行名称：<code>".$Model->bank_name."</code>\n";
                    // $_text.="银行卡号：<code>".$Model->bank_num."</code>\n";
                    // $_text.="持卡人姓名：<code>".$Model->user_name."</code>\n";
                    // $_text.="IBAN：<code>".$Model->iban."</code>\n";
                    $reData=[
                        "chat_id"=>$it['chat_id'],
                        "text"=>$_text,
                        "reply_markup"=>$reply_markup,
                    ];
                    $tgSend=(new BotService())->send("/sendMessage",$reData);
                    BotService::addLog($prefix,"发送文字信息返回",[$tgSend,$reData]);
                    
                }
                BotService::addLog($prefix,"","商户发送完成","end"); 
                $ptBotConfig=BotGroup::where(["mch_id"=>0,"channel_id"=>$Model->channel_id,"recipient"=>1,"scene_id"=>1])
                ->limit(0, 10)
                ->order([ 'id' => 'desc'])
                ->select()
                ->toArray();
                $sql2=BotGroup::getLastSql();
                BotService::addLog($prefix,"获取平台机器人配置",[$ptBotConfig,$sql2]);
                $_text="";
                foreach ($ptBotConfig as &$it){
                    foreach($it['extra'] as $k => $v){
                        if($v['show']==1){
                            $t=$v['en']?$v['en']:$v['zh'];
                            $it['extra'][$k]['txt']=$t;
                            if($k!='image'){
                                $_text.="{$t}：<code>".$Model[$k]."</code>\n";
                            }
                        }
                    }
                    // $_text.="银行名称：<code>".$Model->bank_name."</code>\n";
                    // $_text.="银行卡号：<code>".$Model->bank_num."</code>\n";
                    // $_text.="持卡人姓名：<code>".$Model->user_name."</code>\n";
                    // $_text.="IBAN：<code>".$Model->iban."</code>\n";
                    $reData=[
                        "chat_id"=>$it['chat_id'],
                        "text"=>$_text,
                        "reply_markup"=>$reply_markup,
                    ];
                    $tgSend=(new BotService())->send("/sendMessage",$reData);
                    BotService::addLog($prefix,"发送文字信息返回",[$tgSend,$reData]);
                }
                BotService::addLog($prefix,"","平台发送完成","end"); 
                return true;
    }
    // 代收发送机器人信息
    public static function payinSend($order_sn,$Model=[]){
            $prefix="bot_payinSend";
            $imgUrl=ConfigService::get('img_post_url',"");
            BotService::addLog($prefix,"图片识别",[$imgUrl]); 
            if(!empty($imgUrl)){
                // $img_post_url=$imgUrl.'/api/add/imgPost';
                // $a=imgPosturl($img_post_url,["order_sn"=>$Model->order_sn]);
                // BotService::addLog($prefix,"调用图片识别",[$a]); 
            }
            
            BotService::addLog($prefix,"","代收开始发送消息","start");
            if(!empty($order_sn)){
                BotService::addLog($prefix,"订单号",$order_sn);
                $Model= PayinOrder::where(['order_sn' => $order_sn])
                ->findOrEmpty();
                
                if($Model->isEmpty()){
                    BotService::addLog($prefix,"订单不存在",$Model,'end');
                    return false;
                }
            }
            BotService::addLog($prefix,"订单信息",$Model);
            // 去重 image
            $iscf=false;
            $cfmodel=PayinOrder::where(['imgmd5' => $Model->imgmd5])->where('order_sn','<>',$Model->order_sn)->where('image','<>',"")->findOrEmpty();
            if(!$cfmodel->isEmpty()){
                $iscf=true;
            }
            BotService::addLog($prefix,"去重完成",[$iscf]);
            // 设置订单转换信息
            // $typeTxt=["","商户订单","手动下单","测试订单" ];
            $typeTxt=["","Merchant Order","Manual Order","Test Order" ];
            $Model['typeTxt']=$typeTxt[$Model['type']];
            $Model['imageTime']=date("Y-m-d H:i:s");
            $Model['create_time']=date("Y-m-d H:i:s",$Model['create_time']);
                // 设置机器人下方按钮
                $reply_markup=json_encode([
                    "inline_keyboard"=>[
                        [
                             ["text"=>'成功回调',"callback_data"=>json_encode(["sts"=>2,"sn"=>$Model->order_sn,"osts"=>$Model->status,"t"=>1])],
                            ["text"=>'失败回调',"callback_data"=>json_encode(["sts"=>3,"sn"=>$Model->order_sn,"osts"=>$Model->status,"t"=>1])]
                        ]
                    ]
                ]);
                $wor[]=["bank_id","=",0];
                
                $bank_name=ChannelBank::withTrashed()-> where(['id' => $Model->bank_id]) -> value('bank_name');
                $prefix=$prefix.'_'.$bank_name;
                
                $bankList=ChannelBank::where(["bank_name"=>$bank_name,"channel_id"=>$Model->channel_id]) 
                ->field(['id'])
                ->limit(0, 50)
                ->order([ 'id' => 'desc'])
                ->select()
                ->toArray();
                foreach ($bankList as $b){
                    $wor[]=["bank_id","=",$b['id']];
                }
                
                $botConfig=BotGroup::where(["mch_id"=>$Model->mch_id,"channel_id"=>$Model->channel_id,"recipient"=>2,"scene_id"=>2]) 
                ->where(function ($que)use($wor){$que->whereOr($wor);})
                ->limit(0, 10)
                ->order([ 'id' => 'desc'])
                ->select()
                ->toArray();
                $sql1=BotGroup::getLastSql();
                BotService::addLog($prefix,"获取商户机器人配置",[$botConfig,$sql1]);
                $_text="";
                $cashier_url=ConfigService::get('cashier_url','');
                
                foreach ($botConfig as &$it){
                    foreach($it['extra'] as $k => $v){
                        if($v['show']==1){
                            $t=$v['en']?$v['en']:$v['zh'];
                            $it['extra'][$k]['txt']=$t;
                            if($k!='image'){
                                $_text.="{$t}：<code>".$Model[$k]."</code>\n";
                            }
                        }
                    }
                    // 重复数据
                    $_cftext="repeat：<code>NO</code>\n\n";
                    if($iscf){
                        $statusList=["待付款","确认中","审核成功","审核失败","超时关闭","手动关闭"];
                        $_cftext="repeat：<code>YES</code>\n";
                        $_cftext.="repeatOrder：<code>".$cfmodel->order_sn."</code>\n";
                        @$_cftext.="repeatOrderStatus：<code>".$statusList[$cfmodel->status]."</code>\n";
                        @$_cftext.="<a href='".$cashier_url."/cashier/index?id=".$cfmodel->order_sn."'>点击查看订单详情</a>\n\n";
                    }
                    $_text=$_cftext.$_text;
                    
                    $reData=[
                        "chat_id"=>$it['chat_id'],
                        "caption"=>$_text,
                        "photo"=>$Model->image,
                        "show_caption_above_media"=>true,
                        "reply_markup"=>$reply_markup,
                    ];
                    $tgSend=(new BotService())->send("/sendPhoto",$reData);
                    BotService::addLog($prefix,"发送图片信息返回",$tgSend);
                    if(!$tgSend['ok']){
                        $_text.="<a href='".$Model->image."'>".$it['extra']['image']['txt']."</a>\n";
                        $reData=[
                            "chat_id"=>$it['chat_id'],
                            "text"=>$_text,
                            "reply_markup"=>$reply_markup,
                        ];
                        $tgSend=(new BotService())->send("/sendMessage",$reData);
                        BotService::addLog($prefix,"发送文字信息返回",$tgSend);
                    }
                    
                }
                BotService::addLog($prefix,"","商户发送完成","end"); 
                $ptBotConfig=BotGroup::where(["mch_id"=>0,"recipient"=>1,"scene_id"=>2])
                ->where(function ($que)use($wor){$que->whereOr($wor);})
                ->limit(0, 10)
                ->order([ 'id' => 'desc'])
                ->select()
                ->toArray();
                $sql2=BotGroup::getLastSql();
                BotService::addLog($prefix,"获取平台机器人配置",[$botConfig,$sql2]);
                $_text="";
                foreach ($ptBotConfig as &$it){
                    foreach($it['extra'] as $k => $v){
                        if($v['show']==1){
                            $t=$v['en']?$v['en']:$v['zh'];
                            $it['extra'][$k]['txt']=$t;
                            if($k!='image'){
                                $_text.="{$t}：<code>".$Model[$k]."</code>\n";
                            }
                        }
                    }
                    
                    $reData=[
                        "chat_id"=>$it['chat_id'],
                        "caption"=>$_text,
                        "photo"=>$Model->image,
                        "show_caption_above_media"=>true,
                        "reply_markup"=>$reply_markup,
                    ];
                    $tgSend=(new BotService())->send("/sendPhoto",$reData);
                    BotService::addLog($prefix,"发送图片信息返回",$tgSend);
                    if(!$tgSend['ok']){
                        $_text.="<a href='".$Model->image."'>".$it['extra']['image']['txt']."</a>\n";
                        $reData=[
                            "chat_id"=>$it['chat_id'],
                            "text"=>$_text,
                            "reply_markup"=>$reply_markup,
                        ];
                        $tgSend=(new BotService())->send("/sendMessage",$reData);
                        BotService::addLog($prefix,"发送文字信息返回",$tgSend);
                    }
                }
                $isas=ConfigService::get('asauto_isopen',"0");
                BotService::addLog($prefix,"埃塞回调是否开启",[$isas]); 
                if($isas=='1'){
                    $asUrl=ConfigService::get('asauto_url',"");
                    $asList1=ConfigService::get('asauto_list',"");
                    $asList=explode(',', $asList1);
                    $channel_id=$Model->channel_id;
                    BotService::addLog($prefix,"埃塞回调条件",[$asUrl,$asList1,$asList,$channel_id]); 
                    if(!empty($asUrl)){
                        if (in_array($channel_id, $asList)) {
                            $img_post_url=$asUrl.'/api/add/imgPost';
                            BotService::addLog($prefix,"埃塞开始请求自动回调",[$img_post_url]); 
                            $a=imgPosturl($img_post_url,["order_sn"=>$Model->order_sn]);
                            BotService::addLog($prefix,"调用埃塞回调",[$a]); 
                        }else{
                            BotService::addLog($prefix,"埃塞回调通道未设置",[$asList1,$asList,$channel_id]); 
                        }
                    }else{
                        BotService::addLog($prefix,"埃塞回调地址不存在",[$asUrl]); 
                    }
                }
                BotService::addLog($prefix,"","平台发送完成","end"); 
                return true;
                // $a=imgPosturl($img_post_url,$Model);
    }
}