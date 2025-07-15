<?php


declare(strict_types=1);

namespace app\common\service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExcelService
{
    /**
     * 导出Excel表格 Xlsx格式(2007版)
     *
     * @author liang <23426945@qq.com>
     * @datetime 2019-12-22
     * 
     * @param  array  $title    表头单元格内容
     * @param  array  $data     从第二行开始写入的数据
     * @param  string $path 	Excel文件保存位置,路径中的目录必须存在
     *
     * @return null 没有设定返回值
     */
    public static function excel($head = [], $list = [], $path = '',$fname='')
    {
        $title=[];
        $data=[];
        foreach ($head as $f){
            $title[]=$f['title'];
        }
        foreach($list as $v){
            $dt=[];
            foreach ($head as $f){
                if(!empty($f["txt"])){
                    $dt[]=$f["txt"][$v[$f['key']]];
                }else{
                    $dt[]=$v[$f['key']];
                }
            }
            $data[]=$dt;
        }
        $t = date("Ymd",time());
        $dir=iconv("UTF-8", "GBK", app()->getRootPath().'public'. '/' .'excel'.  '/' .$path.'/' .$t);
        $file_name=date("YmdHis",time()).'_'.$fname. '.xlsx';
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
    	// 获取Spreadsheet对象
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
    	// 表头单元格内容 第一行
    	$titCol = 'A';
        foreach ($title as $value) {
            // 单元格内容写入
            $sheet->setCellValue($titCol . '1', $value);
            $titCol++;
        }	
    
    
        // 从第二行开始写入数据
        $row = 2;
        foreach ($data as $item) {
            $dataCol = 'A';
            foreach ($item as $value) {
                // 单元格内容写入
                $sheet->setCellValue($dataCol . $row, $value);
                $dataCol++;
            }
            $row++;
        }
     
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    
        $result = $writer->save($dir . '/'.$file_name);
        return ('/' .'excel'.  '/' .$path.'/' .$t. '/'.$file_name);
    }
}