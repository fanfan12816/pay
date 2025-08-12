<?php

namespace app\admin\lists\merchant;

use app\admin\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsSortInterface;
use app\common\model\{Merchant};
use app\model\AdminMember;
/**
 * 列表
 * Class WithdrawLists
 */
class MerchantLists extends BaseAdminDataLists implements ListsSearchInterface, ListsSortInterface
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
            '=' => ['sn','disable','debug']
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
        return [ 'create_time' => 'desc'];
    }

    /**
     * @notes 自定查询条件
     * @return array
     */
    public function queryWhere()
    {
        $where=[];
        if (!empty($this->params['keyword'])) {
            $where[] = ['nick_name|account|sn', 'like', '%' . $this->params['keyword'] . '%'];
        }
        if (!empty($this->params['ip_white'])) {
            $where[] = ['ip_white', 'like', '%' . $this->params['ip_white'] . '%'];
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
        $lists = Merchant::field($field)
            ->where($this->searchWhere)
            ->where($this->queryWhere())
            ->limit($this->limitOffset, $this->limitLength)
            ->hidden(["password","salt"])
            ->order($this->sortOrder)
            ->select()
            ->toArray();
        $page_type=input("page_type",1);    
        // $lists[]=MerchantChannel::getLastSql();
        foreach ($lists as &$item) {
            $item["create_time"]=diyTimestamp($item["create_time"],+8);
            $item["login_time"]=diyTimestamp($item["login_time"],+8);
            $item["update_time"]=diyTimestamp($item["update_time"],+8);
            // $item['sql']=MerchantChannel::getLastSql();
            if($item['update_by']>0){
                $admin=AdminMember::where(["member_id"=>$item['update_by']])->field("member_nickname")->findOrEmpty();
                $item['update_by']=$admin['member_nickname'];
            }
            if($page_type==0){
                $name=$item['nick_name'];
                @$item['nick_name']=$name."(".$item['id'].")";
            }
        }
         if($page_type==0){
            array_unshift($lists,["id"=>0,"nick_name"=>"管理平台"]);
        }
        return $lists;
    }

    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        return Merchant::where($this->searchWhere)->where($this->queryWhere())->count();
    }

}