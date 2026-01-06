<?php
/**
 * AES加密中间件
 * @date    2020-01-01
 * @author  kiro
 * @email   294843009@qq.com
 * @version 1.0
 */
namespace App\Http\Middleware\Api;

use App\Services\AesEncryptService;
use Closure;

class CheckAES
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
        return $next($request);
        try {
            $aes_key = env('AES_KEY');
            $aes_iv = env('AES_IV');
            $aes = new AesEncryptService($aes_key, $aes_iv);
            $datas = json_decode($aes->decrypt($request->datas), true);
        } catch (\Exception $exception){
            throw new \Exception('非法请求');
        }
        if (empty($datas)) {
            throw new \Exception('非法请求');
        }
        if (empty($datas['timestamp']) || (abs(time() - (int)$datas['timestamp']) > 60 * 10)) {
            throw new \Exception('请求已过期');
        }
        $request->merge($datas);
        return $next($request);
    }
}
