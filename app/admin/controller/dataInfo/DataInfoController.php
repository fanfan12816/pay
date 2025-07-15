<?php

namespace app\admin\controller\dataInfo;

use app\AdminController;
use app\common\model\{Merchant,MerchantChannel,Channel,ChannelBank,PayinOrder,PayoutOrder,MerchantRechargeOrder,MerchantWithdrawOrder};
use app\common\service\MchSystemService;
use hg\apidoc\annotation as Apidoc;

use think\exception\ValidateException;
use app\common\service\{FileService,ConfigService,ExcelService};
/**
 * @Apidoc\Title("a数据分析")
 * Author: JackMater
 */

class DataInfoController extends AdminController {
    
    /**
    * @Apidoc\Title("首页分析台")
    * @Apidoc\Desc("列表")
    * @Apidoc\Method("GET")
    * @Apidoc\Url("admin/v1/dataInfo/index")
    * @Apidoc\Returned("day_payin_num", type="number", desc="今日代收单数")
    * @Apidoc\Returned("day_payin_price", type="number", desc="今日代收金额")
    * @Apidoc\Returned("day_payin_sv", type="number", desc="今日代收服务费")
    * @Apidoc\Returned("count_payin_num", type="number", desc="总代收单数")
    * @Apidoc\Returned("count_payin_price", type="number", desc="总代收金额")
    * @Apidoc\Returned("count_payin_sv", type="number", desc="总代收服务费")
    * @Apidoc\Returned("day_payout_num", type="number", desc="今日代付单数")
    * @Apidoc\Returned("day_payout_price", type="number", desc="今日代付金额")
    * @Apidoc\Returned("day_payout_sv", type="number", desc="今日代付服务费")
    * @Apidoc\Returned("count_payout_num", type="number", desc="总代付单数")
    * @Apidoc\Returned("count_payout_price", type="number", desc="总代付金额")
    * @Apidoc\Returned("count_payout_sv", type="number", desc="总代付服务费")
    * @Apidoc\Returned("line_chart", type="array", desc="折线图")
    */
    public function index() {
        
        $day=[];
        $payin_price=[];
        $payin_num=[];
        $payin_sv=[];
        $payout_price=[];
        $payout_num=[];
        $payout_sv=[];
        $dqnyr=date('Y-m-d H:i:s');
        $cxtj[]=["type","<>",3];
        $cxtj[]=["status","=",2];
        $ct=intval(ConfigService::get("dataInfoNum",30));
        for($i=1;$i<=$ct;$i++){
            $ii=$i-1;
            $dqnyr1=date('Y-m-d',strtotime("{$dqnyr} -{$i} day"))." 00:00:00";
            $dqnyr2=date('Y-m-d',strtotime("{$dqnyr} -{$ii} day"))." 00:00:00";
            $sjc=strtotime($dqnyr1);
            $sjc2=strtotime($dqnyr2);
            $day[]=date('Y-m-d',$sjc);
            $xhdr=[];
            $xhdr[]=["create_time",">=",$sjc];
            $xhdr[]=["create_time","<=",$sjc2];
            $payin_price[]=PayinOrder::where($xhdr)->where($cxtj)->sum("amount");
            $payin_num[]=PayinOrder::where($xhdr)->where($cxtj)->count();
            $payin_sv[]=PayinOrder::where($xhdr)->where($cxtj)->sum("service_charge");
            $payout_price[]=PayoutOrder::where($xhdr)->where($cxtj)->sum("amount");
            $payout_num[]=PayoutOrder::where($xhdr)->where($cxtj)->count();
            $payout_sv[]=PayoutOrder::where($xhdr)->where($cxtj)->sum("service_charge");
        }
        $jrsjc=strtotime(date('Y-m-d')." 00:00:00");
        $dangri[]=["create_time",">=",$jrsjc];
        
        $day_payin_num=PayinOrder::where($dangri)->where($cxtj)->count();
        $day_payin_price=PayinOrder::where($dangri)->where($cxtj)->sum("amount");
        $day_payin_sv=PayinOrder::where($dangri)->where($cxtj)->sum("service_charge");
        $count_payin_num=PayinOrder::where($cxtj)->count();
        $count_payin_price=PayinOrder::where($cxtj)->sum("amount");
        $count_payin_sv=PayinOrder::where($cxtj)->sum("service_charge");
        $day_payout_num=PayoutOrder::where($dangri)->where($cxtj)->count();
        $day_payout_price=PayoutOrder::where($dangri)->where($cxtj)->sum("amount");
        $day_payout_sv=PayoutOrder::where($dangri)->where($cxtj)->sum("service_charge");
        $count_payout_num=PayoutOrder::where($cxtj)->count();
        $count_payout_price=PayoutOrder::where($cxtj)->sum("amount");
        $count_payout_sv=PayoutOrder::where($cxtj)->sum("service_charge");
        $data=[
            "day_payin_num"=>$day_payin_num,
            "day_payin_price"=>$day_payin_price,
            "day_payin_sv"=>$day_payin_sv,
            "count_payin_num"=>$count_payin_num,
            "count_payin_price"=>$count_payin_price,
            "count_payin_sv"=>$count_payin_sv,
            "day_payout_num"=>$day_payout_num,
            "day_payout_price"=>$day_payout_price,
            "day_payout_sv"=>$day_payout_sv,
            "count_payout_num"=>$count_payout_num,
            "count_payout_price"=>$count_payout_price,
            "count_payout_sv"=>$count_payout_sv,
            "line_chart"=>[
                "day"=>$day,
                "payin_price"=>$payin_price,
                "payin_num"=>$payin_num,
                "payin_sv"=>$payin_sv,
                "payout_price"=>$payout_price,
                "payout_num"=>$payout_num,
                "payout_sv"=>$payout_sv,
            ] 
        ];
        return ajaxReturn(1,'操作成功',$data);
    }
    /**
    * @Apidoc\Title("代收数据")
    * @Apidoc\Desc("列表")
    * @Apidoc\Method("GET")
    * @Apidoc\Url("admin/v1/dataInfo/payin")
    * @Apidoc\Query("mch_id", type="number", require=true,default="", desc="商户ID")
    * @Apidoc\Query("channel_id", type="number", require=true,default="", desc="通道ID")
    * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
    * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
    * @Apidoc\Returned("num", type="number", desc="代收单数")
    * @Apidoc\Returned("price", type="number", desc="代收金额")
    * @Apidoc\Returned("sv", type="number", desc="代收服务费")
    * @Apidoc\Returned("count_num", type="number", desc="总代收单数")
    * @Apidoc\Returned("count_price", type="number", desc="总代收金额")
    * @Apidoc\Returned("count_sv", type="number", desc="总代收服务费")
    * @Apidoc\Returned("list", type="array", desc="查询数据")
    */
    public function payin() {
        $start_time=input("start_time","");
        $end_time=input("end_time","");
        $mch_id=input("mch_id","");
        $channel_id=input("channel_id","");
        $ct=intval(ConfigService::get("dataInfoNum",15));
        $dqnyr=date('Y-m-d H:i:s');
        if(!empty($start_time)&&!empty($end_time)){
            $start_date = strtotime($start_time);
            $end_date = strtotime($end_time);
            $seconds_diff = $end_date - $start_date;
            $ct = floor($seconds_diff / 86400);
            $dqnyr=date('Y-m-d H:i:s',$end_date);
        }
        $list=[];
        // $dqnyr=date('Y-m-d H:i:s');
        $cxtj[]=["type","<>",3];
        $cxtj[]=["status","=",2];
        if(!empty($mch_id)){
            $cxtj[]=["mch_id","=",$mch_id];
        }
        if(!empty($channel_id)){
            $cxtj[]=["channel_id","=",$channel_id];
        }
        for($i=1;$i<=$ct;$i++){
            $ii=$i-1;
            $dqnyr1=date('Y-m-d',strtotime("{$dqnyr} -{$i} day"))." 00:00:00";
            $dqnyr2=date('Y-m-d',strtotime("{$dqnyr} -{$ii} day"))." 00:00:00";
            $sjc=strtotime($dqnyr1);
            $sjc2=strtotime($dqnyr2);
            $day=date('Y-m-d',$sjc);
            $xhdr=[];
            $xhdr[]=["create_time",">=",$sjc];
            $xhdr[]=["create_time","<=",$sjc2];
            $list[]=[
                "day"=>$day,
                "num"=>PayinOrder::where($xhdr)->where($cxtj)->count(),
                "price"=>PayinOrder::where($xhdr)->where($cxtj)->sum("amount"),
                "sv"=>PayinOrder::where($xhdr)->where($cxtj)->sum("service_charge"),
            ];
        }
        $jrsjc=strtotime(date('Y-m-d')." 00:00:00");
        $dangri[]=["create_time",">=",$jrsjc];
        $data=[
            "num"=>PayinOrder::where($dangri)->where($cxtj)->count(),
            "price"=>PayinOrder::where($dangri)->where($cxtj)->sum("amount"),
            "sv"=>PayinOrder::where($dangri)->where($cxtj)->sum("service_charge"),
            "count_price"=>PayinOrder::where($cxtj)->sum("amount"),
            "count_num"=>PayinOrder::where($cxtj)->count(),
            "count_sv"=>PayinOrder::where($cxtj)->sum("service_charge"),
            "data"=>$list
        ];
        return ajaxReturn(1,'操作成功',$data);
    }
    /**
    * @Apidoc\Title("代收数据导出")
    * @Apidoc\Desc("列表")
    * @Apidoc\Method("GET")
    * @Apidoc\Url("admin/v1/dataInfo/payinExport")
    * @Apidoc\Query("mch_id", type="number", require=true,default="", desc="商户ID")
    * @Apidoc\Query("channel_id", type="number", require=true,default="", desc="通道ID")
    * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
    * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
    * @Apidoc\Returned("url", type="string", desc="文件URL地址")
    */
    public function payinExport() {
        $start_time=input("start_time","");
        $end_time=input("end_time","");
        $mch_id=input("mch_id","");
        $channel_id=input("channel_id","");
        $ct=intval(ConfigService::get("dataInfoNum",15));
        $dqnyr=date('Y-m-d H:i:s');
        if(!empty($start_time)&&!empty($end_time)){
            $start_date = strtotime($start_time);
            $end_date = strtotime($end_time);
            $seconds_diff = $end_date - $start_date;
            $ct = floor($seconds_diff / 86400);
            $dqnyr=date('Y-m-d H:i:s',$end_date);
        }
        $list=[];
        // $dqnyr=date('Y-m-d H:i:s');
        $cxtj[]=["type","<>",3];
        $cxtj[]=["status","=",2];
        if(!empty($mch_id)){
            $cxtj[]=["mch_id","=",$mch_id];
        }
        if(!empty($channel_id)){
            $cxtj[]=["channel_id","=",$channel_id];
        }
        for($i=1;$i<=$ct;$i++){
            $ii=$i-1;
            $dqnyr1=date('Y-m-d',strtotime("{$dqnyr} -{$i} day"))." 00:00:00";
            $dqnyr2=date('Y-m-d',strtotime("{$dqnyr} -{$ii} day"))." 00:00:00";
            $sjc=strtotime($dqnyr1);
            $sjc2=strtotime($dqnyr2);
            $day=date('Y-m-d',$sjc);
            $xhdr=[];
            $xhdr[]=["create_time",">=",$sjc];
            $xhdr[]=["create_time","<=",$sjc2];
            $list[]=[
                "day"=>$day,
                "num"=>PayinOrder::where($xhdr)->where($cxtj)->count(),
                "price"=>PayinOrder::where($xhdr)->where($cxtj)->sum("amount"),
                "sv"=>PayinOrder::where($xhdr)->where($cxtj)->sum("service_charge"),
            ];
        }
        $head=[
            ["key"=>"day","title"=>"统计日期","txt"=>""],
            ["key"=>"num","title"=>"代收单数","txt"=>""],
            ["key"=>"price","title"=>"代收金额","txt"=>""],
            ["key"=>"sv","title"=>"代收手续费","txt"=>""],
        ];
        
        $rt=ExcelService::excel($head, $list,"dataInfoPayin",'代收统计列表');
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(1,'操作成功',$data);
    }
    /**
    * @Apidoc\Title("代付数据")
    * @Apidoc\Desc("列表")
    * @Apidoc\Method("GET")
    * @Apidoc\Url("admin/v1/dataInfo/payout")
    * @Apidoc\Query("mch_id", type="number", require=true,default="", desc="商户ID")
    * @Apidoc\Query("channel_id", type="number", require=true,default="", desc="通道ID")
    * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
    * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
    * @Apidoc\Returned("num", type="number", desc="代付单数")
    * @Apidoc\Returned("price", type="number", desc="代付金额")
    * @Apidoc\Returned("sv", type="number", desc="代付服务费")
    * @Apidoc\Returned("count_num", type="number", desc="总代付单数")
    * @Apidoc\Returned("count_price", type="number", desc="总代付金额")
    * @Apidoc\Returned("count_sv", type="number", desc="总代付服务费")
    * @Apidoc\Returned("list", type="array", desc="查询数据")
    */
    public function payout() {
        $start_time=input("start_time","");
        $end_time=input("end_time","");
        $mch_id=input("mch_id","");
        $channel_id=input("channel_id","");
        $ct=intval(ConfigService::get("dataInfoNum",15));
        $dqnyr=date('Y-m-d H:i:s');
        if(!empty($start_time)&&!empty($end_time)){
            $start_date = strtotime($start_time);
            $end_date = strtotime($end_time);
            $seconds_diff = $end_date - $start_date;
            $ct = floor($seconds_diff / 86400);
            $dqnyr=date('Y-m-d H:i:s',$end_date);
        }
        $list=[];
        // $dqnyr=date('Y-m-d H:i:s');
        $cxtj[]=["type","<>",3];
        $cxtj[]=["status","=",2];
        if(!empty($mch_id)){
            $cxtj[]=["mch_id","=",$mch_id];
        }
        if(!empty($channel_id)){
            $cxtj[]=["channel_id","=",$channel_id];
        }
        for($i=1;$i<=$ct;$i++){
            $ii=$i-1;
            $dqnyr1=date('Y-m-d',strtotime("{$dqnyr} -{$i} day"))." 00:00:00";
            $dqnyr2=date('Y-m-d',strtotime("{$dqnyr} -{$ii} day"))." 00:00:00";
            $sjc=strtotime($dqnyr1);
            $sjc2=strtotime($dqnyr2);
            $day=date('Y-m-d',$sjc);
            $xhdr=[];
            $xhdr[]=["create_time",">=",$sjc];
            $xhdr[]=["create_time","<=",$sjc2];
            $list[]=[
                "day"=>$day,
                "num"=>PayoutOrder::where($xhdr)->where($cxtj)->count(),
                "price"=>PayoutOrder::where($xhdr)->where($cxtj)->sum("amount"),
                "sv"=>PayoutOrder::where($xhdr)->where($cxtj)->sum("service_charge"),
            ];
        }
        $jrsjc=strtotime(date('Y-m-d')." 00:00:00");
        $dangri[]=["create_time",">=",$jrsjc];
        $data=[
            "num"=>PayoutOrder::where($dangri)->where($cxtj)->count(),
            "price"=>PayoutOrder::where($dangri)->where($cxtj)->sum("amount"),
            "sv"=>PayoutOrder::where($dangri)->where($cxtj)->sum("service_charge"),
            "count_num"=>PayoutOrder::where($cxtj)->count(),
            "count_price"=>PayoutOrder::where($cxtj)->sum("amount"),
            "count_sv"=>PayoutOrder::where($cxtj)->sum("service_charge"),
            "data"=>$list
        ];
        return ajaxReturn(1,'操作成功',$data);
        
    }
    /**
    * @Apidoc\Title("代付数据导出")
    * @Apidoc\Desc("列表")
    * @Apidoc\Method("GET")
    * @Apidoc\Url("admin/v1/dataInfo/payoutExport")
    * @Apidoc\Query("mch_id", type="number", require=true,default="", desc="商户ID")
    * @Apidoc\Query("channel_id", type="number", require=true,default="", desc="通道ID")
    * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
    * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
    * @Apidoc\Returned("url", type="string", desc="文件URL地址")
    */
    public function payoutExport() {
        $start_time=input("start_time","");
        $end_time=input("end_time","");
        $mch_id=input("mch_id","");
        $channel_id=input("channel_id","");
        $ct=intval(ConfigService::get("dataInfoNum",15));
        $dqnyr=date('Y-m-d H:i:s');
        if(!empty($start_time)&&!empty($end_time)){
            $start_date = strtotime($start_time);
            $end_date = strtotime($end_time);
            $seconds_diff = $end_date - $start_date;
            $ct = floor($seconds_diff / 86400);
            $dqnyr=date('Y-m-d H:i:s',$end_date);
        }
        $list=[];
        // $dqnyr=date('Y-m-d H:i:s');
        $cxtj[]=["type","<>",3];
        $cxtj[]=["status","=",2];
        if(!empty($mch_id)){
            $cxtj[]=["mch_id","=",$mch_id];
        }
        if(!empty($channel_id)){
            $cxtj[]=["channel_id","=",$channel_id];
        }
        for($i=1;$i<=$ct;$i++){
            $ii=$i-1;
            $dqnyr1=date('Y-m-d',strtotime("{$dqnyr} -{$i} day"))." 00:00:00";
            $dqnyr2=date('Y-m-d',strtotime("{$dqnyr} -{$ii} day"))." 00:00:00";
            $sjc=strtotime($dqnyr1);
            $sjc2=strtotime($dqnyr2);
            $day=date('Y-m-d',$sjc);
            $xhdr=[];
            $xhdr[]=["create_time",">=",$sjc];
            $xhdr[]=["create_time","<=",$sjc2];
            $list[]=[
                "day"=>$day,
                "num"=>PayoutOrder::where($xhdr)->where($cxtj)->count(),
                "price"=>PayoutOrder::where($xhdr)->where($cxtj)->sum("amount"),
                "sv"=>PayoutOrder::where($xhdr)->where($cxtj)->sum("service_charge"),
            ];
        }
        $head=[
            ["key"=>"day","title"=>"统计日期","txt"=>""],
            ["key"=>"num","title"=>"代付单数","txt"=>""],
            ["key"=>"price","title"=>"代付金额","txt"=>""],
            ["key"=>"sv","title"=>"代付手续费","txt"=>""],
        ];
        
        $rt=ExcelService::excel($head, $list,"dataInfoPayout",'代付统计列表');
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(1,'操作成功',$data);
    }
    /**
    * @Apidoc\Title("充值数据")
    * @Apidoc\Desc("列表")
    * @Apidoc\Method("GET")
    * @Apidoc\Url("admin/v1/dataInfo/recharge")
    * @Apidoc\Query("mch_id", type="number", require=true,default="", desc="商户ID")
    * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
    * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
    * @Apidoc\Returned("num", type="number", desc="充值单数")
    * @Apidoc\Returned("price", type="number", desc="充值金额")
    * @Apidoc\Returned("sv", type="number", desc="充值手续费")
    * @Apidoc\Returned("count_num", type="number", desc="总充值单数")
    * @Apidoc\Returned("count_price", type="number", desc="总充值金额")
    * @Apidoc\Returned("count_sv", type="number", desc="总充值手续费")
    * @Apidoc\Returned("list", type="array", desc="查询数据")
    */
    public function recharge() {
        $start_time=input("start_time","");
        $end_time=input("end_time","");
        $mch_id=input("mch_id","");
        $ct=intval(ConfigService::get("dataInfoNum",15));
        $dqnyr=date('Y-m-d H:i:s');
        if(!empty($start_time)&&!empty($end_time)){
            $start_date = strtotime($start_time);
            $end_date = strtotime($end_time);
            $seconds_diff = $end_date - $start_date;
            $ct = floor($seconds_diff / 86400);
            $dqnyr=date('Y-m-d H:i:s',$end_date);
        }
        $list=[];
        // $dqnyr=date('Y-m-d H:i:s');
        $cxtj[]=["status","=",1];
        if(!empty($mch_id)){
            $cxtj[]=["mch_id","=",$mch_id];
        }
        for($i=1;$i<=$ct;$i++){
            $ii=$i-1;
            $dqnyr1=date('Y-m-d',strtotime("{$dqnyr} -{$i} day"))." 00:00:00";
            $dqnyr2=date('Y-m-d',strtotime("{$dqnyr} -{$ii} day"))." 00:00:00";
            $sjc=strtotime($dqnyr1);
            $sjc2=strtotime($dqnyr2);
            $day=date('Y-m-d',$sjc);
            $xhdr=[];
            $xhdr[]=["create_time",">=",$sjc];
            $xhdr[]=["create_time","<=",$sjc2];
            $list[]=[
                "day"=>$day,
                "num"=>MerchantRechargeOrder::where($xhdr)->where($cxtj)->count(),
                "price"=>MerchantRechargeOrder::where($xhdr)->where($cxtj)->sum("order_amount"),
                "sv"=>MerchantRechargeOrder::where($xhdr)->where($cxtj)->sum("service_charge"),
            ];
        }
        $jrsjc=strtotime(date('Y-m-d')." 00:00:00");
        $dangri[]=["create_time",">=",$jrsjc];
        $data=[
            "num"=>MerchantRechargeOrder::where($dangri)->where($cxtj)->count(),
            "price"=>MerchantRechargeOrder::where($dangri)->where($cxtj)->sum("order_amount"),
            "sv"=>MerchantRechargeOrder::where($dangri)->where($cxtj)->sum("service_charge"),
            "count_num"=>MerchantRechargeOrder::where($cxtj)->count(),
            "count_price"=>MerchantRechargeOrder::where($cxtj)->sum("order_amount"),
            "count_sv"=>MerchantRechargeOrder::where($cxtj)->sum("service_charge"),
            "data"=>$list
        ];
        return ajaxReturn(1,'操作成功',$data);
    }
        /**
    * @Apidoc\Title("充值数据导出")
    * @Apidoc\Desc("列表")
    * @Apidoc\Method("GET")
    * @Apidoc\Url("admin/v1/dataInfo/rechargeExport")
    * @Apidoc\Query("mch_id", type="number", require=true,default="", desc="商户ID")
    * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
    * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
    * @Apidoc\Returned("url", type="string", desc="文件URL地址")
    */
    public function rechargeExport() {
        $start_time=input("start_time","");
        $end_time=input("end_time","");
        $mch_id=input("mch_id","");
        $ct=intval(ConfigService::get("dataInfoNum",15));
        $dqnyr=date('Y-m-d H:i:s');
        if(!empty($start_time)&&!empty($end_time)){
            $start_date = strtotime($start_time);
            $end_date = strtotime($end_time);
            $seconds_diff = $end_date - $start_date;
            $ct = floor($seconds_diff / 86400);
            $dqnyr=date('Y-m-d H:i:s',$end_date);
        }
        $list=[];
        // $dqnyr=date('Y-m-d H:i:s');
        $cxtj[]=["status","=",1];
        for($i=1;$i<=$ct;$i++){
            $ii=$i-1;
            $dqnyr1=date('Y-m-d',strtotime("{$dqnyr} -{$i} day"))." 00:00:00";
            $dqnyr2=date('Y-m-d',strtotime("{$dqnyr} -{$ii} day"))." 00:00:00";
            $sjc=strtotime($dqnyr1);
            $sjc2=strtotime($dqnyr2);
            $day=date('Y-m-d',$sjc);
            $xhdr=[];
            $xhdr[]=["create_time",">=",$sjc];
            $xhdr[]=["create_time","<=",$sjc2];
            if(!empty($mch_id)){
                $cxtj[]=["mch_id","=",$mch_id];
            }
            $list[]=[
                "day"=>$day,
                "num"=>MerchantRechargeOrder::where($xhdr)->where($cxtj)->count(),
                "price"=>MerchantRechargeOrder::where($xhdr)->where($cxtj)->sum("order_amount"),
                "sv"=>MerchantRechargeOrder::where($xhdr)->where($cxtj)->sum("service_charge"),
            ];
        }
        $head=[
            ["key"=>"day","title"=>"统计日期","txt"=>""],
            ["key"=>"num","title"=>"充值单数","txt"=>""],
            ["key"=>"price","title"=>"充值金额","txt"=>""],
            ["key"=>"sv","title"=>"充值手续费","txt"=>""],
        ];
        
        $rt=ExcelService::excel($head, $list,"dataInfoRecharge",'充值统计列表');
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(1,'操作成功',$data);
    }
    /**
    * @Apidoc\Title("提现数据	")
    * @Apidoc\Desc("列表")
    * @Apidoc\Method("GET")
    * @Apidoc\Url("admin/v1/dataInfo/withdraw")
    * @Apidoc\Query("mch_id", type="number", require=true,default="", desc="商户ID")
    * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
    * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
    * @Apidoc\Returned("num", type="number", desc="提现单数")
    * @Apidoc\Returned("price", type="number", desc="提现金额")
    * @Apidoc\Returned("sv", type="number", desc="提现手续费")
    * @Apidoc\Returned("count_num", type="number", desc="总提现单数")
    * @Apidoc\Returned("count_price", type="number", desc="总提现金额")
    * @Apidoc\Returned("count_sv", type="number", desc="总提现手续费")
    * @Apidoc\Returned("list", type="array", desc="查询数据")
    */
    public function withdraw() {
        $start_time=input("start_time","");
        $end_time=input("end_time","");
        $mch_id=input("mch_id","");
        $ct=intval(ConfigService::get("dataInfoNum",15));
        $dqnyr=date('Y-m-d H:i:s');
        if(!empty($start_time)&&!empty($end_time)){
            $start_date = strtotime($start_time);
            $end_date = strtotime($end_time);
            $seconds_diff = $end_date - $start_date;
            $ct = floor($seconds_diff / 86400);
            $dqnyr=date('Y-m-d H:i:s',$end_date);
        }
        $list=[];
        // $dqnyr=date('Y-m-d H:i:s');
        $cxtj[]=["status","=",1];
        if(!empty($mch_id)){
            $cxtj[]=["mch_id","=",$mch_id];
        }
        for($i=1;$i<=$ct;$i++){
            $ii=$i-1;
            $dqnyr1=date('Y-m-d',strtotime("{$dqnyr} -{$i} day"))." 00:00:00";
            $dqnyr2=date('Y-m-d',strtotime("{$dqnyr} -{$ii} day"))." 00:00:00";
            $sjc=strtotime($dqnyr1);
            $sjc2=strtotime($dqnyr2);
            $day=date('Y-m-d',$sjc);
            $xhdr=[];
            $xhdr[]=["create_time",">=",$sjc];
            $xhdr[]=["create_time","<=",$sjc2];
            $list[]=[
                "day"=>$day,
                "num"=>MerchantWithdrawOrder::where($xhdr)->where($cxtj)->count(),
                "price"=>MerchantWithdrawOrder::where($xhdr)->where($cxtj)->sum("order_amount"),
                "sv"=>MerchantWithdrawOrder::where($xhdr)->where($cxtj)->sum("service_charge"),
            ];
        }
        $jrsjc=strtotime(date('Y-m-d')." 00:00:00");
        $dangri[]=["create_time",">=",$jrsjc];
        $data=[
            "num"=>MerchantWithdrawOrder::where($dangri)->where($cxtj)->count(),
            "price"=>MerchantWithdrawOrder::where($dangri)->where($cxtj)->sum("order_amount"),
            "sv"=>MerchantWithdrawOrder::where($dangri)->where($cxtj)->sum("service_charge"),
            "count_num"=>MerchantWithdrawOrder::where($cxtj)->sum("order_amount"),
            "count_price"=>MerchantWithdrawOrder::where($cxtj)->count(),
            "count_sv"=>MerchantWithdrawOrder::where($cxtj)->sum("service_charge"),
            "data"=>$list
        ];
        return ajaxReturn(1,'操作成功',$data);
    }
            /**
    * @Apidoc\Title("提现数据导出")
    * @Apidoc\Desc("列表")
    * @Apidoc\Method("GET")
    * @Apidoc\Url("admin/v1/dataInfo/withdrawExport")
    * @Apidoc\Query("mch_id", type="number", require=true,default="", desc="商户ID")
    * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
    * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
    * @Apidoc\Returned("url", type="string", desc="文件URL地址")
    */
    public function withdrawExport() {
        $start_time=input("start_time","");
        $end_time=input("end_time","");
        $mch_id=input("mch_id","");
        $ct=intval(ConfigService::get("dataInfoNum",15));
        $dqnyr=date('Y-m-d H:i:s');
        if(!empty($start_time)&&!empty($end_time)){
            $start_date = strtotime($start_time);
            $end_date = strtotime($end_time);
            $seconds_diff = $end_date - $start_date;
            $ct = floor($seconds_diff / 86400);
            $dqnyr=date('Y-m-d H:i:s',$end_date);
        }
        $list=[];
        // $dqnyr=date('Y-m-d H:i:s');
        $cxtj[]=["status","=",1];
        if(!empty($mch_id)){
            $cxtj[]=["mch_id","=",$mch_id];
        }
        for($i=1;$i<=$ct;$i++){
            $ii=$i-1;
            $dqnyr1=date('Y-m-d',strtotime("{$dqnyr} -{$i} day"))." 00:00:00";
            $dqnyr2=date('Y-m-d',strtotime("{$dqnyr} -{$ii} day"))." 00:00:00";
            $sjc=strtotime($dqnyr1);
            $sjc2=strtotime($dqnyr2);
            $day=date('Y-m-d',$sjc);
            $xhdr=[];
            $xhdr[]=["create_time",">=",$sjc];
            $xhdr[]=["create_time","<=",$sjc2];
            $list[]=[
                "day"=>$day,
                "num"=>MerchantWithdrawOrder::where($xhdr)->where($cxtj)->count(),
                "price"=>MerchantWithdrawOrder::where($xhdr)->where($cxtj)->sum("order_amount"),
                "sv"=>MerchantWithdrawOrder::where($xhdr)->where($cxtj)->sum("service_charge"),
            ];
        }
        $head=[
            ["key"=>"day","title"=>"统计日期","txt"=>""],
            ["key"=>"num","title"=>"提现单数","txt"=>""],
            ["key"=>"price","title"=>"提现金额","txt"=>""],
            ["key"=>"sv","title"=>"提现手续费","txt"=>""],
        ];
        
        $rt=ExcelService::excel($head, $list,"dataInfoWithdraw",'提现统计列表');
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(1,'操作成功',$data);
    }
    
}