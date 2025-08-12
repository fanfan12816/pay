<?php


namespace app\common\model;

use app\common\model\BaseModel;
use app\common\service\FileService;
use think\model\concern\SoftDelete;

/**
 * 商户管理模型
 * Class Merchant
 */
class Merchant extends BaseModel
{
    use SoftDelete;
    // protected $table = 'order_merchant';ip_white
    protected $deleteTime = 'delete_time';

     

   /**
     * @notes 头像获取器 - 用于头像地址拼接域名
     * @param $value
     * @return string
     */
    public function getAvatarAttr($value)
    {
        return trim($value) ? FileService::getFileUrl($value) : '';
    }
    
    /**
     * @notes 清除头像域名
     * @param $value
     * @param $data
     * @return array|string|string[]
     */
    public function setAvatarAttr($value)
    {
        return trim($value) ? FileService::setFileUrl($value) : '';
    }
    /**
     * @notes 生成商户编码
     * @param string $prefix
     * @param int $length
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function createMerchantSn($prefix = '', $length = 6)
    {
        $rand_str = '';
        for ($i = 0; $i < $length; $i++) {
            $rand_str .= mt_rand(1, 9);
        }
        $sn = $prefix . $rand_str;
        if (Merchant::where(['sn' => $sn])->find()) {
            return self::createMerchantSn($prefix, $length);
        }
        return $sn;
    }
    /**
     * @notes 生成商户密钥盐
     * @param string $prefix
     * @param int $length
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function createMerchantSalt($prefix = '', $length = 8)
    {
        $rand_str = '';
        for ($i = 0; $i < $length; $i++) {
            $rand_str .= mt_rand(1, 9);
        }
        $sn = $prefix . $rand_str;
        if (Merchant::where(['salt' => $sn])->find()) {
            return self::createMerchantSalt($prefix, $length);
        }
        return $sn;
    }
    /**
     * @notes 获取商户密钥
     * @param string $prefix
     * @param int $length
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function secretKeyString($id,$salt="")
    {
        if(!empty($id)){
            $user = Merchant::where(['id' => $id])->findOrEmpty();
            if ($user->isEmpty()) {
                return false;
            }
            $plaintext=$user->id .$user->sn . $user->account;
            if(empty($salt)){
                $salt=$user->salt;
            }
            
            return strtoupper(create_password($plaintext,$salt));
        }else{
            return false;
        }
    }
}