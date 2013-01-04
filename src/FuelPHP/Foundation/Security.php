<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Foundation
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Foundation;

/**
 * Base Security class
 *
 * Container for various Security handlers.
 *
 * @package  FuelPHP\Foundation
 *
 * @since  1.0.0
 */
class Security
{
	/**
	 * @var  \FuelPHP\Foundation\Environment
	 */
	protected $env;

	/**
	 * @var  \FuelPHP\Foundation\Security\Crypt
	 *
	 * @since  2.0.0
	 */
	protected $crypt;

	/**
	 * @var  \FuelPHP\Foundation\Security\Csrf
	 *
	 * @since  2.0.0
	 */
	protected $csrf;

	/**
	 * @var  \FuelPHP\Foundation\Security\String
	 *
	 * @since  2.0.0
	 */
	protected $string;

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		// set the environment variable necessary for the package loader object
		$this->env = \FuelPHP\Foundation\Environment::singleton();
	}

	/**
	 * Returns the App's Crypt instance
	 *
	 * @return  Crypt\Cryptable
	 *
	 * @since  2.0.0
	 */
	public function getCrypt()
	{
		! isset($this->crypt) and $this->crypt = $this->env->forge('FuelPHP\Foundation\Security\Crypt');
		return $this->crypt;
	}

	/**
	 * Returns the App's Csrf instance
	 *
	 * @return  Csrf\Base
	 *
	 * @since  2.0.0
	 */
	public function getCsrf()
	{
		! isset($this->csrf) and $this->csrf = $this->env->forge('FuelPHP\Foundation\Security\Csrf');
		return $this->csrf;
	}

	/**
	 * Returns the App's String cleaner instance
	 *
	 * @return  String\Base
	 *
	 * @since  2.0.0
	 */
	public function getStringCleaner()
	{
		! isset($this->string) and $this->string = $this->env->forge('FuelPHP\Foundation\Security\String\Htmlentities');
		return $this->string;
	}

	/**
	 * Separate method for cleaning the URI
	 *
	 * @param   string  $uri
	 * @param   null|bool|string|String\Base  $filter
	 * @return  string
	 *
	 * @since  1.1.0
	 */
	public function cleanUri($uri, $filter = null)
	{
		// Set default when null
// CHECKME
		is_null($filter) and $filter = true; //$this->app->config->get('security.uri_filter', true);

		// When true use internal security filter
		$filter === true and $filter = $this->getStringCleaner();

		// When string is passed try to fetch special filter from DiC
		is_string($filter) and $filter = $this->env->forge('FuelPHP\\Foundation\\Security\\String\\'.$filter);

		// Whatever is left is either boolean false or a String Security object
		return $filter ? $filter->clean($uri) : $uri;
	}

	/**
	 * Clean a variable with the String cleaner
	 *
	 * @param   mixed  $input
	 * @return  mixed
	 *
	 * @since  1.0.0
	 */
	public function clean($input)
	{
		return $this->getStringCleaner()->clean($input);
	}

	/**
	 * Fetch the CSRF token
	 *
	 * @return string
	 *
	 * @since  1.0.0
	 */
	public function getToken()
	{
		return $this->getCsrf()->getToken();
	}

	/**
	 * Check the CSRF token
	 *
	 * @param   null|string  $token
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function checkToken($token = null)
	{
		return $this->getCsrf()->checkToken($token);
	}
}
