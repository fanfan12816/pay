<?php


declare(strict_types=1);

namespace app\common\service;

use app\model\SystemConfig as Config;

class ConfigService
{
    /**
     * @notes 设置配置值
     * @param $type
     * @param $name
     * @param $value
     * @return mixed
     * @author 段誉
     * @date 2021/12/27 15:00
     */
    public static function set(string $type, $value, string $name='')
    {
        $original = $value;
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $data = Config::where(['config_key' => $type])->findOrEmpty();

        if ($data->isEmpty()) {
            Config::create([
                'config_key' => $type,
                'config_name' => $name,
                'config_value' => $value,
            ]);
        } else {
            $data->value = $value;
            $data->save();
        }

        // 返回原始值
        return $original;
    }

    /**
     * @notes 获取配置值
     * @param $type
     * @param string $name
     * @param null $default_value
     * @return array|int|mixed|string
     */
    public static function get(string $type, $default_value = null, string $name = '')
    {
        $value = Config::where(['config_key' => $type])->value('config_value');
        if (!is_null($value)) {
            $json = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $json : $value;
        }
        if ($value) {
            return $value;
        }
        // 返回特殊值 0 '0'
        if ($value === 0 || $value === '0') {
            return $value;
        }
        // 返回默认值
        if ($default_value !== null) {
            return $default_value;
        }
        // 返回本地配置文件中的值
        return config('project.' . $type . '.' . $name);
    }
}