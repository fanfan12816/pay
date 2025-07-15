<?php

namespace app\admin\controller\system;

use app\AdminController;
use app\common\model\{Merchant,MerchantChannel,Channel,ChannelBank,BotGroup};
use app\common\service\MchSystemService;
use hg\apidoc\annotation as Apidoc;

use app\admin\lists\system\BotGroupLists;
use app\admin\validate\system\BotGroupValidate;

use think\exception\ValidateException;
use app\common\service\{ConfigService,FileService};



/**
 * @Apidoc\Title("a系统管理-机器人群管理")
 * Author: JackMater
 */

class BotGroupController extends AdminController {
  
  /**
   * @Apidoc\Title("列表")
   * @Apidoc\Desc("列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/system/bot/lists")
   * @Apidoc\Query("keyword", type="string", require=true, desc="关键字搜索")
   * @Apidoc\Query("mch_id", type="string", require=true, desc="商户id")
   * @Apidoc\Query("channel_id", type="string", require=true, desc="通道号id")
   * @Apidoc\Query("bank_id", type="string", require=true, desc="指定银行卡id")
   * @Apidoc\Query("scene_id", type="number", require=true, desc="场景,1-代付通知,2-代收通知,3-代付回调通知,4-代收回调收通知'")
   * @Apidoc\Query("recipient", type="number", require=true, desc="通知接收对象类型;1-平台;2-商家;")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("pageIndex", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("pageSize", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={BotGroup::class})
   */
  public function lists() {
    //   return messageReturn(1,'开发中');
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $export=input("export")??0;
        if($export==0){
            return returnDataListsAdmin(new BotGroupLists(),1);
        }else{
            $data=["url"=>FileService::getFileUrl("/UploadFile/excel/member_template.xlsx")];
            return ajaxReturn(1,'操作成功',$data);
        }
        
     
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }
  
  /**
   * @Apidoc\Title("导出")
   * @Apidoc\Desc("导出")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/system/bot/inExport")
   * @Apidoc\Query("keyword", type="string", require=true, desc="关键字搜索")
   * @Apidoc\Query("mch_id", type="string", require=true, desc="商户id")
   * @Apidoc\Query("channel_id", type="string", require=true, desc="通道号id")
   * @Apidoc\Query("bank_id", type="string", require=true, desc="指定银行卡id")
   * @Apidoc\Query("scene_id", type="number", require=true, desc="场景,1-代付通知,2-代收通知,3-代付回调通知,4-代收回调收通知'")
   * @Apidoc\Query("recipient", type="number", require=true, desc="通知接收对象类型;1-平台;2-商家;")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("export", type="number", require=true,default="0", desc="导出,1导出")
   * @Apidoc\Query("pageIndex", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("pageSize", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("url", type="string", desc="文件URL地址")
   */
  public function inExport() {
      return messageReturn(0,'未配置');
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $list = new BotGroupLists();
        $data=["url"=>FileService::getFileUrl("/UploadFile/excel/member_template.xlsx")];
        return ajaxReturn(1,'操作成功',$data);
     
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }

