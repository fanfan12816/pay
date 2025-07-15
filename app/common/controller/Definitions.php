<?php

namespace app\common\controller;

use hg\apidoc\annotation as Apidoc;

class Definitions
{
    /**
     * 获取分页数据列表的参数
     * @Apidoc\Query("pageIndex",type="int",require=true,default="1",desc="查询页数")
     * @Apidoc\Query("pageSize",type="int",require=true,default="20",desc="查询条数")
     * @Apidoc\Returned("total", type="int", desc="总条数")
     */
    public function pagingParam(){}
  
    /**
     * 返回字典数据
     * @Apidoc\Returned("id",type="int",desc="唯一id")
     * @Apidoc\Returned("name",type="string",desc="字典名")
     * @Apidoc\Returned("value",type="string",desc="字典值")
     */
    public function dictionary(){}

    /**
     * @Apidoc\Header("shopid",type="string",require=true,desc="店铺id")
     */
    public function shopHeader(){}
    
}
