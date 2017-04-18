<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2017 Fuel Development Team
 * @link       http://fuelphp.com
 */

declare(strict_types=1);

namespace Fuel\Foundation\Request;

use Psr\Http\Message\UriInterface;

interface RequestInterface
{
	/**
	 * @return UriInterface
	 */
	public function getUri();

	public function getMethod();
}
