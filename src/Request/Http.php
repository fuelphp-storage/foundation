<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Request;

use Zend\Diactoros\ServerRequest;

class Http extends ServerRequest implements RequestInterface
{
	public static function forge()
	{
		return HttpRequestFactory::fromGlobals();
	}
}
