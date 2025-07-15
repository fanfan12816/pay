<?php


declare(strict_types=1);

namespace app\common\service;

use app\common\model\{Merchant,MerchantAccountLog};

class MchSystemService
{

    /**
     * @notes 用户余额变更
     * @param $mch_id  商户id
     * @param $change_object 变动对象;[1=余额,2=备付金]
     * @param $change_type 变动类型;[1=充值,2=提现,3=代付,4=代收,5=兑换6=后台操作]
     * @param $action 动作 1-增加 2-减少
     * @param $change_amount 变动金额
     * @param $source_sn 关联ID
     * @param $remark 备注
     */
    public static function MerchantMoney($mch_id,$change_object, $change_type, $action,$change_amount, $source_sn="",  $remark='',$update_by="0")
    {
        $logPrefix="merchantMoney";
        if(empty($mch_id)||empty($change_object)||empty($change_object)||empty($change_type)||empty($action)||empty($change_amount)){
            self::addLog($logPrefix,1,"","",$mch_id);
            self::addLog($logPrefix,0,[$mch_id,$change_object,$change_type,$action,$change_amount,$source_sn, $remark],"参数不存在",$mch_id);
            self::addLog($logPrefix,2,"","",$mch_id);
            return ["code"=>0,"msg"=>"参数不存在"];
        }
        self::addLog($logPrefix,1,"","",$mch_id);
        self::addLog($logPrefix,0,[$mch_id,$change_object,$change_type,$action,$change_amount,$source_sn, $remark],"参数",$mch_id);
        $User = Merchant::where(['id' => $mch_id])->lock(true)->findOrEmpty();
        self::addLog($logPrefix,0,$User,"用户信息",$mch_id);
        $left_amount=0;
        if($change_object==1){
            $left_amount=$User->money;
        }elseif($change_object==2){
            $left_amount=$User->reserve_money;
        }else{
            self::addLog($logPrefix,0,$change_object,"变更对象不正确",$mch_id);
            self::addLog($logPrefix,2,"","",$mch_id);
            return ["code"=>0,"msg"=>"变更对象不正确"];
            return false;
        }
        $left_amount=floatval($left_amount);
        self::addLog($logPrefix,0,[$left_amount],"变更前余额",$mch_id);
        $change_amount=floatval($change_amount);
        self::addLog($logPrefix,0,[$change_amount],"变更余额",$mch_id);
        if($change_amount<=0){
            self::addLog($logPrefix,0,$change_amount,"变更余额小于或等于0",$mch_id);
            self::addLog($logPrefix,2,"","",$mch_id);
            return ["code"=>0,"msg"=>"变更余额小于或等于0"];
            return false;
        }
        // 余额增加
        if($action==1){
            $right_amount=0;
            if($change_object==1){
                 $User -> money += $change_amount;
                 $right_amount=$User -> money;
            }elseif($change_object==2){
                 $User -> reserve_money += $change_amount;
                 $right_amount=$User -> reserve_money;
            }
            $User->save();
            self::addLog($logPrefix,0,$right_amount,"变更后余额",$mch_id);
            $logModel=self::AccountLog($mch_id,$change_object,$change_type,$action,$left_amount,$change_amount,$right_amount, $source_sn, $remark,$update_by);
            self::addLog($logPrefix,0,$logModel,"新增流水返回信息",$mch_id);
            self::addLog($logPrefix,2,"","",$mch_id);
            return ["code"=>200,"msg"=>"增加余额成功"];
            return true;
        }
        
        // 余额减少
        if($action==2){
            if($left_amount<$change_amount){
                self::addLog($logPrefix,0,[$left_amount,$change_amount],"金额不足扣除",$mch_id);
                self::addLog($logPrefix,2,"","",$mch_id);
                return ["code"=>0,"msg"=>"金额不足扣除"];
                return false;
            }
            if($change_type!=2){
                if(($left_amount-$User -> frozen_capital)<$change_amount){
                    self::addLog($logPrefix,0,[$left_amount,$change_amount],"有资金被冻结,剩余资金不够扣除",$mch_id);
                    self::addLog($logPrefix,2,"","",$mch_id);
                    return ["code"=>0,"msg"=>"有资金被冻结,剩余资金不够扣除"];
                    return false;
                }
            }
            
            if($change_object==1){
                 $User -> money -= $change_amount;
                 $right_amount=$User -> money;
            }elseif($change_object==2){
                 $User -> reserve_money -= $change_amount;
                 $right_amount=$User -> reserve_money;
            }
            $User->save();
            self::addLog($logPrefix,0,$right_amount,"变更后余额",$mch_id);
            $logModel=self::AccountLog($mch_id,$change_object,$change_type,$action,$left_amount,$change_amount,$right_amount, $source_sn, $remark,$update_by);
            self::addLog($logPrefix,0,$logModel,"新增流水返回信息",$mch_id);
            self::addLog($logPrefix,2,"","",$mch_id);
            return ["code"=>200,"msg"=>"扣除余额成功"];
            return true;
        }
        return ["code"=>0,"msg"=>"未匹配到规则"];
        return false;
        
        
    }
    
