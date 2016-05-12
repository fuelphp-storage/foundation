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

namespace Fuel\Foundation\Request;

use Psr\Http\Message\UriInterface;

class Cli implements RequestInterface
{
	public function getUri() : UriInterface
	{
		// TODO: Implement getUri() method.
	}

	public function getMethod() : string
	{
		// TODO: Implement getMethod() method.
	}
}
