<?php

namespace app\admin\lists\channel;

use app\admin\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsSortInterface;
use app\common\model\{Merchant,ChannelBank,MerchantChannel,Channel};
use app\model\AdminMember;
/**
 * 列表
 * Class WithdrawLists
 */
class ChannelLists extends BaseAdminDataLists implements ListsSearchInterface, ListsSortInterface
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
        $where = [];
        if (!empty($this->params['keyword'])) {
            $where[] = ['name|desc|remark|extra', 'like', '%' . $this->params['keyword'] . '%'];
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
        $lists = Channel::field($field)
            ->where($this->searchWhere)
            ->where($this->queryWhere())
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sortOrder)
            ->select()
            ->toArray();
            
        $page_type=input("page_type",1);
        // $lists[]=MerchantChannel::getLastSql();
        foreach ($lists as &$item) {
            $item["create_time"]=diyTimestamp($item["create_time"]);
            $item["update_time"]=diyTimestamp($item["update_time"]);
            if($item['update_by']>0){
                $admin=AdminMember::where(["member_id"=>$item['update_by']])->field("member_nickname")->findOrEmpty();
                $item['update_by']=$admin['member_nickname'];
            }
            if($page_type==0){
                $name=$item['name'];
                @$item['name']=$name."(".$item['id'].")";
            }
        }
        return $lists;
    }

    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        return Channel::where($this->searchWhere)->where($this->queryWhere())->count();
    }

}