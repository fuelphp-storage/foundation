<?php
/**
 * @package    Fuel\Session
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Session;

use Fuel\Session\Driver;
use Fuel\Session\DataContainer;
use Fuel\Session\FlashContainer;

/**
 * Session driver using a memcached backend
 *
 * @package  Fuel\Session
 *
 * @since  2.0.0
 */
class Memcached extends Driver
{
	/**
	 * @var  array  session driver config defaults
	 */
	protected $defaults = array(
		'cookie_name'           => 'fuelmid',
		'key_prefix'            => '',
	);

	/**
	 * @var  bool  flag to indicate session state
	 */
	protected $started = false;

	/**
	 * @var  Memcached  This drivers Memcached instance
	 */
	protected $memcached = false;

	/**
	 * Constructor
	 *
	 * @param  array    $config  driver configuration
	 * @since  2.0.0
	 */
	public function __construct(array $config = array(), \Memcached $memcached)
	{
		// make sure we've got all config elements for this driver
		$config['memcached'] = array_merge($this->defaults, isset($config['memcached']) ? $config['memcached'] : array());

		// call the parent to process the global config
		parent::__construct($config);

		// store the defined name
		if (isset($config['memcached']['cookie_name']))
		{
			$this->setName($config['memcached']['cookie_name']);
		}

		// store the memcached storage instance
		$this->memcached = $memcached;
	}

    /**
     * Create a new session
     *
     * @param  DataContainer $data
     * @param  FlashContainer $flash
     *
     * @return bool  result of the start operation
	 * @since  2.0.0
     */
    public function create(DataContainer $data, FlashContainer $flash)
    {
		// start the session
		if ( ! $this->started)
		{
			$this->start($data, $flash);
		}
	}

    /**
     * Start the session, and read existing session data back
     *
     * @param  DataContainer $data
     * @param  FlashContainer $flash
     *
     * @return bool  result of the start operation
	 * @since  2.0.0
     */
    public function start(DataContainer $data, FlashContainer $flash)
    {
		// generate a new session id
		$this->regenerate();

		// mark the session as started
		$this->started = true;

		// and read any existing session data
		return $this->read($data, $flash);
	}

    /**
     * Read session data
     *
     * @param  DataContainer $data
     * @param  FlashContainer $flash
     *
     * @return bool  result of the write operation
	 * @since  2.0.0
     */
    public function read(DataContainer $data, FlashContainer $flash)
    {
		// bail out if we don't have an active session
		if ($this->started)
		{
			// fetch the session id
			if ($sessionId = $this->findSessionId())
			{
				// and use that to fetch the payload
				$payload = $this->memcached->get($this->config['memcached']['key_prefix'].$this->name.'_'.$sessionId);

				// make sure we got something meaningful
				if (is_string($payload) and substr($payload,0,2) == 'a:')
				{
					// unserialize it
					$payload = unserialize($payload);

					// verify and process the payload
					return $this->processPayload($payload, $data, $flash);
				}
			}
		}

		// no session started, or no valid session data present
		return false;
	}

    /**
     * Write session data
     *
     * @param  DataContainer $data
     * @param  FlashContainer $flash
     *
     * @return bool  result of the write operation
	 * @since  2.0.0
     */
    public function write(DataContainer $data, FlashContainer $flash)
    {
		// bail out if we don't have an active session
		if ( ! $this->started)
		{
			return false;
		}

		// assemble the session payload
		$payload = $this->assemblePayload($data, $flash);
		$expiration = $payload['security']['ex'];
		$payload = serialize($payload);

		// and store it
		return $this->memcached->set($this->config['memcached']['key_prefix'].$this->name.'_'.$this->sessionId, $payload, $expiration);
	}

    /**
     * Stop the session
     *
     * @param  DataContainer $data
     * @param  FlashContainer $flash
     *
     * @return bool  result of the write operation
	 * @since  2.0.0
     */
    public function stop(DataContainer $data, FlashContainer $flash)
    {
		// bail out if we don't have an active session
		if ( ! $this->started)
		{
			return false;
		}

		// write the data in the session
		$this->write($data, $flash);

		// set the session cookie
		return $this->setCookie(
			$this->name,
			$this->sessionId
		);
	}

    /**
     * Destroy the session
     *
     * @return bool  result of the write operation
	 * @since  2.0.0
     */
    public function destroy()
    {
		// we need to have a session started
		if ($this->started)
		{
			return $this->deleteCookie($this->name);
		}

		return false;
	}
}
