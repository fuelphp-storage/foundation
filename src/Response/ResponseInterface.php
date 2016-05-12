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

namespace Fuel\Foundation\Response;

use Psr\Http\Message\StreamInterface;

interface ResponseInterface
{
	public function getStatusCode();

	public function withBody(StreamInterface $body);

	public function getBody();

	public function withStatus($status);
}
