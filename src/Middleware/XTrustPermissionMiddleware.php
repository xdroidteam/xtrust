<?php namespace XdroidTeam\XTrust\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class XTrustPermissionMiddleware
{
	protected $auth;

	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

	public function handle($request, Closure $next, $permissions)
	{
		if ($this->auth->guest() || !$request->user()->hasPermissions(explode('|', $permissions))) {
            return response('Permission denied.', 403);
		}

		return $next($request);
	}
}
