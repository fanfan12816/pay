<?php

namespace app\mch\lists;

use app\mch\lists\BaseMchDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsSortInterface;
use app\common\model\{Merchant,ChannelBank,MerchantChannel,Channel};

/**
 * 流水列表
 * Class WithdrawLists
 */
class PayinBankLists extends BaseMchDataLists implements ListsSearchInterface, ListsSortInterface
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
            '=' => ['channel_id']
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
        return ['create_time' => 'create_time', 'id' => 'id'];
    }

    /**
     * @notes  设置默认排序
     * @return array
     * @author heshihu
     * @date 2022/2/9 15:08
     */
    public function setDefaultOrder(): array
    {
        return ['sort' => 'desc', 'id' => 'desc'];
    }

    /**
     * @notes 自定查询条件
     * @return array
     */
    public function queryWhere()
    {
        $where[] = ['pay_type', '=', 0];
        $where[] = ['status', '=', 1];
        if (!empty($this->params['keyword'])) {
            $where[] = ['bank_name|user_name', 'like', '%' . $this->params['keyword'] . '%'];
        }
        if (!empty($this->params['type'])) {
            $where[] = ['type', '=',$this->params['type']];
        }else{
            $where[] = ['type', '=',1];
        }
        if (!empty($this->params['money'])) {
            $where[] = ['min', '<=',$this->params['money']];
            $where[] = ['max', '>=',$this->params['money']];
        }else{
            $where[] = ['min', '=',-100];
        }
        if(!empty($this->params['start_time'])&&!empty($this->params['end_time'])){
            $start_time=strtotime($this->params['start_time']);
            $end_time=strtotime($this->params['end_time']);
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
        $lists = ChannelBank::field($field)
            ->where($this->searchWhere)
            ->where($this->queryWhere())
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sortOrder)
            ->select()
            ->toArray();
            
        // $lists[]=ChannelBank::getLastSql();
        // return $lists;
        foreach ($lists as &$item) {
            $item["create_time"]=diyTimestamp($item["create_time"],$this->timezone);
            $item["update_time"]=diyTimestamp($item["update_time"],$this->timezone);
            $channel=Channel::where(["id"=>$item['channel_id']])->field("name")->findOrEmpty();
            $item['channel_title']=$channel['name'];
        }
        return $lists;
    }

    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        return ChannelBank::where($this->searchWhere)->where($this->queryWhere())->count();
    }

}