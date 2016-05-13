<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

declare(strict_types=1);

namespace Fuel\Foundation\Controller;

abstract class AbstractController implements ControllerInterface
{
	protected $routeParams = [];

	public function setRouteParams(array $params)
	{
		$this->routeParams = $params;
	}

	public function getRouteParams() : array
	{
		return $this->routeParams;
	}

	public function getRouteParam(string $name, $default = null)
	{
		if ( ! isset($this->routeParams[$name])) {
			return $default;
		}

		return $this->routeParams[$name];
	}

}
