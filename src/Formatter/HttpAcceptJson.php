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

use Fuel\Foundation\Request\RequestInterface;
use Fuel\Foundation\Response\ResponseInterface;
use Zend\Diactoros\CallbackStream;

/**
 * Formats data in to json if the header "Accept: application/json" is present in the request.
 */
class HttpAcceptJson extends AbstractFormatter
{

	/**
	 * {@inheritdoc}
	 */
	public function canActivate($data) : bool
	{
		/** @var RequestInterface $request */
		$request = $this->getContainer()->get('fuel.application.request');

		return $request->hasHeader('Accept') &&
			in_array('application/json', $request->getHeader('Accept'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function format($data) : ResponseInterface
	{
		/** @var ResponseInterface $response */
		$response = $this->getContainer()->get('fuel.application.response');

		return $response
			->withBody(new CallbackStream(
				function() use ($data) {
					return json_encode($data);
				}
			))
			->withHeader('Content-Type', 'application/json');
	}
}
