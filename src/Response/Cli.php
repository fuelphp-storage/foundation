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

class Cli implements ResponseInterface
{
	protected $statusCode;
	protected $body;

	public function getStatusCode()
	{
		return $this->statusCode;
	}

	public function getBody()
	{
		return $this->body;
	}

	public function withBody(StreamInterface $body)
	{
		$this->body = $body->__toString();
		return $this;
	}

	public function withStatus($status)
	{
		$this->statusCode = $status;
		return $this;
	}
}
