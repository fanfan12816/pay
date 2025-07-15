<?php

namespace app\bot\controller;


use think\App;

class BaseBotModeController
{
    protected string $tobot = "";

    public function __construct()
    {
        $this->tobot = $_REQUEST['bot']??"";
    }
    
}