<?php

namespace app\admin\lists\system;

use app\admin\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsSortInterface;
use app\model\AdminMember;
use app\common\model\{Merchant,BotGroup,Channel,ChannelBank};



/**
 * 机器人群列表
 * Class PayinLists
 */
class BotGroupLists extends BaseAdminDataLists implements ListsSearchInterface, ListsSortInterface
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
            '=' => ['mch_id','channel_id','bank_id','recipient', 'scene_id']
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
        return ['create_time' => 'desc', 'id' => 'desc'];
    }
     /**
     * @notes 自定查询条件
     * @return array
     */
    public function queryWhere()
    {
       $where = [];
        if (!empty($this->params['keyword'])) {
            $idlist=[];
            $cl=ChannelBank::field('*')
            ->where([['bank_name|user_name|bank_num|desc|remark|extra', 'like', '%' . $this->params['keyword'] . '%']])
            ->select()
            ->toArray();
            foreach($cl as $v){
                $idlist[] = $v['id'];
            }
            $where[] = ['bank_id', 'in', $idlist];
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
        $lists = BotGroup::field($field)
            ->where($this->searchWhere)
            ->where($this->queryWhere())
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sortOrder)
            ->select()
            ->toArray();
            
        foreach ($lists as &$item) {
            $item["create_time"]=diyTimestamp($item["create_time"]);
            $item["update_time"]=diyTimestamp($item["update_time"]);
            $channel=Channel::where(["id"=>$item['channel_id']])->field("name")->findOrEmpty();
            $item['channel_title']=$channel['name'];
            $item['mch_nick_name']="";
            if($item['mch_id']>0){
                $merchant=Merchant::where(["id"=>$item['mch_id']])->field("nick_name")->findOrEmpty();
                $item['mch_nick_name']=$merchant['nick_name'];
            }
            if($item['update_by']>0){
                $admin=AdminMember::where(["member_id"=>$item['update_by']])->field("member_nickname")->findOrEmpty();
                $item['update_by']=$admin['member_nickname'];
            }
            $extra=[];
            foreach ($item['extra'] as $k => $v){
                $v['key']=$k;
                $extra[]=$v;
            }
            $item['extra']=$extra;
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
        return BotGroup::where($this->searchWhere)->where($this->queryWhere())->count();
    }

}