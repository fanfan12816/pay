<?php

namespace app\admin\controller\merchant;

use app\AdminController;
use app\common\model\{Merchant};
use app\common\service\MchSystemService;
use hg\apidoc\annotation as Apidoc;

use app\admin\lists\merchant\MerchantLists;
use app\admin\validate\merchant\MerchantValidate;
use app\common\service\ConfigService;
use think\exception\ValidateException;
use app\common\service\{FileService,ExcelService};

/**
 * @Apidoc\Title("a商户管理")
 * Author: JackMater
 */

class MerchantController extends AdminController {
  
  /**
   * @Apidoc\Title("商户列表")
   * @Apidoc\Desc("商户列表")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/merchant/lists")
   * @Apidoc\Query("sn", type="string", require=false, desc="用户编号")
   * @Apidoc\Query("keyword", type="string", require=false, desc="关键字搜索")
   * @Apidoc\Query("debug", type="number", require=false, desc="是否开启沙盒测试 0-否 1-是")
   * @Apidoc\Query("ip_white", type="number", require=false, desc="ip白名单")
   * @Apidoc\Query("disable", type="number", require=true, desc="是否禁用：0-否；1-是；")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("pageIndex", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("pageSize", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("pno", type="number", desc="当前页码")
   * @Apidoc\Returned("page_size", type="number", desc="当前每页条数")
   * @Apidoc\Returned("page_count", type="number", desc="总页数")
   * @Apidoc\Returned("total", type="number", desc="总条数")
   * @Apidoc\Returned("lists", type="array", desc="订单列表")
   * @Apidoc\Returned(ref={Merchant::class})
   */
  public function lists() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        return returnDataListsAdmin(new MerchantLists(),1);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }
  /**
   * @Apidoc\Title("导出")
   * @Apidoc\Desc("导出")
   * @Apidoc\Method("GET")
   * @Apidoc\Url("admin/v1/merchant/inExport")
   * @Apidoc\Query("sn", type="string", require=false, desc="用户编号")
   * @Apidoc\Query("keyword", type="string", require=false, desc="关键字搜索")
   * @Apidoc\Query("debug", type="number", require=false, desc="是否开启沙盒测试 0-否 1-是")
   * @Apidoc\Query("ip_white", type="number", require=false, desc="ip白名单")
   * @Apidoc\Query("disable", type="number", require=true, desc="是否禁用：0-否；1-是；")
   * @Apidoc\Query("start_time", type="string", require=true,  desc="订单查询开始时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("end_time", type="string", require=true, desc="订单查询结束时间,格式:YYYY-MM-DD")
   * @Apidoc\Query("pageIndex", type="number", require=true,default="1", desc="第几页,默认1")
   * @Apidoc\Query("pageSize", type="number", require=true,default="10", desc="一页几条,默认10")
   * @Apidoc\Query("page_type", type="number", require=true,default="1", desc="分页类型：1-一般分页；0-不分页，获取最大所有数据")
   * @Apidoc\Returned("url", type="string", desc="文件url地址")
   */
  public function inExport() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $lists = new MerchantLists();
        $list =$lists->lists();
        $head=[
            ["key"=>"sn","title"=>"商户编号","txt"=>""],
            ["key"=>"nick_name","title"=>"用户昵称","txt"=>""],
            ["key"=>"account","title"=>"账号","txt"=>""],
            ["key"=>"money","title"=>"用户余额","txt"=>""],
            ["key"=>"frozen_capital","title"=>"冻结资金","txt"=>""],
            ["key"=>"disable","title"=>"是否禁用","txt"=>["否","是"]],
            ["key"=>"login_num","title"=>"登录次数","txt"=>[]],
            ["key"=>"login_time","title"=>"最后登录时间","txt"=>[]],
            ["key"=>"login_ip","title"=>"最后登录ip","txt"=>[]],
            ["key"=>"update_by","title"=>"后台修改的用户","txt"=>[]],
            ["key"=>"create_time","title"=>"创建时间","txt"=>[]],
            ["key"=>"update_time","title"=>"更新时间","txt"=>[]],
        ];
        
        $rt=ExcelService::excel($head, $list,"MerchantLists",'商户列表'.$lists->pageNo."_".$lists->pageSize."_".$lists->count());
        $data=["url"=>FileService::getFileUrl($rt)];
        return ajaxReturn(1,'操作成功',$data);
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
    
  }

  /**
   * @Apidoc\Title("修改商户信息")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/merchant/edit")
   * @Apidoc\Param("id", type="string", desc="商户id")
   * @Apidoc\Param("debug", type="int", desc="是否开启沙盒测试 0-否 1-是")
   * @Apidoc\Param("ip_white", type="string", desc="ip白名单")
   * @Apidoc\Param("avatar", type="string", desc="商户头像")
   * @Apidoc\Param("nick_name", type="string", desc="商户昵称")
   * @Apidoc\Param("password", type="string", desc="登录密码")
   * @Apidoc\Param("timezone", type="string", desc="时区")
   * @Apidoc\Param("disable", type="string", desc="是否禁用")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function edit() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $prefix="merchantAdmin";
        $ip=getClientIP();
        $params = (new MerchantValidate())->post()->goCheck('edit');
        addLog($prefix,1,'','');
        addLog($prefix,0,$params,'接收的参数');
        addLog($prefix,0,[$ip,$this -> member_id],'修改人信息');
        if(!empty($params['password'])){
            $params['password'] = encode($params['password']);
        }
        $merchant=Merchant::field(['id','debug','ip_white','password','avatar','disable','nick_name','ip_white','timezone'])->find($params['id']);
        $newdata=isModification($params,$merchant);
        addLog($prefix,0,[$params,$merchant,$newdata],'修改的参数');
        // return messageReturn(1,'操作成功',[$params,$merchant,$newdata]);
        if(array_key_exists('newData',$newdata)){
            $newdata['newData']['update_by']=$this -> member_id;
            $merchant->allowField(['debug','password','avatar','ip_white','disable','nick_name','ip_white','timezone','update_by'])->save($newdata['newData']);
            addLog($prefix,0,[$merchant],'修改完成');
            addLog($prefix,2,'','');
            return messageReturn(1,"操作成功");
        }else{
            addLog($prefix,0,[],'数据相同,未作修改');
            addLog($prefix,2,'','');
            return messageReturn(0,"数据相同,未作修改");
        }
        
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
   return messageReturn(1,'开发中');
  }

  /**
   * @Apidoc\Title("新增商户")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/merchant/add")
   * @Apidoc\Param("nick_name", type="string", desc="商户昵称")
   * @Apidoc\Param("debug", type="int", desc="是否开启沙盒测试 0-否 1-是")
   * @Apidoc\Param("ip_white", type="string", desc="ip白名单")
   * @Apidoc\Param("avatar", type="string", desc="商户头像")
   * @Apidoc\Param("account", type="string", desc="登录账号")
   * @Apidoc\Param("password", type="string", desc="登录密码")
   * @Apidoc\Param("timezone", type="string", desc="时区")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function add() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new MerchantValidate())->post()->goCheck('add');
        # 加密密码
        $enPwd = encode($params['password']);
        $sn=Merchant::createMerchantSn();
        $salt=Merchant::createMerchantSalt();
        $data=[
            "id"=>$sn,
            "sn"=>$sn,
            "nick_name"=>$params['nick_name']??"",
            "avatar"=>$params['avatar']??"",
            "account"=>$params['account']??"",
            "password"=>$enPwd,
            "google_key"=>CreateGoogleAuthKey(),
            "is_google"=>0,
            "debug"=>$params['debug']??0,
            "pay_pwd"=>"",
            "ip_white"=>$params['ip_white']??"",
            "timezone"=>$params['timezone']??8,
            "secret_key"=>"",
            "salt"=>$salt,
            "update_by"=>$this -> member_id
            
        ];
        $model=Merchant::create($data);
        
        $secretKey=Merchant::secretKeyString($sn,$salt);
        $model->save([
            "secret_key"=>$secretKey
        ]);
        
        return messageReturn(1,'操作成功',$model);
    //   return json($User) ->Code(200);
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }
      
      
      
      
      return messageReturn(1,'开发中',$params);
  }

  /**
   * @Apidoc\Title("删除商户")
   * @Apidoc\Desc("开发中")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/merchant/del")
   * @Apidoc\Param("id", type="number", desc="商户ID")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function del() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new MerchantValidate())->post()->goCheck('del');
        $merchant=Merchant::field(['id','debug','password','avatar','disable','nick_name','ip_white','timezone'])->find($params['id']);
        if (empty($merchant)) {
            return messageReturn(300,"商户不存在",$merchant);
        } else {
            Merchant::update(['update_by' => $this -> member_id], ['id' => $merchant['id']]);
            $merchant->delete();
            return messageReturn(1,"操作成功");
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }

  /**
   * @Apidoc\Title("操作余额")
   * @Apidoc\Desc("完成")
   * @Apidoc\Method("POST")
   * @Apidoc\Url("admin/v1/merchant/money")
   * @Apidoc\Param("id", type="number", desc="商户ID")
   * @Apidoc\Param("action", type="number", desc="动作 1-增加 2-减少")
   * @Apidoc\Param("change_amount", type="number", desc="变动数量")
   * @Apidoc\Param("remark", type="string", desc="说明")
   * @Apidoc\Returned("code", type="number", desc="状态码")
   * @Apidoc\Returned("message", type="string", desc="提示内容")
   */
  public function money() {
    # 如果请求头中有携带token
    if ($this -> member_id) {
        $params = (new MerchantValidate())->post()->goCheck('money');
        $merchant=Merchant::field(['id','debug','password','avatar','disable','nick_name','ip_white','timezone'])->find($params['id']);
        if (empty($merchant)) {
            return messageReturn(300,"商户不存在",$merchant);
        } else {
            $change_amount=floatval($params['change_amount']);
            $action=intval($params['action']);
            $koumoney=MchSystemService::MerchantMoney($merchant['id'],1,6,$action,$change_amount,0,$params['remark'],$this -> member_id);
            
            if($koumoney['code']!=200){
                return messageReturn(500,$koumoney['msg'],[$koumoney,$merchant]);
            }
            Merchant::update(['update_by' => $this -> member_id], ['id' => $merchant['id']]);
            return messageReturn(1,"操作成功");
        }
      
    } else {
      # 未登录
      return messageReturn(400,'您当前未登录');
    }  
  }
}