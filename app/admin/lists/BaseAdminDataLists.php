<?php


namespace app\admin\lists;


use app\common\lists\BaseDataLists;

/**
 * 管理员模块数据列表基类
 * Class BaseAdminDataLists
 * @package app\adminapi\lists
 */
abstract class BaseAdminDataLists extends BaseDataLists
{
    protected $member_username;
    protected $member_id;
    protected $timezone;

    public function __construct()
    {
        parent::__construct();
        // 截取Bearer 前戳
        $AuthToken = str_ireplace('Bearer ', '', $this -> request -> header('Authorization'));
        // 解密
        $payload = decode($AuthToken);
        
          // 用户ID
          $this -> member_id = $payload -> member_id;
    
          // 手机号
          $this -> member_username = $payload -> member_username;
    }


}