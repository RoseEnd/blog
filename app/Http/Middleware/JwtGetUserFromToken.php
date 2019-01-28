<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Middleware;

use Closure;

class JwtGetUserFromToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $role = 'user')
    {
        app('JWTAuth')->authenticate($request);

        $user = app('JWTAuth')->user();
        if ($role == 'admin' && $user->admin != 'yes') {
            return response()->json('forbidden', 403);
        }
        app()->singleton('api.user', $user);
        return $next($request);
    }
}
