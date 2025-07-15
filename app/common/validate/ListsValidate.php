<?php


namespace app\common\validate;

/**
 * 列表参数验证
 * Class ListsValidate
 * @package app\common\validate
 */
class ListsValidate extends BaseValidate
{
    protected $rule = [
        'page_no' => 'integer|gt:0',
        'page_size' => 'integer|gt:0|pageSizeMax',
        'page_start' => 'integer|gt:0',
        'page_end' => 'integer|gt:0|egt:page_start',
        'page_type' => 'in:0,1',
        'order_by' => 'in:desc,asc',
        'start_time' => 'date',
        'end_time' => 'date|gt:start_time',
        'start' => 'number',
        'end' => 'number',
        'export' => 'in:0,1',
    ];

    protected $message = [
        'page_end.egt' => '导出范围设置不正确，请重新选择',
        'end_time.gt' => '搜索的时间范围不正确',
    ];

    /**
     * @notes 查询数据量判断
     * @param $value
     * @param $rule
     * @param $data
     * @return bool
     */
    public function pageSizeMax($value, $rule, $data)
    {
        $pageSizeMax = 2500;
        if ($pageSizeMax < $value) {
            return '已超出系统限制数量，请分页查询或导出，' . '当前最多记录数为：' . $pageSizeMax;
        }
        return true;
    }


}