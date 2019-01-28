<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string $role
     * @return mixed
     */
    public function handle($request, Closure $next, string $role = 'user')
    {
        if ($this->auth->guard()->guest()) {
            return response('Unauthorized.', 401);
        }
        $user = $this->auth->guard()->user();
        if ($role == 'admin' && $user->admin == 'no') {
            return response('forbidden', 401);
        }
        /*将用户信息绑定到容器单例*/
        app()->singleton('api.user', function () use ($user) {
            return $user;
        });
        return $next($request);
    }
}
