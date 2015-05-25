<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Routing\Route;
use Laracasts\Flash\Flash;

class EntrustMiddleware
{

	protected $requireRole = false;
	protected $needsPerms = false;

	public function __construct(Guard $auth, Route $route)
	{
		/**
		 * Información del usuario autentificado
		 */
		$this->auth = $auth;

		/**
		 * Si en la ruta definimos que necesita roles
		 * get('uri',['roles'=>'admin'])
		 */
		if(isset($route->getAction()['roles']))
		{
			$this->requireRole = true;
			$this->roles = $route->getAction()['roles'];
		}

		/**
		 * Si en la ruta definimos perms
		 * get('uri',['perms'=>'create-post'])
		 */
		if(isset($route->getAction()['perms']))
		{
			$this->needsPerms = true;
			$this->permissions = $route->getAction()['perms'];
		}
	}

	/**
	 * Handle an incoming request.
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if($this->requireRole)
		{
			return $this->analiceWithRole($request, $next);
		}
		elseif($this->needsPerms)
		{
			return $this->analiceWithPerms($request, $next);
		}
		else
		{
			return $next($request);
		}
	}

	/**
	 * Analiza si el usuario autentificado
	 * tiene el rol(es) solicitado en la ruta
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function analiceWithRole($request, $next)
	{
		if($this->auth->user()->hasRole($this->roles))
		{
			if($this->needsPerms)
			{
				return $this->analiceWithPerms($request, $next);
			}

			return $next($request);
		}
		else
		{
			Flash::warning('No Tiene permisos suficientes para acceder a este recurso.');

			return redirect()->back(302);
		}
	}

	/**
	 * Analiza si el usuario autentificado
	 * tiene los permisos solicitados
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function analiceWithPerms($request, $next)
	{
		if($this->auth->user()->can($this->permissions))
		{
			return $next($request);
		}
		else
		{
			Flash::warning('No Tiene acceso a este recurso.');

			return redirect()->back(302);
		}
	}
}
