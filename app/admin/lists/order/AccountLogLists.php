<?php

namespace app\admin\lists\order;

use app\admin\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsSortInterface;
use app\common\lists\ListsExtendInterface;
use app\model\AdminMember;
use app\common\model\{Merchant,MerchantAccountLog};

/**
 * 流水列表
 * Class WithdrawLists
 */
class AccountLogLists extends BaseAdminDataLists implements ListsSearchInterface, ListsSortInterface,ListsExtendInterface
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
            '=' => ['order_sn', 'change_object','change_type','action','source_sn','mch_id']
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
        $where=[];
        if (!empty($this->params['keyword'])) {
            $where[] = ['remark', 'like', '%' . $this->params['keyword'] . '%'];
        }
        if(!empty($this->params['start_time'])&&!empty($this->params['end_time'])){
            $start_time=strtotime($this->params['start_time']);
            $end_time=strtotime($this->params['end_time']);
            $where[] = ['create_time', '>=', $start_time];
            $where[] = ['create_time', '<=', $end_time];
            // $where[] = ['create_time', '>=', 1745856000];
            // $where[] = ['create_time', '<=', 1751299200];
            // $this->limitOffset=0;
            // $this->limitLength=5700;
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
        $lists = MerchantAccountLog::field($field)
            ->with(['merchant','member'])
            ->where($this->searchWhere)
            ->where($this->queryWhere())
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sortOrder)
            ->select()
            ->toArray();
            // $sql=MerchantAccountLog::getLastSql();
        foreach ($lists as &$item) {
            $item["create_time"]=diyTimestamp($item["create_time"]);
            $item["update_time"]=diyTimestamp($item["update_time"]);
            @$item['mch_nick_name']=$item['merchant']['nick_name'];
            if($item['update_by']>0){
                @$item['update_by']=$item['member']['member_nickname'];
            }else{
                $item['update_by']="系统操作";
            }
            unset($item['merchant']);
            unset($item['member']);
            // $item['sql']=MerchantAccountLog::getLastSql();
        }    
        // $lists[]=$sql;
        return $lists;
    }

    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        return MerchantAccountLog::where($this->searchWhere)->where($this->queryWhere())->count();
    }


    public function extend()
    {
        $field=[
            'sum(change_amount) as amount'
            ];
        $sum=(new MerchantAccountLog())->field($field)
            ->where($this->searchWhere)
            ->where($this->queryWhere())
            ->select()
            ->toArray();
        return $sum[0]??[];
    }
}