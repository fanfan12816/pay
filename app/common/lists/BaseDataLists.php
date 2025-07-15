<?php



namespace app\common\lists;


use app\common\validate\ListsValidate;
use app\Request;
use think\facade\Config;

/**
 * 数据列表基类
 * Class BaseDataLists
 * @package app\common\lists
 */
abstract class BaseDataLists implements ListsInterface
{

    use ListsSearchTrait;
    use ListsSortTrait;

    public Request $request; //请求对象

    public int $pageNo; //页码
    public int $pageSize; //每页数量
    public int $limitOffset;  //limit查询offset值
    public int $limitLength;  //limit查询数量
    public int $pageSizeMax;
    public int $pageType = 1; //默认类型：2-一般分页；0-不分页，获取最大所有数据


    protected string $orderBy;
    protected string $field;

    protected $startTime;
    protected $endTime;

    protected $start;
    protected $end;

    protected array $params;
    protected $sortOrder = [];

    public string $export;


    public function __construct()
    {
        //参数验证
        (new ListsValidate())->get()->goCheck();

        //请求参数设置
        $this->request = request();
        $this->params = $this->request->param();

        //分页初始化
        $this->initPage();

        //搜索初始化
        $this->initSearch();

        //排序初始化
        $this->initSort();

    }


    /**
     * @notes 分页参数初始化
     */
    private function initPage()
    {
        $this->pageSizeMax = 25000;
        $this->pageSize = 10;
        $this->pageType = $this->request->get('page_type', 1);

        if ($this->pageType == 1) {
            //分页
            $this->pageNo=1;
            $this->pageSize = $this->pageSize;
            if($this->request->get('page_no')){
                $this->pageNo = $this->request->get('page_no');
            }
            if($this->request->get('pageIndex')){
                $this->pageNo = $this->request->get('pageIndex');
            }
            if($this->request->get('page_size')){
                $this->pageSize = $this->request->get('page_size');
            }
            if($this->request->get('pageSize')){
                $this->pageSize = $this->request->get('pageSize');
            }
           
        } else {
            //不分页
            $this->pageNo = 1;//强制到第一页
            $this->pageSize = $this->pageSizeMax;// 直接取最大记录数
        }

        //limit查询参数设置
        $this->limitOffset = ($this->pageNo - 1) * $this->pageSize;
        $this->limitLength = $this->pageSize;
    }

    /**
     * @notes 初始化搜索
     * @return array
     */
    private function initSearch()
    {
        if (!($this instanceof ListsSearchInterface)) {
            return [];
        }
        $startTime = $this->request->get('start_time');
        if ($startTime) {
            $this->startTime = strtotime($startTime);
        }

        $endTime = $this->request->get('end_time');
        if ($endTime) {
            $this->endTime = strtotime($endTime);
        }

        $this->start = $this->request->get('start');
        $this->end = $this->request->get('end');

        return $this->searchWhere = $this->createWhere($this->setSearch());
    }


    /**
     * @notes 初始化排序
     * @return array|string[]
     */
    private function initSort()
    {
        if (!($this instanceof ListsSortInterface)) {
            return [];
        }

        $this->field = $this->request->get('field', '');
        $this->orderBy = $this->request->get('order_by', '');

        return $this->sortOrder = $this->createOrder($this->setSortFields(), $this->setDefaultOrder());
    }

    /**
     * @notes 不需要分页，可以调用此方法，无需查询第二次
     * @return int
     */
    public function defaultCount(): int
    {
        return count($this->lists());
    }


}