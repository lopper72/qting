<?php
/**
 * 用户管理服务
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Services;

use App\Models\Config;
use App\Models\User;

class UserService {

    public static $ACCOUNT_TYPE = [
        'AMOUNT'    => '余额账户',
        'INTEGRAL'  => '积分账户',
        'GOLD'      => '金币账户',
    ];

    public static $AGENT_LIST = [
        1   => '一级代理',
        2   => '二级代理',
        3   => '三级代理',
        4   => '四级代理',
        5   => '五级代理',
    ];

    public function __construct()
    {
    }

    public static function setLevel($user_id, $refcode)
    {
        $pid = User::where('refcode', $refcode)->value('id');
        if (empty($pid)) {
            throw new \Exception("推荐码不正确");
        }
        User::where('id', $user_id)->update(
            [
                'pid'   => $pid
            ]
        );
        $level = '';
        self::getParentsStr($user_id, $level);
        User::where('id', $user_id)->update(
            [
                'level' => $level
            ]
        );
        return true;
    }

    public static function getParentsStr($user_id, &$level)
    {
        $pid = User::where('id', $user_id)->value('pid');
        if (empty($pid)) {
            return $level;
        }
        $level = '/' . $pid . $level;
        self::getParentsStr($pid, $level);
    }

    public static function getLevel($user_id)
    {
        $level = User::where('id', $user_id)->value('level');
        if (empty($level)) {
            return 1;
        }
        $levels = explode('/', $level);
        return count($levels) + 1;
    }

    public static function getParents($user_id)
    {
        $level = User::where('id', $user_id)->value('level');
        if (empty($level)) {
            return [];
        }
        $levels = explode('/', $level);
        return $levels;
    }

    public static function getChildNum($user_id)
    {
        return User::where('level', 'like', "%/$user_id/%")->orWhere('level', "/$user_id")->count();
    }

    public static function getChildList($user_id)
    {
        $list = User::where('level', 'like', "%/$user_id/%")->orWhere('level', "/$user_id")->select('id', 'username', 'nickname', 'pid', 'level')->get()->toArray();
        return self::recursion($list, $user_id);
    }

    public static function getAllChildUserIds($user_id)
    {
        return User::where('level', 'like', "%/$user_id/%")->orWhere('level', "/$user_id")->pluck('id')->toArray();
    }

    public static function recursion($data, $pid = 0)
    {
        $child = [];
        foreach ($data as $key => $value) {
            if ($value['pid'] == $pid) {
                unset($data[$key]);
                $value['child'] = self::recursion($data, $value['id']);
                $child[] = $value;
            }
        }
        return $child;
    }

    public static function getChildUserIds($user_ids)
    {
        if (empty($user_ids)) {
            return [];
        }
        return User::where('status', 1)->whereIn('pid', $user_ids)->pluck('id')->toArray();
    }

    public static function getNum($user_ids)
    {
        if (empty($user_ids)) {
            return 0;
        }
        return User::where('status', 1)->whereIn('pid', $user_ids)->count();
    }

    public static function doAccount($user_id, $amount = 0, $remark = '', $type = 'GOLD', $source = 'AGENT_RECHARGE')
    {
        try {
            switch ($type) {
                case 'AMOUNT':
                    $result = User::amount($user_id, $amount, $remark, $source);
                    break;
                case 'INTEGRAL':
                    $result = User::integral($user_id, $amount, $remark, $source);
                    break;
                case 'GOLD':
                    $result = User::gold($user_id, $amount, $remark, $source);
                    break;
            }
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function doAgentAmount($user_id, $amount = 0, $source = 'AGENT_RECHARGE')
    {
        $config = Config::getConfig('agent');
        $account_type = $config['agent_account'] ?? 'GOLD';
        $level = intval($config['agent_level']);
        $agent_config = json_decode($config['agent_config'], true);

        if (empty($amount)) {
            $amount = $config['agent_back_amount'] ?? 0;
        }

        if ($level == 0 || empty($amount) || empty($user_id)) {
            return false;
        }
        try {
            $one_pid = 0;
            if ($level >= 1) {
                $rate = isset($agent_config[0]) ? intval($agent_config[0]['rate']):0;
                if ($rate) {
                    $one_pid = User::where('id', $user_id)->value('pid');
                    if ($one_pid) {
                        $amount_to = round($amount * ($rate / 100), 2);
                        self::doAccount($one_pid, $amount_to, "一级代理返利{$rate}%", $account_type, $source);
                    }
                }
            }
            $two_pid = 0;
            if ($level >= 2 && $one_pid) {
                $rate = isset($agent_config[1]) ? intval($agent_config[1]['rate']):0;
                if ($rate) {
                    $two_pid = User::where('id', $one_pid)->value('pid');
                    if ($two_pid) {
                        $amount_to = round($amount * ($rate / 100), 2);
                        self::doAccount($two_pid, $amount_to, "二级代理返利{$rate}%", $account_type, $source);
                    }
                }
            }
            $three_pid = 0;
            if ($level >= 3 && $two_pid) {
                $rate = isset($agent_config[2]) ? intval($agent_config[2]['rate']):0;
                if ($rate) {
                    $three_pid = User::where('id', $two_pid)->value('pid');
                    if ($three_pid) {
                        $amount_to = round($amount * ($rate / 100), 2);
                        self::doAccount($three_pid, $amount_to, "三级代理返利{$rate}%", $account_type, $source);
                    }
                }
            }
            $four_pid = 0;
            if ($level >= 4 && $three_pid) {
                $rate = isset($agent_config[3]) ? intval($agent_config[3]['rate']):0;
                if ($rate) {
                    $four_pid = User::where('id', $three_pid)->value('pid');
                    if ($four_pid) {
                        $amount_to = round($amount * ($rate / 100), 2);
                        self::doAccount($four_pid, $amount_to, "四级代理返利{$rate}%", $account_type, $source);
                    }
                }
            }
            if ($level >= 5 && $four_pid) {
                $rate = isset($agent_config[4]) ? intval($agent_config[4]['rate']):0;
                if ($rate) {
                    $five_pid = User::where('id', $four_pid)->value('pid');
                    if ($five_pid) {
                        $amount_to = round($amount * ($rate / 100), 2);
                        self::doAccount($five_pid, $amount_to, "五级代理返利{$rate}%", $account_type, $source);
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
