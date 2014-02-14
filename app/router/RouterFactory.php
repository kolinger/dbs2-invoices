<?php

namespace App;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		$router[] = new Route('<presenter>[/<action>][/<id>]', 'Homepage:default');
		return $router;
	}
}
