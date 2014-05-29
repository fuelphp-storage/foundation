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

use Fuel\Config\Container;

/**
 * Input
 *
 * Keeps the HTTP input for a request or the application as a whole.
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Input
{
	/**
	 * @var  InjectionFactory  this applications object factory
	 *
	 * @since  2.0.0
	 */
	protected $factory;

	/**
	 * @var  Input  parent Input object to fall back on
	 *
	 * @since  2.0.0
	 */
	protected $parent;

	/**
	 * @var  Fuel\Config\Container  Configuration container
	 *
	 * @since  2.0.0
	 */
	protected $config;

	/**
	 * @var  null|string  The URI that was detected automatically
	 *
	 * @since  1.0.0
	 */
	protected $detectedUri = null;

	/**
	 * @var  null|string  The URI extension that was detected automatically
	 *
	 * @since  1.1.0
	 */
	protected $detectedExt = null;

	/**
	 * @var  string  HTTP method used
	 *
	 * @since  1.0.0
	 */
	protected $httpMethod = null;

	/**
	 * @var  Fuel\Common\DataContainer  server variables
	 *
	 * @since  2.0.0
	 */
	protected $server;

	/**
	 * @var  Fuel\Common\DataContainer  The vars from the HTTP method (GET, POST, PUT, DELETE)
	 *
	 * @since  2.0.0
	 */
	protected $param;

	/**
	 * @var  Fuel\Common\DataContainer  All of the variables from the URL (= GET when input method is GET)
	 *
	 * @since  2.0.0
	 */
	protected $query;

	/**
	 * @var  Fuel\Common\DataContainer  All of the variables from the CLI
	 *
	 * @since  2.0.0
	 */
	protected $cli;

	/**
	 * @var  Fuel\Common\CookieJar  Cookie
	 *
	 * @since  2.0.0
	 */
	protected $cookie;

	/**
	 * @var  Fuel\Common\DataContainer
	 *
	 * @since  2.0.0
	 */
	protected $files;

	/**
	 * @var  string  the raw data from php://input
	 *
	 * @since  2.0.0
	 */
	protected $requestBody;

	/**
	 * Constructor
	 *
	 * @param  array             $inputVars  HTTP input overwrites
	 * @param  Input             $parent     whether this input object falls back to another one
	 * @param  InjectionFactory  $factory    factory object to construct external objects
	 *
	 * @since  2.0.0
	 */
	public function __construct(array $inputVars = array(), $parent = null, InjectionFactory $factory)
	{
		// store the applications object factory
		$this->factory = $factory;

		// assign the parent object if given
		$this->parent = $parent instanceof self ? $parent : null;

		// create the data containers
		$this->server  = $this->factory->createDataContainer();
		$this->param   = $this->factory->createDataContainer();
		$this->query   = $this->factory->createDataContainer();
		$this->files   = $this->factory->createDataContainer();
		$this->cli     = $this->factory->createDataContainer();
		$this->cookie  = $this->factory->createCookieJar();

		// link our containers to their parents
		if ($this->parent)
		{
			$this->server->setParent($this->parent->getServer());
			$this->param->setParent($this->parent->getParam());
			$this->query->setParent($this->parent->getQuery());
			$this->files->setParent($this->parent->getFile());
			$this->cli->setParent($this->parent->getCli());
			$this->cookie->setParent($this->parent->getCookie());
		}

		// load the object with any data passed
		$this->load($inputVars);

		// store the http method if one was passed. if not get it

		isset($inputVars['method'])
			? $this->httpMethod = $inputVars['method']
			: $this->httpMethod = $this->getServer('HTTP_X_HTTP_METHOD_OVERRIDE', $this->getServer('REQUEST_METHOD')) ?: null;
		$this->httpMethod and $this->httpMethod = strtoupper($this->httpMethod);

	}

	/**
	 * Load input variables in bulk
	 *
	 * @param   array  $input  multi-dimensional array of input variables
	 * @return  Input
	 *
	 * @since  2.0.0
	 */
	public function load(array $input = array())
	{
		// load the data into the containers
		if (isset($input['server']))
		{
			$this->server->merge($input['server']);
		}
		if (isset($input['param']))
		{
			$this->param->merge($input['param']);
		}
		if (isset($input['query']))
		{
			$this->query->merge($input['query']);
		}
		if (isset($input['cookie']))
		{
			$this->cookie->merge($input['cookie']);
		}
		if (isset($input['files']))
		{
			$this->files->merge($input['files']);
		}
		if (isset($input['cli']))
		{
			$this->cli->merge($input['cli']);
		}

		// update the request body if one was passed
		$this->requestBody = isset($input['requestBody']) ? $inputVars['requestBody'] : array();

		return $this;
	}

	/**
	 * Inserts global input variables into this object
	 *
	 * @param   array  $include  list of which variables to insert, empty for all
	 * @return  Input
	 *
	 * @since  2.0.0
	 */
	public function fromGlobals(array $include = array())
	{
		$vars = array('server', 'param', 'uriVars', 'cookie', 'files');
		$vars = ! $include ? $vars : array_intersect($include, $vars);

		if (in_array('server', $vars))
		{
			$this->server->setContents($_SERVER);

			$this->parseCli();
		}

		if ( ! isset($this->httpMethod))
		{
			if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']))
			{
				$this->httpMethod = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
			}
			elseif (isset($_SERVER['REQUEST_METHOD']))
			{
				$this->httpMethod = $_SERVER['REQUEST_METHOD'];
			}
		}

		if (in_array('param', $vars))
		{
			switch ($this->httpMethod)
			{
				case 'DELETE':
				case 'PUT':
					parse_str($this->requestBody(), $param);
					$this->param->setContents($param);
					break;
				case 'POST':
					$this->param->setContents($_POST);
					break;
				case 'GET':
				default:
					$this->param->setContents($_GET);
					break;
			}
		}

		// Support JSON & simpleXML through CONTENT_TYPE header, any others are considered application logic
		if ($this->getServer('CONTENT_TYPE') == 'application/json')
		{
			$this->param->setContents(((array) json_decode($this->requestBody(), true)));
		}
		elseif ($this->getServer('CONTENT_TYPE') == 'text/xml')
		{
			$xmlObj = simplexml_load_string($this->requestBody(), 'SimpleXMLElement', LIBXML_NOCDATA);
			$toArr = function ($xmlObj, $_func)
			{
				$arr = array();
				foreach ($xmlObj as $key => $val)
				{
					$arr[$key] = (is_array($val) or is_object($val)) ? $_func($val) : $val;
				}
				return $arr;
			};
			$this->param->setContents($toArr($xmlObj, $toArr));
		}

		in_array('uriVars', $vars)
			and $this->query->setContents($_GET);

		in_array('files', $vars)
			and $this->files->setContents($_FILES);

		in_array('cookie', $vars)
			and $this->cookie->merge($_COOKIE);

		return $this;
	}

	/**
	 * Sets a parent Input object to fall back on
	 *
	 * @param   Input  $parent
	 * @return  Input
	 */
	public function setParent(Input $parent)
	{
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Sets the configuration container to be used by this instance
	 *
	 * @param   Fuel\Config\Container  $config
	 * @return  Input
	 */
	public function setConfig(Container $config)
	{
		$this->config = $config;
		return $this;
	}

	/**
	 * Detects and returns the current URI based on a number of different server
	 * variables.
	 *
	 * @return  string
	 * @throws  \RuntimeException
	 *
	 * @since  1.1.0
	 */
	public function getPathInfo()
	{
		if ($this->detectedUri !== null)
		{
			return $this->detectedUri;
		}

		// We want to use PATH_INFO if we can.
		if (isset($this->server['PATH_INFO']))
		{
			$uri = $this->server['PATH_INFO'];
		}

		// Only use ORIG_PATH_INFO if it contains the path
		elseif (isset($this->server['ORIG_PATH_INFO'])
			and ($path = str_replace($this->server['SCRIPT_NAME'], '', $this->server['ORIG_PATH_INFO'])) != '')
		{
			$uri = $path;
		}
		else
		{
			// Fall back to parsing the REQUEST URI
			if (isset($this->server['REQUEST_URI']))
			{
				$uri = strpos($this->server['SCRIPT_NAME'], $this->server['REQUEST_URI']) !== 0 ? $this->server['REQUEST_URI'] : '';
			}

			// Or if that doesn't exist, was this a CLi request?
			elseif ($this->cli->count() > 1)
			{
				$uri = $this->cli[1];
			}
			else
			{
				throw new \Exception('FOU-002: Unable to detect the URI.');
			}

			// Remove the base URL from the URI
			$base_url = parse_url($this->getBaseUrl(), PHP_URL_PATH);
			if ($uri != '' and strncmp($uri, $base_url, strlen($base_url)) === 0)
			{
				$uri = substr($uri, strlen($base_url));
			}

			// If we are using an index file (not mod_rewrite) then remove it
			$indexFile = $this->config->get('indexFile', null);
			if ($indexFile and strncmp($uri, $indexFile, strlen($indexFile)) === 0)
			{
				$uri = substr($uri, strlen($indexFile));
			}

			// When index.php? is used and the config is set wrong, lets just
			// be nice and help them out.
			if ($indexFile and strncmp($uri, '?/', 2) === 0)
			{
				$uri = substr($uri, 1);
			}

			// Lets split the URI up in case it contains a ?.  This would
			// indicate the server requires 'index.php?' and that mod_rewrite
			// is not being used.
			preg_match('#(.*?)\?(.*)#i', $uri, $matches);

			// If there are matches then lets set set everything correctly
			if ( ! empty($matches))
			{
				$uri = $matches[1];
				$this->server->setContents(array('QUERY_STRING' => $matches[2]), true);
				parse_str($matches[2], $query);
				$this->query->setContents($query, true);
			}
		}

		// Deal with any trailing dots
		$uri = rtrim($uri, '.');

		// Do we have a URI and does it not end on a slash?
		if ($uri and substr($uri, -1) !== '/')
		{
			// Strip the defined url suffix from the uri if needed
			$ext = strrchr($uri, '.');
			$path = $ext === false ? $uri : substr($uri, 0, -strlen($ext));

			// Did we detect something that looks like an extension?
			if ( ! empty($ext))
			{
				// if it has a slash in it, it's a URI segment with a dot in it
				if (strpos($ext,'/') === false)
				{
					$this->detectedExt = ltrim($ext, '.');
// TODO: strip extension?
				}
			}
		}

		return ($this->detectedUri = $uri);
	}

	/**
	 * Detects and returns the current URI extension
	 *
	 * @return  string
	 *
	 * @since  1.1.0
	 */
	public function getExtension()
	{
		if ( ! isset($this->detectedExt))
		{
			$this->getPathInfo();
		}
		return $this->detectedExt;
	}

	/**
	 * Get the public ip address of the user.
	 *
	 * @param   string  $default
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function getIp($default = '0.0.0.0')
	{
		if ($this->getServer('REMOTE_ADDR') !== null)
		{
			return $this->getServer('REMOTE_ADDR');
		}

		// detection failed, return the default
		return result($default);
	}

	/**
	 * Get the real ip address of the user.  Even if they are using a proxy.
	 *
	 * @param   string  @default  default return value when no IP is detected
	 * @return  string  the real ip address of the user
	 *
	 * @since  1.0.0
	 */
	public function getRealIp($default = '0.0.0.0')
	{
		if ($this->getServer('HTTP_X_CLUSTER_CLIENT_IP') !== null)
		{
			return $this->getServer('HTTP_X_CLUSTER_CLIENT_IP');
		}
		elseif ($this->getServer('HTTP_X_FORWARDED_FOR') !== null)
		{
			return $this->getServer('HTTP_X_FORWARDED_FOR');
		}
		elseif ($this->getServer('HTTP_CLIENT_IP') !== null)
		{
			return $this->getServer('HTTP_CLIENT_IP');
		}
		elseif ($this->getServer('REMOTE_ADDR') !== null)
		{
			return $this->getServer('REMOTE_ADDR');
		}

		// detection failed, return the default
		return result($default);
	}

	/**
	 * Returns the scheme that the request was made with
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function getScheme()
	{
		if (( ! is_null($this->getServer('HTTPS')) and $this->getServer('HTTPS') != 'off')
			or (is_null($this->getServer('HTTPS')) and $this->getServer('SERVER_PORT') == 443))
		{
			return 'https';
		}

		return 'http';
	}

	/**
	 * Returns whether this is an AJAX request or not
	 *
	 * @return  bool
	 *
	 * @since  1.0.0
	 */
	public function isAjax()
	{
		return ($this->getServer('HTTP_X_REQUESTED_WITH') !== null)
			and strtolower($this->getServer('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
	}

	/**
	 * Returns the referrer
	 *
	 * @param   string  $default
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function getReferrer($default = '')
	{
		return $this->getServer('HTTP_REFERER', $default);
	}

	/**
	 * Returns the input method used (GET, POST, DELETE, etc.)
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function getMethod()
	{
		return $this->httpMethod ?: 'GET';
	}

	/**
	 * Returns the user agent
	 *
	 * @param   string  $default
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function getUserAgent($default = '')
	{
		return $this->getServer('HTTP_USER_AGENT', $default);
	}

	/**
	 * Parses the CLI parameters from $_SERVER['argv']
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	protected function parseCli()
	{
		$cli = isset($this->server['argv']) ? $this->server['argv'] : array();
		foreach ($cli as $i => $arg)
		{
			$arg = explode('=', $arg);
			$cli[$i] = reset($arg);

			if (count($arg) > 1 or strncmp(reset($arg), '-', 1) === 0)
			{
				$cli[ltrim(reset($arg), '-')] = isset($arg[1]) ? $arg[1] : true;
			}
		}
		$this->cli->merge($cli);
	}

	/**
	 * Returns the raw data from php://input
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function requestBody()
	{
		if (is_null($this->requestBody))
		{
			if (isset($this->parent))
			{
				return $this->parent->requestBody();
			}

			$this->requestBody = file_get_contents('php://input');
		}

		return $this->requestBody;
	}

	/**
	 * Fetch an item from the FILE array
	 *
	 * @param   string  $index    The index key
	 * @param   mixed   $default  The default value
	 * @return  string|array
	 *
	 * @since  1.0.0
	 */
	public function getFile($index = null, $default = null)
	{
		if (func_num_args() === 0)
		{
			return $this->files;
		}

		return $this->files->get($index, $default);
	}

	/**
	 * Fetch an item from the URI query string
	 *
	 * @param   string  $index    The index key
	 * @param   mixed   $default  The default value
	 * @return  string|array
	 *
	 * @since  2.0.0
	 */
	public function getQuery($index = null, $default = null)
	{
		if (func_num_args() === 0)
		{
			return $this->query;
		}

		return $this->query->get($index, $default);
	}

	/**
	 * Fetch a param from the CLI input
	 *
	 * @param   string  $index    The index key
	 * @param   mixed   $default  The default value
	 * @return  string|array
	 *
	 * @since  2.0.0
	 */
	public function getCli($index = null, $default = null)
	{
		if (func_num_args() === 0)
		{
			return $this->cli;
		}

		return $this->cli->get($index, $default);
	}

	/**
	 * Fetch an item from the input
	 *
	 * @param   string  $index    The index key
	 * @param   mixed   $default  The default value
	 * @return  string|array
	 *
	 * @since  1.1.0
	 */
	public function getParam($index = null, $default = null)
	{
		if (func_num_args() === 0)
		{
			return $this->param;
		}

		return $this->param->get($index, $default);
	}

	/**
	 * Fetch an item from the COOKIE array
	 *
	 * @param    string  $index    The index key
	 * @param    mixed   $default  The default value
	 * @return   string|array
	 *
	 * @since  1.0.0
	 */
	public function getCookie($index = null, $default = null)
	{
		if (func_num_args() === 0)
		{
			return $this->cookie;
		}

		return $this->cookie->get($index, $default);
	}

	/**
	 * Fetch an item from the SERVER array
	 *
	 * @param   string  $index    The index key
	 * @param   mixed   $default  The default value
	 * @return  string|array
	 *
	 * @since  1.0.0
	 */
	public function getServer($index = null, $default = null)
	{
		if (func_num_args() === 0)
		{
			return $this->server;
		}

		return $this->server->get(strtoupper($index), $default);
	}

	/**
	 * PHP magic method to allow access to protected properties
	 *
	 * @param   string  $prop
	 * @return  mixed
	 * @throws  \OutOfBoundsException
	 *
	 * @since  2.0.0
	 */
	public function __get($prop)
	{
		if (method_exists($this, $method = 'get'.ucfirst($prop)))
		{
			return $this->{$method}();
		}

		throw new \OutOfBoundsException('FOU-003: Property ['.$prop.'] not set on Input.');
	}

	/**
	 * Generates a base url.
	 *
	 * @return  string  the base url
	 *
	 * @since  2.0.0
	 */
	public function getBaseUrl()
	{
		$baseUrl = '';
		if (isset($this->server['HTTP_HOST']))
		{
			$baseUrl .= $this->getScheme().'://'.$this->server['HTTP_HOST'];
		}
		if (isset($this->server['SCRIPT_NAME']))
		{
			$baseUrl .= str_replace('\\', '/', dirname($this->server['SCRIPT_NAME']));

			// Add a slash if it is missing
			$baseUrl = rtrim($baseUrl, '/').'/';
		}

		return $baseUrl;
	}
}