  /**
   * @Apidoc\Title("新增")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/system/bot/add")
   * @Apidoc\Param("mch_id", type="string", desc="商户id")
   * @Apidoc\Param("chat_id", type="string", desc="飞机群id")
   * @Apidoc\Param("channel_id", type="string", desc="通道id")
   * @Apidoc\Param("bank_id", type="string", desc="指定银行卡id")
   * @Apidoc\Param("scene_id", type="string", desc="场景,场景,1-代付通知,2-代收通知,3-代付回调通知,4-代收回调收通知")
   * @Apidoc\Param("recipient", type="string", desc="通知接收对象类型;1-全部;2-商家")
   * @Apidoc\Param("remark", type="string", desc="备注")
   */
  public function add() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new BotGroupValidate())->post()->goCheck('add');
        $mchid=input('mch_id','');
        $bank_id=input("bank_id",0);
        $Model=BotGroup::where(['chat_id' => input('chat_id',0)])->findOrEmpty();
        if(!$Model->isEmpty()){
           return messageReturn(0,'机器人已经在此群里面了');
        }
        if($bank_id!=0){
            $wor=[];
            $bank_name=ChannelBank:: where(['id' => $bank_id]) -> value('bank_name');
            $bankList=ChannelBank::where(["bank_name"=>$bank_name]) 
            ->field(['id'])
            ->limit(0, 10)
            ->order([ 'id' => 'desc'])
            ->select()
            ->toArray();
            foreach ($bankList as $b){
                $wor[]=["bank_id","=",$b['id']];
            }
            $md=BotGroup::where(function ($que)use($wor){$que->whereOr($wor);})->where(['mch_id' => $mchid])->findOrEmpty();
            // $sql=BotGroup::getLastSql();
            // return messageReturn(300,"新增失败",[$md,$sql,$bank_name,$bankList,$wor]);
            if(!$md->isEmpty()){
               return messageReturn(0,'银行【'.$bank_name.'】已经创建机器人了');
            }
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
                    'mch_id' =>$mchid,
                    'chat_id' =>input('chat_id',''),
                    'channel_id' =>input('channel_id',''),
                    'bank_id' =>input('bank_id',''),
                    'scene_id' =>input('scene_id',''),
                    'recipient' =>input('recipient',''),
                    'remark' =>input('remark',''),
                    'extra' => $extra,
                    "update_by"=>$this -> member_id,
                ]);
            return messageReturn(1,"操作成功");
        } catch (\Exception $e) {
            return messageReturn(300,"新增失败",$e);
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }

  /**
   * @Apidoc\Title("编辑")
   * @Apidoc\Desc("开发中")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/system/bot/edit")
   * @Apidoc\Param("id", type="string", desc="主键id")
   * @Apidoc\Param("mch_id", type="string", desc="商户id")
   * @Apidoc\Param("chat_id", type="string", desc="飞机群id")
   * @Apidoc\Param("channel_id", type="string", desc="通道id")
   * @Apidoc\Param("bank_id", type="string", desc="指定银行卡id")
   * @Apidoc\Param("scene_id", type="string", desc="场景,场景,1-代付通知,2-代收通知,3-代付回调通知,4-代收回调收通知")
   * @Apidoc\Param("recipient", type="string", desc="通知接收对象类型;1-全部;2-商家")
   * @Apidoc\Param("remark", type="string", desc="备注")
   */
  public function edit() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new BotGroupValidate())->post()->goCheck('edit');
        $id=input("id",0);
        $Model=BotGroup::where(['id' => $id])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'机器人群不存在');
        }else{
            $newdata=isModification($params,$Model);
            if(array_key_exists('newData',$newdata)){
                $newdata['newData']['update_by']=$this -> member_id;
                $Model->allowField(['mch_id', 'chat_id','channel_id','bank_id','scene_id','recipient','remark','update_by'])->save($newdata['newData']);
                return messageReturn(1,"操作成功");
            }else{
                return messageReturn(1,"数据相同,未作修改");
            }
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }

  /**
   * @Apidoc\Title("配置")
   * @Apidoc\Desc("开发中")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/system/bot/config")
   * @Apidoc\Param("id", type="string", desc="主键id")
   * @Apidoc\Param("channel_id", type="string", desc="通道id")
   * @Apidoc\Param("pay_type", type="string", desc="收款类型:0=代收,1代付")
   * @Apidoc\Param("type", type="string", desc="类型:0银行卡,1钱包")
   * @Apidoc\Param("desc", type="string", desc="描述")
   * @Apidoc\Param("bank_name", type="string", desc="银行卡名称/钱包名称")
   * @Apidoc\Param("user_name", type="string", desc="持卡人名称")
   * @Apidoc\Param("bank_num", type="string", desc="银行卡号码/钱包卡号")
   * @Apidoc\Param("iban", type="string", desc="iban")
   * @Apidoc\Param("image", type="string", desc="图片地址")
   * @Apidoc\Param("min", type="string", desc="最小值")
   * @Apidoc\Param("max", type="string", desc="最大值")
   * @Apidoc\Param("status", type="string", desc="状态:0-关闭,1开启")
   * @Apidoc\Param("remark", type="string", desc="备注")
   * @Apidoc\Param("sort", type="string", desc="排序")
   */
  public function config() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new BotGroupValidate())->post()->goCheck('config');
        $id=input("id",0);
        $bot=BotGroup::where(["id"=>$id])->findOrEmpty();
        if ($bot->isEmpty()) {
            return messageReturn(300,"机器人不存在");
        } else {
            
            $key=$bot['extra'];
            if($params['en']){
                $key[$params['key']]['en']=$params['en'];
            }
            $key[$params['key']]['show']=$params['show'];
            $bot->save([
                'update_by'=>$this -> member_id,
                "extra"=>$key
            ]);
            return messageReturn(1,"操作成功");
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }
 
  /**
   * @Apidoc\Title("删除")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/system/bot/del")
   * @Apidoc\Param("id", type="string", desc="主键id")
   */
  public function del() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new BotGroupValidate())->post()->goCheck('del');
        $id=input("id",0);
        $Model=BotGroup::where(['id' => $id])->findOrEmpty();
        if($Model->isEmpty()){
           return messageReturn(0,'不存在');
        }else{
            $Model->save([
                "update_by"=>$this -> member_id
            ]);
            $Model->delete();
            return messageReturn(1,"操作成功");
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }

  
}