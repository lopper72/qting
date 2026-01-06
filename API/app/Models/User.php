<?php
/**
 * 用户模型
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasRoles;

    protected $table = 'users';

    protected $dateFormat = 'U';

    protected $casts = [
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'phone',
        'email',
        'password',
        'avatar',
        'amount',
        'integral',
        'vip_end_time',
        'refcode',
        'truename',
        'qq',
        'avatar',
        'nickname',
        'desc',
        'sex',
        'birthday',
        'alipay_account_name',
        'alipay_account',
        'pid',
        'is_auth',
        'is_audio',
        'last_login_time',
        'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'api_token',
        'wx_openid',
        'qq_openid',
        'wb_openid',
        'is_admin',
        'is_super'
    ];

    public static function resetPass($username, $password)
    {
        return self::where('username', $username)->update([
            'password' => bcrypt($password)
        ]);
    }

    public static function getRefNum($user_id)
    {
        $key = 'my_ref_num_'.$user_id;
        $num = cache($key);
        if (empty($num)){
            $num = self::where('pid', $user_id)->count();
            cache([$key => $num], mt_rand(10,30));
        }
        return $num;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public static function setUsername($id)
    {
        $username = getRandNumber();
        if (self::where('username', $username)->exists()) {
            self::setUsername($id);
        } else {
            return self::where('id', $id)->update(['username' => $username]);
        }
    }

    /**
     * 操作账户金额
     */
    public static function amount($user_id, $amount = 0, $remark = '', $source = 'AGENT_RECHARGE')
    {
        if ($amount > 0) {
            $result = self::where('id', $user_id)->increment('amount', abs($amount));
            if (!$result) {
                throw new \Exception('增加余额失败');
            }
        } else {
            $user = self::where('id', $user_id)->first();
            if ($user->amount < abs($amount)) {
                throw new \Exception('余额不足');
            }
            $result = self::where('id', $user_id)->decrement('amount', abs($amount));
            if (!$result) {
                throw new \Exception('扣除余额失败');
            }
        }
        UserAccountLog::create([
            'user_id'   => $user_id,
            'type'      => UserAccountLog::TYPE_AMOUNT,
            'amount'    => $amount,
            'source'    => $source,
            'remark'    => $remark,
            'status'    => 1,
        ]);
        return $result;
    }

    /**
     * 操作积分
     */
    public static function integral($user_id, $integral = 0, $remark = '', $source = 'AGENT_RECHARGE')
    {
        if ($integral > 0) {
            $result = self::where('id', $user_id)->increment('integral', abs($integral));
            if (!$result) {
                throw new \Exception('增加积分失败');
            }
        } else {
            $user = self::where('id', $user_id)->first();
            if ($user->integral < abs($integral)) {
                throw new \Exception('积分不足');
            }
            $result = self::where('id', $user_id)->decrement('integral', abs($integral));
            if (!$result) {
                throw new \Exception('扣除积分失败');
            }
        }
        UserAccountLog::create([
            'user_id'   => $user_id,
            'type'      => UserAccountLog::TYPE_INTEGRAL,
            'integral'  => $integral,
            'source'    => $source,
            'remark'    => $remark,
            'status'    => 1,
        ]);
        return $result;
    }

    /**
     * 操作金币
     */
    public static function gold($user_id, $gold = 0, $remark = '', $source = 'AGENT_RECHARGE')
    {
        if ($gold > 0) {
            $result = self::where('id', $user_id)->increment('gold', abs($gold));
            if (!$result) {
                throw new \Exception('增加金币失败');
            }
        } else {
            $user = self::where('id', $user_id)->first();
            if ($user->gold < abs($gold)) {
                throw new \Exception('金币不足');
            }
            $result = self::where('id', $user_id)->decrement('gold', abs($gold));
            if (!$result) {
                throw new \Exception('扣除金币失败');
            }
        }
        UserAccountLog::create([
            'user_id'   => $user_id,
            'type'      => UserAccountLog::TYPE_GOLD,
            'gold'      => $gold,
            'source'    => $source,
            'remark'    => $remark,
            'status'    => 1,
        ]);
        return $result;
    }
}
