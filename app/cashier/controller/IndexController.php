<?php

namespace app\cashier\controller;

use app\common\model\{PayinOrder};
use app\BaseController;

class IndexController extends BaseController {

  public function index() {
    $id=input("id","");
    if(empty($id)){
        echo "订单号有误";
        return;
    }
    $Model=PayinOrder::where(['order_sn' => $id])->findOrEmpty();
    $statusList=["待付款","确认中","审核成功","审核失败","超时关闭","手动关闭"];
    if($Model->isEmpty()){
        echo "订单不存在";
        return;
    }else{
        echo '<head>
                <meta charset="UTF-8">
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <meta name="renderer" content="webkit">
                <meta name="force-rendering" content="webkit">
                <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,minimum-scale=1,user-scalable=no,viewport-fit=cover">
                <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
                <meta http-equiv="pragma" content="no-cache">
                <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
                <meta http-equiv="Cache" content="no-cache"><meta http-equiv="expires" content="-1">
                <meta name="format-detection" content="telephone=no">
                <meta name="wap-font-scale" content="no">
                <meta name="theme-color" content="#000000">
                <meta name="mobile-web-app-capable" content="yes">
                <meta name="app-mobile-web-app-capable" content="yes">
                <meta name="apple-mobile-web-app-status-bar-style" content="default"><meta name="screen-orientation" content="portrait">
                <meta name="x5-orientation" content="portrait">
                <title>订单详情</title>
                <meta name="description" content="AA">
              </head>';
        echo '<div style="font-size:15px;">';
        echo '商户号：<code>'.$Model->mch_id.'</code><br/>';
        echo '通道号：<code>'.$Model->channel_id.'</code><br/>';
        echo '平台订单号：<code>'.$Model->order_sn.'</code><br/>';
        echo '商户订单号：<code>'.$Model->mch_sn.'</code><br/>';
        echo '订单金额：<code>'.$Model->amount.'</code><br/>';
        echo '订单手续费：<code>'.$Model->service_charge.'</code><br/>';
        echo '订单状态：<code>'.$statusList[$Model->status].'</code><br/>';
        echo '状态变化时间：<code>'.date("Y-m-d H:i:s",$Model->status_time).'</code><br/>';
        echo '订单创建时间：<code>'.date("Y-m-d H:i:s",$Model->create_time).'</code><br/>';
        if(!empty($Model->image)){
            echo '凭证哈希值：<code>'.$Model->imgmd5.'</code><br/>';
            echo '凭证图片：<br/><img width="100%" class="ant-image-img" src="'.$Model->image.'"><br/>';
        }
        echo '</div>';
        return;
        return ajaxReturn(200,'success',$Model);
    }
  }

}