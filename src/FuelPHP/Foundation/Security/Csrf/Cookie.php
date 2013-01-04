<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Foundation
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Foundation\Security\Csrf;

/**
 * Cookie CSRF Security class
 *
 * Cookie based tokens to secure against CSRF attacks.
 *
 * @package  Fuel\Kernel
 *
 * @since  2.0.0
 */
class Cookie extends Base
{
	/**
	 * @var  string  token key used in cookie
	 *
	 * @since  2.0.0
	 */
	protected $tokenKey = 'fuel_csrf_token';

	/**
	 * @var  null|string  token for the next Request
	 *
	 * @since  2.0.0
	 */
	protected $newToken;

	/**
	 * Magic Fuel method that is the setter for the current app
	 *
	 * @param   \Fuel\Kernel\Application\Base  $app
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function _setApp(Application\Base $app)
	{

// CHECKME
//		$this->tokenKey = $this->app->config->get('security.csrfTokenKey', 'fuel_csrf_token');
	}

	public function updateToken($forceReset = false)
	{
		$old_token = $this->app->getActiveRequest()->input->cookies->get($this->tokenKey);

		// re-use old token when found (= not expired) and expiration is used (otherwise always reset)
		if ( ! $forceReset and $old_token and $this->app->config->get('security.csrfExpiration', 0) > 0)
		{
			$this->newToken = $old_token;
		}
		// set new token for next session when necessary
		else
		{
			$this->newToken = md5(uniqid().time());

			$expiration = $this->app->config->get('security.csrfExpiration', 0);
			$this->app->getObject('Cookie')->set($this->tokenKey, $this->newToken, $expiration);
		}

		return $this;
	}

	public function getToken()
	{
		if (is_null($this->newToken))
		{
			$this->updateToken();
		}

		return $this->newToken;
	}

	public function checkToken($value = null)
	{
		$value = $value ?: $this->app->getActiveRequest()->input->getCookie($this->tokenKey, null);
		$oldToken = $this->app->getActiveRequest()->input->getCookie($this->tokenKey);

		// always reset token once it's been checked and still the same
		if ($this->getToken() == $oldToken and isset($value))
		{
			$this->updateToken(true);
		}

		return $value === $oldToken;
	}
}
