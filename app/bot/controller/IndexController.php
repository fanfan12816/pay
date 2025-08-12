<?php

namespace app\bot\controller;


use app\common\service\bot\BotService;
use think\response\Json;


/**
 * index
 * Class IndexController
 * @package app\bot\controller
 */
class IndexController extends BaseBotController
{

    /**
     * @notes 首页数据
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 段誉
     * @date 2022/9/21 19:15
     */
    public function index()
    {
        $type = $this->request->get('type/s', 'getWebhook');
        // $type = $this->request->all();
        // var_dump($type);
        // return ;
        // $result = BotService::getMe(1);
        // $result = BotService::getChat(1,"-1002127383429");
        // $result = BotService::deleteForumTopic(1,"-1002127383429",23);
        // $result = BotService::botStatus(1,$type);
        $result =(new BotService())->webhook($type);
        // $result = BotService::telegramFun("getChat",["chat_id"=>"boluo128"]);
        // var_dump($result);
        return ajaxReturn(200,'测试',$result);
    }

}