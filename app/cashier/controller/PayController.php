<?php

namespace app\cashier\controller;

use hg\apidoc\annotation as Apidoc;

use app\common\model\{Merchant,PayinOrder,Language,ChannelBank,BotGroup};
use app\common\service\{ConfigService,ImgcompressService};
use think\facade\Filesystem;
use think\facade\Request;
use app\common\service\{PayinCallbackService,BotSendService};
use app\common\service\bot\BotService;
/**
 * @Apidoc\Title("收银台")
 * Author: JackMater
 */

class PayController  {

  public function index() {
    

  }
  
   /**
   * @Apidoc\Title("查询订单")
   * @Apidoc\Desc("已完成")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("cashier/v1/pay/info")
   * @Apidoc\Query("id", type="string", require=true, desc="接收的id")
   * @Apidoc\Query("token", type="string", require=true, desc="id 的MD5如 MD5(QueryId:2323232323)")
   * @Apidoc\Returned("order_sn", type="string", desc="平台订单号")
   * @Apidoc\Returned("mch_sn", type="string", desc="商户订单编号")
   * @Apidoc\Returned("channel_id", type="string", desc="通道id")
   * @Apidoc\Returned("status", type="string", desc="审核状态:0-待付款;1-确认中;2-审核成功,3-审核失败,4-订单超时已关闭,5-订单手动关闭")
   * @Apidoc\Returned("request_time", type="string", desc="创建时间 YYYY-MM-DD HH:SS:II ")
   * @Apidoc\Returned("expire_time", type="string", desc="交易过期时间  毫秒")
   * @Apidoc\Returned("amount", type="string", desc="订单金额")
   * @Apidoc\Returned("bankList", type="string", desc="银行卡列表")
   * @Apidoc\Returned("bank_type", type="string", desc="类型:0银行卡,1钱包")
   * @Apidoc\Returned("bank_name", type="string", desc="银行卡名称/钱包名称")
   * @Apidoc\Returned("bank_user_name", type="string", desc="持卡人名称")
   * @Apidoc\Returned("bank_num", type="string", desc="银行卡号码/钱包卡号")
   * @Apidoc\Returned("iban", type="string", desc="iban")
   * @Apidoc\Returned("bank_image", type="string", desc="图片地址")
   * @Apidoc\Returned("config", type="string", desc="收银台配置")
   */
    public function info() {
        $id=input("id")??"";
        $token=input("token")??"";
        if(empty($id)){
             return messageReturn(40004,'Missing Request Id!');
        }
        if(empty($token)){
             return messageReturn(40005,'Missing Request Token!');
        }else{
            if($token!==md5("QueryId:".$id)){
                return messageReturn(50000,'Token incorrect!');
            }
        }
        $Model=PayinOrder::where(['order_sn' => $id])->findOrEmpty();
        
        if($Model->isEmpty()){
            return messageReturn(40006,'Order does not exist!');
        }else{
            if($Model->status>0){
               return messageReturn(50001,'Order status has changed !');
            }
            if($Model->expire_time<time()){
                return messageReturn(40008,'Order has expired!');
            }
            $lanField="title,logo,desc,next,previous,accomplish,bank,bankInfo,credit,await,bankName,bankNum,bankIban,bankUser,price,sn,create,end,scpzts,payName,fzwzts,warn,bg_img,bg_color,text_color";
            $lan=Language::where(['channel_id' => $Model->channel_id,'mch_id'=>$Model->mch_id])->field($lanField)->findOrEmpty();
            if($lan->isEmpty()){
                return messageReturn(40007,'No configured language!');
            }
            $request_time=diyTimestamp($Model->request_time,$Model->timezone);
            $request_time=date('d/m/Y H:i:s',strtotime($request_time));
            $expire_time=($Model->expire_time-time())*1000;
            $bankList=[];
            $bankField = 'id as bank_id,type as bank_type,bank_name,user_name as bank_user_name,bank_num,iban,image as bank_image';
            
            $bankWhere=[
                ['pay_type', '=', 0],
                ['status', '=', 1],
                ['channel_id', '=', $Model->channel_id],
                ['min', '<=',$Model->amount],
                ['max', '>=',$Model->amount]
            ];
            $bankList = ChannelBank::field($bankField)
                ->where($bankWhere)
                ->limit(0, 20)
                ->order(['sort' => 'desc', 'id' => 'desc'])
                ->select()
                ->toArray();
            
            $data=[
                "config"=>$lan,
                "order_sn"=>$Model->order_sn,
                "mch_sn"=>$Model->mch_sn,
                "channel_id"=>$Model->channel_id,
                "status"=>$Model->status,
                "back_url"=>$Model->back_url,
                "request_time"=>$request_time,
                "expire_time"=>$expire_time,
                "amount"=>number_format($Model->amount,2),
                "bankList"=>$bankList,
            ];
            
            return ajaxReturn(200,'success',$data);
        }

    }
    /**
   * @Apidoc\Title("上传截图")
   * @Apidoc\Desc("已完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("cashier/v1/pay/upload")
   * @Apidoc\Param("file", type="file", require=true, desc="文件")
   * @Apidoc\Param("id", type="string", require=true, desc="接收的id")
   * @Apidoc\Param("token", type="string", require=true, desc="id 的MD5如 MD5(QueryId:2323232323)")
   * @Apidoc\Returned("url", type="string", desc="图片地址")
   */
     public function upload(){
        $prefix="payImg";
        BotService::addLog($prefix,"","上传图片开始","start");
        BotService::addLog($prefix,"获取原始参数",[input(),$_FILES['file']]);
        $id=input("id")??"";
        $token=input("token")??"";
        if(empty($id)){
             return messageReturn(40004,'Missing Request Id!');
        }
        if(empty($token)){
             return messageReturn(40005,'Missing Request Token!');
        }else{
            if($token!==md5("QueryId:".$id)){
                return messageReturn(50000,'Token incorrect!');
            }
        }
        
        $file            =  request() -> file('file'); # 上传文件信息
        // 获取用户的ip
        $ip = getClientIP();
        BotService::addLog($prefix,"获取请求IP",[$ip]);
        if(empty($file)){
            return messageReturn(40001,'No file !',$file);
        }
        // $size            =  round((($_FILES['file']['size'] / 1024) / 1024), 2); # 计算文件大小(Mb)
        $cashier_url=ConfigService::get('cashier_url',"");
        
        if(empty($cashier_url)){
            return messageReturn(40001,'No register address is configured !');
        }
        // @$picname='/'.time().rand(1,1000).substr($file['tmp_name'],strrpos($file['tmp_name'],"."));
        # 获取文件地址
        // $fileRoute = 'payimg';
        $fileRoute = '/payimg/'.date('Ymd', time());
        // if (!file_exists(app()->getRootPath(). 'public/UploadFile/' .$fileRoute)) {
        //     @mkdir(app()->getRootPath(). 'public/UploadFile/' .$fileRoute, 0777, true);
        // }
        try {
            // 使用验证器验证上传的文件
            validate(
                [
                    'file' => [
                        // 限制文件大小(单位b)，这里限制为4M
                        // 'fileSize' => 4 * 1024 * 1024,
                        // 限制文件后缀，多个后缀以英文逗号分割
                        // 'fileExt'  => 'gif,jpg,png,jpeg'
                        'fileExt'  => 'jpg,png,jpeg,gif,bmp,webp,jfif'
                    ]
                ],
                [
                    'file.fileSize' => '文件太大',
                    'file.fileExt' => '不支持的文件后缀',
                ]
            )->check(['file' => $file]);
        
        
            
            $result = Filesystem::disk('public') -> putFile($fileRoute, $file, 'uniqid');
            $delurl=app()->getRootPath(). 'public/UploadFile/' . $result;
            $FilePath =getImageDomain(). '/UploadFile/' . $result;
            
            // @$img_data = file_get_contents($delurl);
            
            // @$base64= chunk_split(base64_encode($img_data));
            
            // BotService::addLog($prefix,"base64",[$base64]);
            @$imgmd5=md5_file($delurl);
            @$hxz=PayinOrder::update(['imgmd5' => $imgmd5], ['order_sn' => $id]);
            BotService::addLog($prefix,"imgmd5",[$imgmd5,$hxz]);
            
            $maxsize=1 * 1024 * 1024;
            $imgszie=$_FILES['file']['size']??0;
            if($imgszie>$maxsize){
                $path = $delurl; // 原始文件路径
                $percent = 800; // 压缩后小边尺寸（或是缩放比例）
                $dstImg = $delurl; // 保存文件名称（原路径传回覆盖原图）
                @$ystp=(new ImgcompressService())->compressImg($path, $percent, $dstImg);
                // @addLog($prefix, 0,[$maxsize,$file['size'],$ystp]);
                BotService::addLog($prefix,"处理压缩返回",[$maxsize,$imgszie,$ystp]);
            }
            $post_string= "url=$FilePath&version=beta";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,  $cashier_url."uploadPictures/upload.php");
            curl_setopt($ch, CURLOPT_POSTFIELDS,  $post_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $rtinfo = curl_exec($ch);
            curl_close($ch);
            $rtinfo = json_decode($rtinfo,true);
            @$code=$rtinfo['code']??0;
            // @addLog($prefix, 0,[$rtinfo,$code]);
            BotService::addLog($prefix,"图片上传收银台返回",[$rtinfo,$code]);
            if($code>0){
                if(@$rtinfo['data']['url']){
                    $rtinfo['data']['url']=$cashier_url.'uploadPictures/'.$rtinfo['data']['url'];
                }
                
                @$del=unlink($delurl);
                // $rtinfo['data']['$del']=[$delurl];
                BotService::addLog($prefix,"图片上传完成",[$rtinfo,$delurl],"end");
                return ajaxReturn($rtinfo['code'],$rtinfo['msg'],$rtinfo['data']);
            }else{
                return messageReturn(5000,"Upload server error",$rtinfo);
            } 
        } catch (\think\exception\ValidateException $e) {
            BotService::addLog($prefix,"图片上传失败",[$e,$e->getMessage()],"end");
            return $e->getMessage();
        }
        
 
        
    }
    
