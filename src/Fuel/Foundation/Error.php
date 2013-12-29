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
	 * @var  Whoops\Handler\PrettyPageHandler  the current page handler
	 */
	protected $pagehandler;

	/**
	 * Initialization, set the Error handler
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		// are we in a CLi environment?
		if ((bool) defined('STDIN'))
		{
			$this->commandlineHandler();
		}

		// is this an ajax call?
		elseif(false)
		{
			$this->ajaxCallHandler();
		}

		// load the default interactive error handler
		else
		{
			$this->interactiveHandler();
		}
	}

	/**
	 * Load the correct file with translations, based on the locale passed
	 *
	 * @return  Whoops\Handler\PrettyPageHandler
	 *
	 * @since  2.0.0
	 */
	public function handler()
	{
		return $this->pagehandler;
	}

	/**
	 * Error handler for when the framework is used in CLi environments
	 *
	 * @since  2.0.0
	 */
	protected function commandlineHandler()
	{
	}

	/**
	 * Error handler for when the framework is used in Ajax environments
	 *
	 * @since  2.0.0
	 */
	protected function ajaxCallHandler()
	{
	}

	/**
	 * Error handler for when the framework is used in Web environments
	 *
	 * @since  2.0.0
	 */
	protected function interactiveHandler()
	{
		// use the framework default Whoops error handler
		$this->whoops = new Run;

		$this->whoops->writeToOutput(false);
		$this->whoops->allowQuit(false);

		// define the default page handler
		$this->pagehandler = new PrettyPageHandler;
		$this->pagehandler->addResourcePath(__DIR__.DS.'Whoops'.DS.'resources');

		$this->pagehandler->addDataTableCallback('Application', function()
		{
			$application = \Application::getInstance();
			$environment = $application->getEnvironment();
			$request     = \Request::getInstance();
			$route = $request ? $request->getRoute() : null;

			return array(
				'Active application'    => $application ? $application->getName() : '',
				'Application namespace' => $route ? rtrim($route->namespace, '\\') : '',
 				'Environment'           => $environment ? $environment->getName() : '',
			);
		});

		$this->pagehandler->addDataTableCallback('Current Request', function()
		{
			$request = \Request::getInstance();
			$route = $request ? $request->getRoute() : null;
			$controller = $route ? $route->controller : '';
			$parameters = $route ? $route->parameters : array();
			array_shift($parameters);

			return array(
				'Original URI'          => $route ? $route->uri : '',
				'Mapped URI'            => $route ? $route->translation : '',
				'Controller'            => $controller,
				'Action'                => $controller ? ('action'.$route->action) : '',
				'HTTP Method'           => $request ? $request->getInput()->getMethod() : '',
				'Parameters'            => $parameters,
			);
		});

		$this->pagehandler->addDataTableCallback('Request Parameters', function()
		{
			$request = \Request::getInstance();
			return $request ? $request->getInput()->getParam()->getContents() : '';
		});

		$this->pagehandler->addDataTableCallback('Permanent Session Data', function()
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

		$this->pagehandler->addDataTableCallback('Flash Session Data', function()
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

		$this->pagehandler->addDataTableCallback('Defined Cookies', function()
		{
			$result = array();
			if ($input = \Input::getInstance())
			{
				foreach ($input->getCookie() as $cookie)
				{
					$result[] = $cookie;
				}
			}
			return $result;
		});

		$this->pagehandler->addDataTableCallback('Uploaded Files', function()
		{
			$result = array();
			if ($input = \Input::getInstance())
			{
				foreach ($input->getFile() as $file)
				{
					$result[] = $file;
				}
			}
			return $result;
		});

		$this->pagehandler->addDataTableCallback('Server Data', function()
		{
			return $_SERVER;
		});

		$this->whoops->pushHandler($this->pagehandler);

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
			$result = call_user_func($current_handler, $e);

			// re-enable output buffering, then send the response for the handlers out
			ob_start();
			echo $result;
		});
	}

	/**
	 * Load the correct file with translations, based on the locale passed
	 *
	 * @return  mixed  array of message translations, or false if none are found
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
	 * @param  string  $translation  message in the current locale
	 * @param  string  $original     original error message
	 *
	 * @return  string  translated error message
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
