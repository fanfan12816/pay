<?php

namespace app\mch\controller;

use app\model\AuthRule;
use think\facade\Cache;
use app\MchController;
use app\common\model\{Merchant,PayinOrder,PayoutOrder,MerchantRechargeOrder,MerchantWithdrawOrder,MerchantAccountLog,MerchantChannel,Channel,ChannelBank,BotGroup,Language};
use think\captcha\facade\Captcha;
use hg\apidoc\annotation as Apidoc;
use app\mch\validate\AdminValidate;
use think\exception\ValidateException;
use app\common\cache\MchAccountSafeCache;
use app\common\service\ConfigService;
use app\common\service\FileService;
use app\mch\lists\{BotGroupLists,LanguageLists};
use app\mch\validate\{SystemValidate};

/**
 * @Apidoc\Title("系统信息")
 * Author: JackMater
 */
class SystemController extends MchController {
  
  /**
   * @Apidoc\Title("获取首页数据分析")
   * @Apidoc\Desc("获取首页数据分析")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/system/indexData")
   * @Apidoc\Returned("money", type="float", desc="商户余额")
   * @Apidoc\Returned("timezone", type="string", desc="商户时区")
   * @Apidoc\Returned("unit", type="string", desc="钱币单位")
   * @Apidoc\Returned("current_time", type="string", desc="商户时区当前时间")
   * @Apidoc\Returned("in_ratio", type="float", desc="代收税率")
   * @Apidoc\Returned("out_ratio", type="float", desc="代付税率")
   * @Apidoc\Returned("in_day_price", type="float", desc="当日代收金额")
   * @Apidoc\Returned("out_day_price", type="float", desc="当日代付金额")
   * @Apidoc\Returned("in_day_num", type="int", desc="当日代收数量")
   * @Apidoc\Returned("out_day_num", type="int", desc="当日代付数量")
   * @Apidoc\Returned("in_day_charge", type="float", desc="当日代收手续费")
   * @Apidoc\Returned("out_day_charge", type="float", desc="当日代付手续费")
   * @Apidoc\Returned("in_month_price", type="float", desc="当月代收金额")
   * @Apidoc\Returned("out_month_price", type="float", desc="当月代付金额")
   * @Apidoc\Returned("in_month_num", type="int", desc="当月代收数量")
   * @Apidoc\Returned("out_month_num", type="int", desc="当月代付数量")
   * @Apidoc\Returned("in_month_charge", type="float", desc="当月代收手续费")
   * @Apidoc\Returned("out_month_charge", type="float", desc="当月代付手续费")
   * @Apidoc\Returned("line_chart", type="array", desc="折线图")
   */
  public function indexData() {
    # 如果请求头中有携带token
    if ($this -> mchid) {
        $field = [
            'id', 'sn','nick_name', 'avatar',  'account','is_google','debug','money','reserve_money','timezone','online','login_num','login_time','login_ip','location','disable','create_time','update_time'
        ];
        // return ajaxReturn(200,'测试',[$field,$this->mchid]);
       
        // $User = Merchant::where(['id' => $this -> mchid])->field($field)->findOrEmpty();
        
        $Channel = MerchantChannel::where(['mch_id'=>$this -> mchid])->findOrEmpty();
        
        // $User["login_time"]=diyTimestamp($User["login_time"],$User['timezone']);
        // $User["update_time"]=diyTimestamp($User["update_time"],$User['timezone']);
        // $User["create_time"]=diyTimestamp($User["create_time"],$User['timezone']);
        // $User["pay_pwd"]=$User["pay_pwd"]?1:0;
        
        $day=[];
        $in_price=[];
        $out_price=[];
        $dqnyr=date('Y-m-d H:i:s');
        
        $cxtj[]=["type","<>",3];
        $cxtj[]=["status","=",2];
        $cxtj[]=["mch_id","=",$this -> mchid];
        
        for($i=1;$i<=7;$i++){
            $ii=$i-1;
            $dqnyr1=date('Y-m-d',strtotime("{$dqnyr} -{$i} day"))." 00:00:00";
            $dqnyr2=date('Y-m-d',strtotime("{$dqnyr} -{$ii} day"))." 00:00:00";
            $sjc=diyTimestamp(strtotime($dqnyr1),$this->timezone,true);
            $sjc2=diyTimestamp(strtotime($dqnyr2),$this->timezone,true);
            $day[]=date('Y-m-d',$sjc);
            $xhdr=[];
            $xhdr[]=["create_time",">=",$sjc];
            $xhdr[]=["create_time","<=",$sjc2];
            
            $in_price[]=PayinOrder::where($xhdr)->where($cxtj)->sum("amount");
            $out_price[]=PayoutOrder::where($xhdr)->where($cxtj)->sum("amount");
        }
        $jrsjc=diyTimestamp(strtotime(date('Y-m-d')." 00:00:00"),$this->timezone,true);
        $dysjc=diyTimestamp(strtotime(date('Y-m')."-1 00:00:00"),$this->timezone,true);
        
        $dangri[]=["create_time",">=",$jrsjc];
        $dangyue[]=["create_time",">=",$dysjc];
        
        $in_day_price=PayinOrder::where($dangri)->where($cxtj)->sum("amount");
        $in_day_charge=PayinOrder::where($dangri)->where($cxtj)->sum("service_charge");
        $in_day_num=PayinOrder::where($dangri)->where($cxtj)->count();
        $out_day_price=PayoutOrder::where($dangri)->where($cxtj)->sum("amount");
        $out_day_charge=PayoutOrder::where($dangri)->where($cxtj)->sum("service_charge");
        $out_day_num=PayoutOrder::where($dangri)->where($cxtj)->count();
        $in_month_price=PayinOrder::where($dangyue)->where($cxtj)->sum("amount");
        $in_month_charge=PayinOrder::where($dangyue)->where($cxtj)->sum("service_charge");
        $in_month_num=PayinOrder::where($dangyue)->where($cxtj)->count();
        $out_month_price=PayoutOrder::where($dangyue)->where($cxtj)->sum("amount");
        $out_month_charge=PayoutOrder::where($dangyue)->where($cxtj)->sum("service_charge");
        $out_month_num=PayoutOrder::where($dangyue)->where($cxtj)->count();
        $rest=[
            // "money"=>$User['money'],
            "timezone"=>"UTC ".$this->timezone,
            "current_time"=>diyTimestamp(time(),$this->timezone),
            "in_ratio"=>floatval($Channel['in_ratio'])*100,
            "out_ratio"=>floatval($Channel['out_ratio'])*100,
            "unit"=>"",
            "in_day_price"=>$in_day_price,
            "out_day_price"=>$out_day_price,
            "in_month_price"=>$in_month_price,
            "out_month_price"=>$out_month_price,
            "in_day_charge"=>$in_day_charge,
            "out_day_charge"=>$out_day_charge,
            "in_month_charge"=>$in_month_charge,
            "out_month_charge"=>$out_month_charge,
            "in_day_num"=>$in_day_num,
            "out_day_num"=>$out_day_num,
            "in_month_num"=>$in_month_num,
            "out_month_num"=>$out_month_num,
            "line_chart"=>[
                "day"=>$day,
                "out_price"=>$out_price,
                "in_price"=>$in_price,
            ]
            
        ];
        
        return ajaxReturn(200,'操作成功',$rest);
 
     
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
  }

  /**
   * @Apidoc\Title("站点信息")
   * @Apidoc\Desc("站点信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/system/webSite")
   * @Apidoc\Returned("website_name", type="string", desc="站点名称")
   * @Apidoc\Returned("website_description", type="string", desc="站点描述")
   * @Apidoc\Returned("website_keywords", type="string", desc="站点关键词")
   * @Apidoc\Returned("website_favicon", type="string", desc="站点角标")
   * @Apidoc\Returned("website_logo", type="string", desc="站点logo")
   * @Apidoc\Returned("website_login_logo", type="string", desc="站点登录logo")
   * @Apidoc\Returned("website_home", type="string", desc="首页Logo")
   * @Apidoc\Returned("website_copyright", type="string", desc="版权信息")
   */
  public function webSite() {
    $rest=[
        "website_name"=>ConfigService::get("website_name","shifang"),
        "website_description"=>ConfigService::get("website_description","shifang"),
        "website_favicon"=>FileService::getFileUrl(ConfigService::get("website_favicon","/static/tsl.png")),
        "website_logo"=>FileService::getFileUrl(ConfigService::get("website_logo","/static/tsl.png")),
        "website_login_logo"=>FileService::getFileUrl(ConfigService::get("website_login_logo","/static/tsl.png")),
        "website_home"=>FileService::getFileUrl(ConfigService::get("website_home","/static/tsl.png")),
        "website_copyright"=>ConfigService::get("website_copyright","shifang"),
    ];
    
    return ajaxReturn(200,"操作成功",$rest);
  }

  /**
   * @Apidoc\Title("获取机器人列表")
   * @Apidoc\Desc("获取机器人列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/system/botLists")
   * @Apidoc\Query("channel_id", type="string", require=true, desc="通道号")
   * @Apidoc\Query("chat_id", type="string", require=true, desc="飞机群id")
   * @Apidoc\Query("scene_id", type="string", require=true, desc="通知场景1-代付通知,2-代收通知")
   * @Apidoc\Returned("id", type="string", desc="id")
   * @Apidoc\Returned("chat_id", type="string", desc="飞机群id")
   * @Apidoc\Returned("scene_id", type="string", desc="通知场景1-代付通知,2-代收通知")
   * @Apidoc\Returned("extra", type="string", desc="配置详情")
   * @Apidoc\Returned(ref={BotGroup::class})
   */
  public function botLists() {
    // BotGroup
      # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        return returnDataLists(new BotGroupLists());
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("修改机器人语言")
   * @Apidoc\Desc("修改机器人语言")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/system/updateBotExtra")
   * @Apidoc\Param("id", type="string", require=true, desc="id")
   * @Apidoc\Param("key", type="string", require=true, desc="字段")
   * @Apidoc\Param("en", type="string", require=true, desc="翻译的语言")
   * @Apidoc\Param("show", type="int", require=true, desc="是否显示")
   */
  public function updateBotExtra() {
         # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        $params = (new SystemValidate())->post()->goCheck('updateBotExtra');
        // return messageReturn(300,"debug",$params);
        $bot=BotGroup::where(["id"=>$params['id']])->findOrEmpty();
        if ($bot->isEmpty()) {
            return messageReturn(300,"机器人不存在");
        } else {
            
            $key=$bot['extra'];
            if($params['en']){
                $key[$params['key']]['en']=$params['en'];
            }
            $key[$params['key']]['show']=$params['show'];
            $bot->save([
                "extra"=>$key
            ]);
            return messageReturn(200,"操作成功");
        }
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("修改机器人信息")
   * @Apidoc\Desc("修改机器人信息")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/system/updateBot")
   * @Apidoc\Param("channel_id", type="string", require=true, desc="通道号")
   * @Apidoc\Param("id", type="string", require=true, desc="id")
   * @Apidoc\Param("chat_id", type="string", require=true, desc="飞机群id")
   * @Apidoc\Param("scene_id", type="string", require=true, desc="通知场景1-代付通知,2-代收通知")
   */
  public function updateBot() {
    // BotGroup
      # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        $params = (new SystemValidate())->post()->goCheck('updateBot');
        
        $bot=BotGroup::where(["id"=>$params['id']])->findOrEmpty();
        if ($bot->isEmpty()) {
            return messageReturn(300,"机器人不存在");
        } else {
            $bot->chat_id=$params['chat_id'];
            $bot->scene_id=$params['scene_id'];
            $bot->save();
            return messageReturn(200,"操作成功");
        }
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("新增机器人")
   * @Apidoc\Desc("新增机器人")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/system/addBot")
   * @Apidoc\Param("channel_id", type="string", require=true, desc="通道号")
   * @Apidoc\Param("chat_id", type="string", require=true, desc="飞机群id")
   * @Apidoc\Param("scene_id", type="string", require=true, desc="通知场景1-代付通知,2-代收通知")
   * @Apidoc\Param("extra", type="object", require=true, desc="配置详情")
   */
  public function addBot() {
    // BotGroup
      # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        $params = (new SystemValidate())->post()->goCheck('addBot');
        $Model=BotGroup::where(['chat_id' => input('chat_id',0)])->findOrEmpty();
        if(!$Model->isEmpty()){
           return messageReturn(0,'机器人已经在此群里面了');
        }
        $extra=[
            "imageTime"=>["zh"=>"凭证时间","en"=>"","show"=>1],
            "mch_id"=>["zh"=>"商户编号","en"=>"","show"=>1],
           "channel_id"=> ["zh"=>"通道编号","en"=>"","show"=>1],
            "order_sn"=>["zh"=>"平台订单编号","en"=>"","show"=>1],
            "mch_sn"=>["zh"=>"商户订单编号","en"=>"","show"=>1],
            "typeTxt"=>["zh"=>"订单类型","en"=>"","show"=>1],
            "amount"=>["zh"=>"订单金额","en"=>"","show"=>1],
            "payer_name"=>["zh"=>"付款人姓名","en"=>"","show"=>1],
            "create_time"=>["zh"=>"订单创建时间","en"=>"","show"=>1],
        ];
        if($params['scene_id']){
            $extra['image']=["zh"=>"交易凭证","en"=>"","show"=>1];
        }
        $params['extra']=$extra;
        try {
            BotGroup::create([
                    'extra' => $extra,
                    'chat_id' =>$params['chat_id'],
                    'scene_id' =>$params['scene_id'],
                    'channel_id' =>$params['channel_id'],
                    'mch_id' => $this -> mchid,
                    'recipient' => 2
                ]);
            return messageReturn(200,"操作成功");
        } catch (\Exception $e) {
            return messageReturn(300,"新增失败",$e);
        }
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("删除机器人")
   * @Apidoc\Desc("删除机器人")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/system/delBot")
   * @Apidoc\Param("id", type="string", require=true, desc="id")
   */
  public function delBot() {
    // BotGroup
      # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        $params = (new SystemValidate())->post()->goCheck('delBot');
        
        $bot=BotGroup::where(["id"=>$params['id']])->findOrEmpty();
        if ($bot->isEmpty()) {
            return messageReturn(300,"机器人不存在",$bot);
        } else {
            $bot->delete();
            return messageReturn(200,"操作成功");
        }
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("获取收银台语言配置")
   * @Apidoc\Desc("获取收银台语言配置")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("mch/v1/system/language")
   * @Apidoc\Param("channel_id", type="string", require=true, desc="通道id")
   * @Apidoc\Returned(ref={Language::class})
   */
  public function language() {
    // BotGroup
      # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        return returnDataLists(new LanguageLists());
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  /**
   * @Apidoc\Title("修改配置")
   * @Apidoc\Desc("修改配置")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("mch/v1/system/upLanguage")
   * @Apidoc\Param("channel_id", type="string", require=true, desc="通道id")
   * @Apidoc\Returned(ref={Language::class})
   */
  public function upLanguage() {
    // BotGroup
      # 如果请求头中有携带token type=3
    if ($this -> mchid) {
        $params = (new SystemValidate())->post()->goCheck('language');
         
        $field=["channel_id","title","logo","desc","next","previous","accomplish","bank","bankInfo","credit","await","bankName","bankNum","bankIban","bankUser","price","sn","create","end","scpzts","payName","fzwzts","warn",'bg_img','bg_color',"text_color"];
        $model=Language::where(["channel_id"=>$params['channel_id'],"mch_id"=>$this -> mchid])->field($field)->findOrEmpty();
        if ($model->isEmpty()) {
           return messageReturn(300,"配置不存在",$model);
        } else {
            $newdata=isModification($params,$model);
            // return messageReturn(300,"debug",$newdata);
            if(array_key_exists('newData',$newdata)){
                $model->save($newdata['newData']);
                return messageReturn(200,"操作成功");
            }else{
                return messageReturn(202,"数据相同,未作修改");
            }
        }
    } else {
      # 未登录
      return messageReturn(50001,'您当前未登录');
    }
  }
  
  
 

}