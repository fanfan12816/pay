<?php

declare(strict_types=1);

namespace app\common\validate;

use think\Validate;

class BaseValidate extends Validate
{
    public string $method = 'GET';

    /**
     * @notes 设置请求方式
     */
    public function post()
    {
        if (!$this->request->isPost()) {
            return ajaxReturn(4002,'请求方式错误，请使用POST请求方式');
        }
        $this->method = 'POST';
        return $this;
    }

    /**
     * @notes 设置请求方式
     */
    public function get()
    {
        if (!$this->request->isGet()) {
            // returnThrow();
            return ajaxReturn(4003,'请求方式错误，请使用get请求方式');
        }
        return $this;
    }


    /**
     * @notes 切面验证接收到的参数
     * @param null $scene 场景验证
     * @param array $validateData 验证参数，可追加和覆盖掉接收的参数
     * @return array
     */
    public function goCheck($scene = null, array $validateData = []): array
    {
        //接收参数
        if ($this->method == 'GET') {
            $params = request()->get();
        } else {
            $params = request()->post();
        }
        //合并验证参数
        $params = array_merge($params, $validateData);

        //场景
        if ($scene) {
            $result = $this->scene($scene)->check($params);
        } else {
            $result = $this->check($params);
        }

        if (!$result) {
            $exception = is_array($this->error) ? implode(';', $this->error) : $this->error;
            // returnThrow($exception);
            return ajaxReturn(4001,$exception);
        }
        // 3.成功返回数据
        return $params;
    }
}