<?php


namespace app\mch\lists;


use app\common\lists\BaseDataLists;

/**
 * 管理员模块数据列表基类
 * Class BaseAdminDataLists
 * @package app\adminapi\lists
 */
abstract class BaseMchDataLists extends BaseDataLists
{
    protected $account;
    protected $mchid;
    protected $timezone;

    public function __construct()
    {
        parent::__construct();
        // 截取Bearer 前戳
        $AuthToken = str_ireplace('Bearer ', '', $this -> request -> header('Authorization'));
        // 解密
        $payload = decode($AuthToken);
        // 用户ID
        $this -> mchid = $payload -> member_id;
        // 用户名
        $this -> account = $payload -> member_username;
        @$this -> timezone = $payload ->timezone ?:8 ;
    }


}