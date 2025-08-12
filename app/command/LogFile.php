<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\service\bot\{BotService};
use app\common\service\{ConfigService};


class LogFile extends Command
{
    protected function configure()
    {
        $this->setName('log_file')
            ->setDescription('日志定时删除');
    }


    protected function execute(Input $input, Output $output)
    {
        $prefix="delFile";
        BotService::addLog($prefix,"日志定时删除日志开始","","start");
        $delDay=ConfigService::get('log_file_del',"5");
        BotService::addLog($prefix,"数据只保留多少天",$delDay);
        try {
            $output -> writeln('开始处理');
            $output -> writeln(date("Y-m-d H:i:s"));
            $logPath=substr(str_replace('\\','/',dirname(__FILE__)),0,stripos(str_replace('\\','/',dirname(__FILE__)),'/app'));
            $dtTmp = date('Ymd');
            
            $delDir=$logPath."/runtime/customLog/".date("Ymd",strtotime("-$delDay days"));
            BotService::addLog($prefix,"判断自定义日志目录是否存在",$delDir);
            if(is_dir($delDir)){
                BotService::addLog($prefix,"自定义日志目录存在,开始执行删除",$delDir);
                $rt=$this->deldirFun($delDir);
                BotService::addLog($prefix,"删除自定义日志目录执行成功",[$rt]);
            }else{
                BotService::addLog($prefix,"自定义日志目录不存在不用删除","");
            }
            // 判断日志目录
            $logDelDir=$logPath."/runtime/telegram/log/".date("Ymd",strtotime("-$delDay days"));
            BotService::addLog($prefix,"判断飞机日志目录是否存在",$logDelDir);
            if(is_dir($logDelDir)){
                BotService::addLog($prefix,"飞机日志目录存在,开始执行删除",$logDelDir);
                $rt=$this->deldirFun($logDelDir);
                BotService::addLog($prefix,"删除飞机日志目录执行成功",[$rt]);
            }else{
                BotService::addLog($prefix,"飞机日志目录不存在不用删除","");
            }
            BotService::addLog($prefix,"程序执行结束","","end");
            
            
            $output -> writeln('处理结束');
            return true;
        } catch (\Exception $e) {
            BotService::addLog($prefix,"处理失", $e->getMessage(),"end");
            $output -> writeln('处理失败,失败原因:' . $e->getMessage());
            // Log::write('订单退款状态查询失败,失败原因:' . $e->getMessage());
            return false;
        }
    }


    /**
     * @notes 删除文件
     */
    public function deldirFun($dir) {
      //先删除目录下的文件：
      $dh=opendir($dir);
      while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
          $fullpath=$dir."/".$file;
          if(!is_dir($fullpath)) {
              unlink($fullpath);
          } else {
              $this->deldirFun($fullpath);
          }
        }
      }
     
      closedir($dh);
      //删除当前文件夹：
      if(rmdir($dir)) {
        return true;
      } else {
        return false;
      }
    }

}