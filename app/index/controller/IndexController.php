<?php

namespace app\index\controller;

use think\facade\Request;

class IndexController 
{

    /**
     * @notes 主页
     * @param string $name
     * @return \think\response\Json|\think\response\View
     * @author 段誉
     * @date 2022/10/27 18:12
     */
    public function index($name = '你好,likeadmin')
    {
        // $template = app()->getRootPath() . 'public/pc/index.html';
        // if (Request::isMobile()) {
        //     $template = app()->getRootPath() . 'public/mobile/index.html';
        // }
        // if (file_exists($template)) {
        //     return view($template);
        // }
        // return JsonService::success($name);
        return "你好,新一";
    }


}