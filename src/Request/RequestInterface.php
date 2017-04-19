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

	public function getHeaders();

	/**
	 * @param string $header
	 *
	 * @return bool
	 */
	public function hasHeader($header);

	/**
	 * @param string $header Case-insensitive header field name.
	 * @return string[] An array of string values as provided for the given
	 *    header. If the header does not appear in the message, this method MUST
	 *    return an empty array.
	 */
	public function getHeader($header);
}
