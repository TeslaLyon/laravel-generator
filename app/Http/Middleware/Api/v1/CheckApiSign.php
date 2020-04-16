<?php

namespace App\Http\Middleware\Api\v1;

use App\Api\Helpers\ApiResponse;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Log;

class CheckApiSign
{

    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (config('app.debug')) {
            return $next($request);
        }

        $timestamp = $request->input('timestamp');
        $sign = $request->input('sign');
        // Log::info('start');
        // Log::info(json_encode($request->all()));
        if (!$timestamp || !$sign) {
            $this->failed(config('return.422'), 422);
        }

        // 1、验证时间
        $now = Carbon::now()->timestamp;
        if ($now - $timestamp >= 60) {
            // Log::info('timestamp is error:$now:'.$now.':$request->timestamp:'.$timestamp);
            return $this->failed('timestamp is error');
        }

        // 2、验证sign
        $BodyData = $request->all();
        $requestSignVal = $BodyData['sign'];
        unset($BodyData['sign']);

        if (getSignature($BodyData) !== $requestSignVal) {
            // Log::info('408:requestSign:$requestSignVal:'.$requestSignVal.':getSignature:'.getSignature($BodyData).':allData:'.json_encode($BodyData));
            return $this->failed(config('return.408'), 408);
        }
        // Log::info('end');
        return $next($request);
    }

}