    /**
     * @notes 日志重写功能
     * @param $prefix文件类型
     * @param string $start 0 正常记录,1开始,2结束
     * @param null $data
     * @param null  $tt 标题
     * @return array|int|mixed|string
     */
    public static function AccountLog($mch_id,$change_object,$change_type,$action,$left_amount,$change_amount,$right_amount,$source_sn="", $remark='',$update_by="0")
    {
        $order_sn=generate_sn(MerchantAccountLog::class, 'order_sn',"LS");
        $ip=getClientIP();
        $model=MerchantAccountLog::create([
            'order_sn' => $order_sn,
            'mch_id' => $mch_id,
            'change_object' => $change_object,
            'change_type' => $change_type,
            'action' => $action,
            'left_amount' => $left_amount,
            'change_amount' => $change_amount,
            'right_amount' => $right_amount,
            'source_sn' => $source_sn,
            'ip' => $ip,
            'remark' => $remark,
            'update_time' => time(),
            'update_by' => $update_by,
        ]);
        return $model;
    }
    /**
     * @notes 日志重写功能
     * @param $prefix文件类型
     * @param string $start 0 正常记录,1开始,2结束
     * @param null $data
     * @param null  $tt 标题
     * @return array|int|mixed|string
     */
    public static function addLog($prefix="", $start = 0,$data= '',$tt="",$mch="")
    {
        $t = date("Ymd",time());
        $shi = date("H",time());
        $day = date("Y-m-d H:i:s",time());
        if(empty($prefix)){
            $prefix="system";
        }
        if(is_array($data)){
            $data=json_encode($data,JSON_UNESCAPED_UNICODE);
        }
        $t=$t.'/'.$prefix;
        if(!empty($mch)){
            $t=$t.'/'.$mch;
        };
        $dir=iconv("UTF-8", "GBK", app()->getRootPath().'runtime'. '/' .'customLog'. '/' .$t);
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
        
        // 创建文件
        $file = fopen($dir."/{$shi}.log","a+");
        
        if($start==1){
            fwrite($file,"\n");
            fwrite($file,"\n");
        }
        fwrite($file,"╔========================[$day]========================╗\n");
        if($start==1){
            fwrite($file,"|                               ".($tt?$tt:"日志开始")."\n");
        }
        if($start==2){
            fwrite($file,"|                               ".($tt?$tt:"日志结束")."\n");
        }
        if(!empty($tt)){
            fwrite($file,"   ┌---------------------------------------------------------------┑\n");
            fwrite($file,"   |                              {$tt}\n");
            fwrite($file,"   ┗---------------------------------------------------------------┛\n");
        }
        if(!empty($data)){
            fwrite($file,$data."\n\n");
        }
        fwrite($file,"╚=====================================================================╝\n");
        
    }
}