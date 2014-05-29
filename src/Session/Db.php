<?php
/**
 * @package    Fuel\Session
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Session;

use Fuel\Session\Driver;
use Fuel\Database\Connection;

/**
 * Session driver using a database backend
 *
 * NOTE: this driver is not thread-safe.
 *
 * @package  Fuel\Session
 *
 * @since  2.0.0
 */
class Db extends Driver
{
	/**
	 * @var  array  session driver config defaults
	 */
	protected $defaults = array(
		'cookie_name'           => 'fueldid',
		'table'                 => 'sessions',
		'gc_probability'        => 5,
	);

	/**
	 * @var  bool  flag to indicate session state
	 */
	protected $started = false;

	/**
	 * @var  \Fuel\Database\Connection  This drivers database instance
	 */
	protected $db = false;

	/**
	 * Constructor
	 *
	 * @param  array    $config  driver configuration
	 *
	 * @since  2.0.0
	 */
	public function __construct(array $config = array(), Connection $db)
	{
		// make sure we've got all config elements for this driver
		$config['db'] = array_merge($this->defaults, isset($config['db']) ? $config['db'] : array());

		// call the parent to process the global config
		parent::__construct($config);

		// store the defined name
		if (isset($config['db']['cookie_name']))
		{
			$this->name = $config['db']['cookie_name'];
		}

		// store the database storage instance
		$this->db = $db;
	}

    /**
     * Create a new session
     *
     * @return bool  result of the start operation
	 *
	 * @since  2.0.0
     */
    public function create()
    {
		// create the session
		if ( ! $this->started)
		{
			// generate a new session id
			$this->regenerate();

			// create a new session record
			$result = $this->db->insert($this->config['db']['table'])->values(array(
				'session_id' => $this->sessionId,
				'created_at' => time(),
				'updated_at' => time(),
				'payload' => serialize(array()),
			))->execute();

			// check the result
			if ($result == array(0, 1))
			{
				// mark the session as started
				$this->started = true;

				return true;
			}
		}

		return false;
	}

    /**
     * Start the session, and read existing session data back
     *
     * @return bool  result of the start operation
	 *
	 * @since  2.0.0
     */
    public function start()
    {
		// mark the session as started
		$this->started = true;

		// and read any existing session data
		return $this->read();
	}

    /**
     * Read session data
     *
     * @return bool  result of the write operation
     *
	 * @since  2.0.0
     */
    public function read()
    {
		// bail out if we don't have an active session
		if ( ! $this->started)
		{
			return false;
		}

		// fetch the session id
		if ($sessionId = $this->findSessionId())
		{
			// and use that to fetch the payload
			if ($result = $this->db->select()->from($this->config['db']['table'])->where('session_id', $sessionId)->execute())
			{
				// make sure we got something meaningful
				if ($result = reset($result) and is_object($result) and isset($result->payload) and substr($result->payload,0,2) == 'a:')
				{
					// unserialize it
					$payload = unserialize($result->payload);

					// verify and process the payload
					return $this->processPayload($payload);
				}
			}
		}

		// no session record found, reset the started flag
		$this->started = false;

		// and create a new session
		return $this->create();
	}

    /**
     * Write session data
     *
     * @return bool  result of the write operation
     *
	 * @since  2.0.0
     */
    public function write()
    {
		// bail out if we don't have an active session
		if ( ! $this->started)
		{
			return false;
		}

		// assemble the session payload
		$payload = serialize($this->assemblePayload());

		// and store it
		$result = $this->db->update($this->config['db']['table'])
			->set('payload', $payload)
			->set('updated_at', time())
			->where('session_id', $this->sessionId)
			->execute();

		return $result === 1;
	}

    /**
     * Stop the session
     *
     * @return bool  result of the write operation
     *
	 * @since  2.0.0
     */
    public function stop()
    {
		// bail out if we don't have an active session
		if ( ! $this->started)
		{
			return false;
		}

		// write the data in the session
		$this->write();

		// mark the session as stopped
		$this->started = false;

		// do some garbage collection
		if (mt_rand(0,100) < $this->config['db']['gc_probability'])
		{
			// delete expired session records
			$expired = time() - $this->expiration;
			$this->db->delete($this->config['db']['table'])
				->where('updated_at', '<', $expired)
				->execute();
		}

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
     *
	 * @since  2.0.0
     */
    public function destroy()
    {
		// we need to have a session started
		if ($this->started)
		{
			// mark the session as stopped
			$this->started = false;

			// reset the session containers
			$this->manager->reset();

			// delete the session data from the store
			$this->db->delete($this->config['db']['table'])
				->where('session_id', $this->sessionId)
				->execute();

			// delete the session cookie
			return $this->deleteCookie($this->name);
		}

		// session was not started
		return false;
	}
}
