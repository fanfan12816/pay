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
class ChannelLists extends BaseMchDataLists implements ListsSearchInterface, ListsSortInterface
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
            '=' => ['source','status','out_status','in_status']
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
        return [ 'id' => 'desc'];
    }

    /**
     * @notes 自定查询条件
     * @return array
     */
    public function queryWhere()
    {
        $where[] = ['mch_id', '=', $this->mchid];
        if(empty($this->params['status'])){
            $where[] = ['status', '=', 1];
        }
        if (!empty($this->params['channel_name'])) {
            $idlist=[];
            $cl=Channel::field('*')
            ->where([['name', 'like', '%' . $this->params['channel_name'] . '%']])
            ->select()
            ->toArray();
            foreach($cl as $v){
                $idlist[] = $v['id'];
            }
            $where[] = ['channel_id', 'in', $idlist];
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
        $lists = MerchantChannel::field($field)
            ->where($this->searchWhere)
            ->where($this->queryWhere())
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sortOrder)
            ->select()
            ->toArray();
            
        // $lists[]=MerchantChannel::getLastSql();
        foreach ($lists as &$item) {
            $item["create_time"]=diyTimestamp($item["create_time"],$this->timezone);
            $item["update_time"]=diyTimestamp($item["update_time"],$this->timezone);
            $channel=Channel::where(["id"=>$item['channel_id']])->field("name")->findOrEmpty();
            $item['channel_title']=$channel['name'];
            // $item['sql']=MerchantChannel::getLastSql();
        }
        return $lists;
    }

    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        return MerchantChannel::where($this->searchWhere)->where($this->queryWhere())->count();
    }

}