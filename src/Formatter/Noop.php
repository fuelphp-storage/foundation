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

namespace Fuel\Foundation\Formatter;

use Fuel\Foundation\Response\ResponseInterface;
use Zend\Diactoros\CallbackStream;

/**
 * Dummy formatter that simply passes the response right through.
 */
class Noop extends AbstractFormatter
{

	/**
	 * {@inheritdoc}
	 */
	public function canActivate($data) : bool
	{
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function format($data) : ResponseInterface
	{
		/** @var ResponseInterface $response */
		$response = $this->getContainer()->get('fuel.application.response');

		return $response->withBody(new CallbackStream(
			function() use ($data) {
				return $data;
			}
		));
	}
}
