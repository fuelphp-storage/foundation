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

namespace Fuel\Foundation\Response;

use Psr\Http\Message\StreamInterface;

interface ResponseInterface
{
	public function getStatusCode();

	/**
	 * @param StreamInterface $body
	 *
	 * @return ResponseInterface
	 */
	public function withBody(StreamInterface $body);

	public function getBody();

	/**
	 * @param int $status
	 *
	 * @return ResponseInterface
	 */
	public function withStatus($status);

	/**
	 * @param string       $header
	 * @param array|string $value
	 *
	 * @return ResponseInterface
	 */
	public function withHeader($header, $value);
}
