<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;
use Fuel\Foundation\Whoops\ProductionHandler;

/**
 * Error class, implements the Whoops error handler
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Error
{
	/**
	 * @var  Whoops\Run  the Whoops error handler instance
	 */
	protected $whoops;

	/**
	 * Initialization, set the Error handler
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		// use the framework default Whoops error handler
		$this->whoops = new Run;

		// define the default page handler
		$pagehandler = new PrettyPageHandler;
		$pagehandler->addResourcePath(__DIR__.DS.'Whoops'.DS.'resources');

		$pagehandler->addDataTableCallback('Current Request', function()
		{
			$application = \Application::getInstance();
			$environment = $application->getEnvironment();
			$request     = \Request::getInstance();
			$route = $request ? $request->getRoute() : null;
			$controller = $route ? $route->controller : '';
			$parameters = $route ? $route->parameters : array();
			array_shift($parameters);

			return array(
				'Application'  => $application ? $application->getName() : '',
 				'Environment'  => $environment ? $environment->getName() : '',
				'Original URI' => $route ? $route->uri : '',
				'Mapped URI'   => $route ? $route->translation : '',
				'Namespace'    => $route ? $route->namespace : '',
				'Controller'   => $controller,
				'Action'       => $controller ? ('action'.$route->action) : '',
				'HTTP Method'  => $request ? \Input::getMethod() : '',
				'Parameters'   => $parameters,
			);
		});
		$pagehandler->addDataTableCallback('Request Parameters', function()
		{
			$input = \Input::getInstance();
			return $input ? $input->getParam()->getContents() : '';
		});
		$pagehandler->addDataTableCallback('Permanent Session Data', function()
		{
			if ($application = \Application::getInstance())
			{
				if ($session = $application->getSession())
				{
					return $session->getContents();
				}
			}
			return 'no session active';
		});
		$pagehandler->addDataTableCallback('Flash Session Data', function()
		{
			if ($application = \Application::getInstance())
			{
				if ($session = $application->getSession())
				{
					return $session->getContentsFlash();
				}
			}
			return 'no session active';
		});
		$pagehandler->addDataTableCallback('Defined Cookies', function()
		{
			$input = \Input::getInstance();
			return $input ? $input->getCookie() : '';
		});
		$pagehandler->addDataTableCallback('Uploaded Files', function()
		{
			$input = \Input::getInstance();
			return $input ? $input->getFile() : '';
		});
		$pagehandler->addDataTableCallback('Server Data', function()
		{
			return $_SERVER;
		});

		$this->whoops->pushHandler($pagehandler);

		// next on the stack goes the JSON handler, to deal with AJAX reqqests
		$jsonHandler = new JsonResponseHandler;
		$jsonHandler->onlyForAjaxRequests(true);
		// $jsonHandler->addTraceToOutput(true);

		$this->whoops->pushHandler($jsonHandler);

		// add the Fuel production handler
		$productionHandler = new ProductionHandler;
		$this->whoops->pushHandler($productionHandler);

		// activate the error handler
		$this->whoops->register();

		// set a custom handler, so we can deal with translations
		$current_handler = set_exception_handler(function($e) use(&$current_handler)
		{
			// get the locale
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			{
				// if the locale is set to C, default to English
				if (($locale = getenv('LC_ALL')) === 'C')
				{
					$locale = 'English';
				}
			}
			else
			{
				// if the locale is set to C, default to en_US
				if (($locale = setlocale(LC_MESSAGES, null)) === 'C')
				{
					$locale = 'en_US';
				}
			}

			// get access to the exception's error message
			$reflection = new \ReflectionClass($e);
			$property = $reflection->getProperty("message");
			$property->setAccessible(true);

			// get the translations for the current locale
			if ($translations = $this->getMessages($locale, true))
			{
				// does the error message exist?
				$messageId = substr($e->getMessage(), 0,7);
				if (isset($translations[$messageId]))
				{
					// swap the original message for the translated one
					$property->setValue($e, $this->setMessage($translations[$messageId], $e->getMessage()));
				}
			}

			// call the original error handler with the translated exception message
			call_user_func($current_handler, $e);
		});
	}

	/**
	 * Load the correct file with translations, based on the locale passed
	 *
	 * @since  2.0.0
	 */
	protected function getMessages($locale, $shorten = false)
	{
		$baseDir = realpath(__DIR__.'/../../../translations');

		$lookup = array($locale);
		if ($shorten)
		{
			$lookup[] = substr($locale, 0, 2);
		}

		foreach($lookup as $lang)
		{
			if (is_file($lang = $baseDir.'/'.$lang.'.php'))
			{
				$translations = include $lang;
				if (is_string($translations))
				{
					return $this->getMessages($translations);
				}
				return $translations;
			}
		}

		// nothing found
		return false;
	}

	/**
	 * Convert the original message to the translated message
	 *
	 * @since  2.0.0
	 */
	protected function setMessage($translation, $original)
	{
		// strip any parameters from the original message and unify the message for translation
		if (preg_match_all('~\[(.*?)\]~', $original, $matches) and ! empty($matches[1]))
		{
			$params = $matches[1];
			$message = preg_replace_callback('~\[(.*?)\]~', function($matches) { static $c = 0; return '"%'.(++$c).'$s"'; }, $original);
		}

		// put the parameters back in if needed
		if ( ! empty($params))
		{
			$translation = vsprintf($translation, $params);
		}

		return $translation;
	}
}
