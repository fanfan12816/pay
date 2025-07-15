<?php

namespace app\admin\lists\order;

use app\admin\lists\BaseAdminDataLists;
use app\common\lists\ListsSearchInterface;
use app\common\lists\ListsSortInterface;
use app\common\model\{Merchant,PayinOrder,Channel,ChannelBank};
use app\model\AdminMember;
use app\common\service\ConfigService;
/**
 * 代收列表
 * Class PayinLists
 */
class PayinLists extends BaseAdminDataLists implements ListsSearchInterface, ListsSortInterface
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
            '=' => ['mch_id','type', 'channel_id','pay_type','status','order_sn','mch_sn']
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
     * @notes 自定查询条件 payer_name	
     * @return array
     */
    public function queryWhere()
    {
        $where=[];
        if (!empty($this->params['payer_name'])) {
            $where[] = ['payer_name', 'like', '%' . $this->params['payer_name'] . '%'];
        }
        if(!empty($this->params['start_time'])&&!empty($this->params['end_time'])){
            $start_time=strtotime($this->params['start_time']);
            $end_time=strtotime($this->params['end_time']);
            $where[] = ['create_time', '>=', $start_time];
            $where[] = ['create_time', '<=', $end_time];
            // $where[] = ['create_time', '>=', 1745856000];
            // $where[] = ['create_time', '<=', 1751299200];
            // $where[] = ['amount', '>=', 5000];
            // $this->limitOffset=0;
            // $this->limitLength=5000;
        }
        return $where;
    }
    /**
     * @notes  获取管理列表
     * @return array
     */
    public function lists(): array
    {
        $field="*";
        $lists = PayinOrder::field($field)
            ->append(['theme','channel_title'])
            ->with(['bank'])
            ->where($this->searchWhere)
            ->where($this->queryWhere())
            ->limit($this->limitOffset, $this->limitLength)
            ->order($this->sortOrder)
            ->select()
            ->toArray();
        
        // if(!empty($this->params['start_time'])&&!empty($this->params['end_time'])){
        // $lists[]=PayinOrder::getLastSql();
        // return $lists;
        // }
        $cashier_url=ConfigService::get('cashier_url','');
        foreach ($lists as &$item) {
            $syturl=$cashier_url;
            $item["create_time"]=diyTimestamp($item["create_time"]);
            $item["update_time"]=diyTimestamp($item["update_time"]);
            $item["status_time"]=diyTimestamp($item["status_time"]);
            $item["request_time"]=diyTimestamp($item["request_time"]);
            $item["expire_time"]=diyTimestamp($item["expire_time"]);
            // @$item['bank_name']=$item['bank']['bank_name'];
            // @$item['user_name']=$item['bank']['user_name'];
            // @$item['bank_num']=$item['bank']['bank_num'];
            // @$item['iban']=$item['bank']['iban'];
            // if($item['update_by']>0){
            //     @$item['update_by']=$item['member']['member_nickname'];
            // }else{
            //     @$item['update_by']="";
            // }
            // unset($item['bank']);
            // unset($item['member']);
            if(empty($syturl)){
               $syturl ="收银台未配置地址";
            }else{
                @$theme=$item['theme']??"";
                $syturl=$syturl."/".$theme."/#/".$item['order_sn'];
            }
            // 收银台
            $item['cashier_desk']=$syturl;
        }
        return $lists;
    }

    /**
     * @notes  获取数量
     * @return int
     */
    public function count(): int
    {
        return PayinOrder::where($this->searchWhere)->where($this->queryWhere())->count();
    }

}