   /**
   * @Apidoc\Title("提交订单")
   * @Apidoc\Desc("已完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("cashier/v1/pay/submit")
   * @Apidoc\Param("id", type="string", require=true, desc="接收的id")
   * @Apidoc\Param("token", type="string", require=true, desc="id 的MD5如 MD5(QueryId:2323232323)")
   * @Apidoc\Param("bank_id", type="string", require=true, desc="银行卡/钱包id)")
   * @Apidoc\Param("payer_name", type="string", require=true, desc="付款人名字)")
   * @Apidoc\Param("image", type="string", require=true, desc="交易凭证)")
   */

    public function submit(){
        $prefix="CashierSubmit";
        BotService::addLog($prefix,"","收银台提交信息","start");
        // @$pdd=input();
        BotService::addLog($prefix,"获取原始参数",input());
        $id=input("id")??"";
        $token=input("token")??"";
        $bank_id=input("bank_id")??"";
        $image=input("image")??"";
        $payer_name=input("payer_name")??"";
        if(empty($id)){
             return messageReturn(40004,'Missing Request Id!');
        }
        if(empty($bank_id)){
             return messageReturn(40004,'Missing Request BANKID!');
        }
        if(empty($image)){
             return messageReturn(40004,'Missing Request IMAGE!');
        }
        if(empty($payer_name)){
            //  return messageReturn(40004,'Missing Request payer_name!');
        }
        if(empty($token)){
             return messageReturn(40005,'Missing Request Token!');
        }else{
            if($token!==md5("QueryId:".$id)){
                return messageReturn(50000,'Token incorrect!');
            }
        }
        $Model=PayinOrder::where(['order_sn' => $id])->findOrEmpty();
        BotService::addLog($prefix,"获取订单信息",$Model);
        if($Model->isEmpty()){
            BotService::addLog($prefix,"订单为空",$Model,"end");
            return messageReturn(40006,'Order does not exist!');
        }else{
            if($Model->status==0){
                $Model->save([
                    "status"=>1,
                    "bank_id"=>$bank_id,
                    "image"=>$image,
                    "payer_name"=>$payer_name,
                    "status_time"=>time(),
                    "update_time"=>time(),
                ]);
                BotService::addLog($prefix,"修改信息",$Model);
                $cb=PayinCallbackService::notify($Model->order_sn);
                BotService::addLog($prefix,"回调返回",$cb);
                // 机器人发送消息
                
                @$btfh=BotSendService::payinSend("",$Model);
                BotService::addLog($prefix,"机器人发送返回",[$btfh],'end');
                
                return messageReturn(200,'success');
            }else{
                if($Model->status==1){
                    BotService::addLog($prefix,"订单已经上传",$Model,"end");
                    return messageReturn(200,'success');
                }
                BotService::addLog($prefix,"订单状态已改变",$Model,"end");
                return messageReturn(50001,'Order status has changed !');
            }
        }
        
        
    }
    // 弃用
    public function botSend($Model){
            $prefix="Cashier";
            $imgUrl=ConfigService::get('img_post_url',"");
            BotService::addLog($prefix,"图片识别",[$imgUrl]); 
            if(!empty($imgUrl)){
                // $img_post_url=$imgUrl.'/api/add/imgPost';
                // $a=imgPosturl($img_post_url,["order_sn"=>$Model->order_sn]);
                // BotService::addLog($prefix,"调用图片识别",[$a]); 
            }
            
            BotService::addLog($prefix,"","收银台开始发送消息","start");
            if(empty($Model->order_sn)){
                BotService::addLog($prefix,"订单有误",$Model,'end');
                return ;
            }
            BotService::addLog($prefix,"订单号",$Model->order_sn);
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
                            ["text"=>'成功回调',"callback_data"=>json_encode(["status"=>2,"order_sn"=>$Model->order_sn])],
                            ["text"=>'失败回调',"callback_data"=>json_encode(["status"=>3,"order_sn"=>$Model->order_sn])]
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
    public function submitTest(){
            $id=input("id")??"";
            $tgSend=["ok"=>false];
            $Model=PayinOrder::where(['order_sn' => $id])->findOrEmpty();
            
            // 设置订单转换信息
            $typeTxt=["","商户订单","手动下单","测试订单" ];
            $Model['typeTxt']=$typeTxt[$Model['type']];
            $Model['imageTime']=date("Y-m-d H:i:s");
         // 获取机器人配置
                BotService::addLog("CashierTest","","收银台开始发送消息");
                BotService::addLog("CashierTest","订单号",$id);
                // 设置机器人下方按钮
                $reply_markup=json_encode([
                    "inline_keyboard"=>[
                        [
                            ["text"=>'成功回调',"callback_data"=>json_encode(["status"=>2,"order_sn"=>$Model->order_sn])],
                            ["text"=>'失败回调',"callback_data"=>json_encode(["status"=>3,"order_sn"=>$Model->order_sn])]
                        ]
                    ]
                ]);
                $wor[]=["bank_id","=",0];
                
                $bank_name=ChannelBank:: where(['id' => $Model->bank_id]) -> value('bank_name');
                
                $bankList=ChannelBank::where(["bank_name"=>$bank_name]) 
                ->field(['id'])
                ->limit(0, 10)
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
                return ajaxReturn(1,'操作成功',[$bank_name,$bankList,$botConfig,$sql1]);
                BotService::addLog("CashierTest","获取商户机器人配置",[$bank_name,$bankList,$botConfig,$sql1]);
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
                    
                    $reData=[
                        "chat_id"=>$it['chat_id'],
                        "caption"=>$_text,
                        "photo"=>$Model->image,
                        "show_caption_above_media"=>true,
                        "reply_markup"=>$reply_markup,
                    ];
                    // $tgSend=(new BotService())->send("/sendPhoto",$reData);
                    // BotService::addLog("CashierTest","发送图片信息返回",[$reData,$tgSend]);
                    // if(!$tgSend['ok']){
                    //     $_text.="<a href='".$Model->image."'>".$it['extra']['image']['txt']."</a>\n";
                    //     $reData=[
                    //         "chat_id"=>$it['chat_id'],
                    //         "text"=>$_text,
                    //         "reply_markup"=>$reply_markup,
                    //     ];
                    //     $tgSend=(new BotService())->send("/sendMessage",$reData);
                    //     BotService::addLog("CashierTest","发送文字信息返回",[$reData,$tgSend]);
                    // }
                    
                }
                BotService::addLog("CashierTest","","商户发送完成","end"); 
                $ptBotConfig=BotGroup::where(["mch_id"=>0,"recipient"=>1,"scene_id"=>2])
                ->where(function ($que)use($wor){$que->whereOr($wor);})
                ->limit(0, 10)
                ->order([ 'id' => 'desc'])
                ->select()
                ->toArray();
                $sql2=BotGroup::getLastSql();
                BotService::addLog("CashierTest","获取平台机器人配置",[$ptBotConfig,$sql2]);
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
                    // $tgSend=(new BotService())->send("/sendPhoto",$reData);
                    // BotService::addLog("CashierTest","发送图片信息返回",$tgSend);
                    // if(!$tgSend['ok']){
                    //     $_text.="<a href='".$Model->image."'>".$it['extra']['image']['txt']."</a>\n";
                    //     $reData=[
                    //         "chat_id"=>$it['chat_id'],
                    //         "text"=>$_text,
                    //         "reply_markup"=>$reply_markup,
                    //     ];
                    //     $tgSend=(new BotService())->send("/sendMessage",$reData);
                    //     BotService::addLog("CashierTest","发送文字信息返回",$tgSend);
                    // }
                }
                BotService::addLog("CashierTest","","平台发送完成","end"); 
    }
}