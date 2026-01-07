<?php
/**
 * 系统配置模型
 * @date    2021-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

use Illuminate\Support\Facades\Cache;

class System extends BaseModel
{
    protected $table = 'system';

    protected $fillable = [
        'attr_name',
        'attr_value'
    ];

    public static function getConfig($prefix = 'base')
    {
        $key = 'system_list_'.$prefix;
        $config = cache($key);
        if (empty($config)){
            $config = self::where('attr_name', 'like' ,$prefix.'_%')->pluck('attr_value', 'attr_name');
            cache([$key => $config], 3600);
        }
        return $config;
    }

    public static function getAll()
    {
        $key = 'system_all_list';
        $config = cache($key);
        if (empty($config)){
            $config = self::pluck('attr_value', 'attr_name');
            cache([$key => $config], 3600);
        }
        return $config;
    }

    public static function getValue($attr_name = '')
    {
        $key = 'system_value_'.$attr_name;
        $value = cache($key);
        if (empty($value)) {
            $value = self::where('attr_name', $attr_name)->value('attr_value');
            cache([$key => $value], 3600);
        }
        return $value;
    }

    public static function complete($arrs)
    {
        foreach ($arrs as $key => $value) {
            if (empty($key)){
                continue;
            }
            $value = is_null($value) ? '':$value;
            $config = self::where('attr_name', $key)->first();
            if (empty($config)) {
                self::create([
                    'attr_name'     => $key,
                    'attr_value'    => $value
                ]);
            } else {
                self::where('attr_name', $key)->update(['attr_value' => $value]);
            }
        }
        Cache::flush();
        return true;
    }
}
