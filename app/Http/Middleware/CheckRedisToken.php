<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRedisToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $redisKey = 'user:' . $user->id;
            Log::info('CheckRedisToken: User authenticated. User ID: ' . $user->id . ', Redis Key: ' . $redisKey);

            $redisData = Redis::get($redisKey);

            if ($redisData) {
                $redisToken = json_decode($redisData)->token;
                $requestToken = $request->bearerToken();

                Log::info('CheckRedisToken: Redis Data found.');
                Log::info('CheckRedisToken: Redis Token: ' . $redisToken);
                Log::info('CheckRedisToken: Request Token: ' . $requestToken);

                if ($redisToken === $requestToken) {
                    Log::info('CheckRedisToken: Tokens match. Request proceeding.');
                    return $next($request);
                }
            } else {
                Log::warning('CheckRedisToken: No Redis data found for key: ' . $redisKey);
            }
            
            Log::warning('CheckRedisToken: Tokens do not match or Redis data not found. Unauthorized.');
            return response()->json(['message' => 'Unauthorized: Invalid or expired token.'], 401);
        }

        Log::warning('CheckRedisToken: No user authenticated.');
        return response()->json(['message' => 'Unauthorized: No user authenticated.'], 401);
    }
}
