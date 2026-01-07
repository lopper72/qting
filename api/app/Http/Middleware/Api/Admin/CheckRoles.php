<?php
/**
 * 后台验证角色中间件
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Middleware\Api\Admin;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

class CheckRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::guard('api')->user();
        $roles = $user->getRoleNames()->toArray();
        if (count($roles) <= 0) {
            throw new \Exception('对不起，您没有权限！');
        }
        if (in_array('admin', $roles)) {
            return $next($request);
        }
        $route_name = Route::currentRouteName();
        foreach ($roles as $role) {
            $roleModel = Role::where('name', $role)->first();
            if ($roleModel->hasPermissionTo($route_name)) {
                return $next($request);
            }
        }
        if (($user->username == 'demo')) {
            throw new \Exception('演示站点，请不要修改数据，谢谢！');
        }
    }
}
