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

namespace Fuel\Foundation;

use Fuel\Foundation\Request\RequestInterface;
use Fuel\Foundation\Response\ResponseInterface;
use League\Container\ServiceProvider;

class ApplicationServicesProvider extends ServiceProvider
{

	protected $provides = [
		'fuel.application.event',

		'fuel.application.request',
		'Fuel\Foundation\Request\Cli',

		'fuel.application.response',
		'Fuel\Foundation\Response\Cli',
	];

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->getContainer()->add('fuel.application.event', 'League\Event\Emitter', true);

		$this->getContainer()->add('Fuel\Foundation\Request\Cli', 'Fuel\Foundation\Request\Cli', false);
		$this->getContainer()->add('fuel.application.request', $this->constructRequest(), true);

		$this->getContainer()->add('Fuel\Foundation\Response\Cli', 'Fuel\Foundation\Response\Cli', false);
		$this->getContainer()->add('fuel.application.response', $this->constructResponse(), true);
	}

	/**
	 * @return RequestInterface
	 */
	protected function constructRequest() : RequestInterface
	{
		// TODO: perform an actual check to see what kind of request we are dealing with!
		return $this->getContainer()->get('Fuel\Foundation\Request\Cli');
	}

	/**
	 * @return ResponseInterface
	 */
	protected function constructResponse() : ResponseInterface
	{
		// TODO: perform an actual check to see what kind of request we are dealing with!
		return $this->getContainer()->get('Fuel\Foundation\Response\Cli');
	}
}
