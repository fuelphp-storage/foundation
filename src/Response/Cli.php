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

class Cli implements ResponseInterface
{
	protected $statusCode;

	public function setStatusCode($code)
	{
		$this->statusCode = $code;
	}

	public function getStatusCode()
	{
		return $this->statusCode;
	}
}
