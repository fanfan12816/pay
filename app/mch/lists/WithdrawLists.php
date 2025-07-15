<?php

namespace app\mch\lists;

use app\mch\lists\BaseMchDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsSortInterface;
use app\common\model\{Merchant,MerchantWithdrawOrder};

/**
 * 提现列表
 * Class WithdrawLists
 */
class WithdrawLists extends BaseMchDataLists implements ListsSearchInterface, ListsSortInterface
{

    /**
     * @notes  设置搜索条件
     * @return array
     * @author heshihu
     * @date 2022/2/8 18:39
     */
    public function setSearch(): array
    {
        return [
            '=' => ['order_sn','status']
        ];
    }

    /**
     * @notes  设置支持排序字段
     * @return array
     * @author heshihu
     * @date 2022/2/9 15:11
     */
    public function setSortFields(): array
    {
        return ['id' => 'desc','create_time' => 'desc'];
    }

    /**
     * @notes  设置默认排序
     * @return array
     * @author heshihu
     * @date 2022/2/9 15:08
     */
    public function setDefaultOrder(): array
    {
        return ['order_sn' => 'desc', 'id' => 'desc'];
    }

    /**
     * @notes 自定查询条件
     * @return array
     */
    public function queryWhere()
    {
        $where[] = ['type', '=', 1];
        $where[] = ['mch_id', '=', $this->mchid];
        if(!empty($this->params['start_time'])&&!empty($this->params['end_time'])){
            // $start_time=strtotime($this->params['start_time']);
            // $end_time=strtotime($this->params['end_time']);
            $start_time=diyTimestamp(strtotime($this->params['start_time']),$this->timezone,true);;
            $end_time=diyTimestamp(strtotime($this->params['end_time']),$this->timezone,true);;
            $where[] = ['create_time', '>=', $start_time];
            $where[] = ['create_time', '<=', $end_time];
        }
        return $where;
    }
    /**
     * @notes  获取管理列表
     * @return array
     */
    public function lists(): array
    {
        $field = '*';
        $lists = MerchantWithdrawOrder::field($field)
            ->where($this->searchWhere)
            ->where($this->queryWhere())
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sortOrder)
            ->select()
            ->toArray();
        
        // $lists[]=MerchantWithdrawOrder::getLastSql();
        foreach ($lists as &$item) {
            $item["create_time"]=diyTimestamp($item["create_time"],$this->timezone);
            $item["update_time"]=diyTimestamp($item["update_time"],$this->timezone);
        }
        return $lists;
    }

    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        return MerchantWithdrawOrder::where($this->searchWhere)->where($this->queryWhere())->count();
    }

}