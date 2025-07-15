<?php

namespace app\mch\lists;

use app\mch\lists\BaseMchDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsSortInterface;
use app\common\model\{Merchant,PayoutOrder,Channel,ChannelBank};

/**
 * 代付列表
 * Class PayinLists
 */
class TestPayoutLists extends BaseMchDataLists implements ListsSearchInterface, ListsSortInterface
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
            '=' => [ 'channel_id','pay_type','status','order_sn','mch_sn']
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
        return ['id' => 'desc','create_time' => 'desc'];
    }
     /**
     * @notes 自定查询条件
     * @return array
     */
    public function queryWhere()
    {
        $where[] = ['mch_id', '=', $this->mchid];
        $where[] = ['type', '=',3];
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
        $lists = PayoutOrder::field($field)
            ->with(['channel','member'])
            ->where($this->searchWhere)
            ->where($this->queryWhere())
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sortOrder)
            ->select()
            ->toArray();
            
        foreach ($lists as &$item) {
            $item["create_time"]=diyTimestamp($item["create_time"],$this->timezone);
            $item["update_time"]=diyTimestamp($item["update_time"],$this->timezone);
            $item["status_time"]=diyTimestamp($item["status_time"],$this->timezone);
            $item["request_time"]=diyTimestamp($item["request_time"],$this->timezone);
            $item["expire_time"]=diyTimestamp($item["expire_time"],$this->timezone);
            @$item['channel_title']=$item['channel']['name'];
            @$item['mch_nick_name']=$item['merchant']['nick_name'];
            if($item['update_by']>0){
                @$item['update_by']=$item['member']['member_nickname'];
            }
            
            
        }
        // $lists[]=PayoutOrder::getLastSql();
        
        return $lists;
    }

    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        return PayoutOrder::where($this->searchWhere)->where($this->queryWhere())->count();
    }

}