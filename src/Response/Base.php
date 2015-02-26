<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Response;

use Fuel\Foundation\Request\RequestAware;

/**
 * FuelPHP base response class
 *
 * Standardized response on any request initiated
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
abstract class Base implements RequestAware
{
	/**
	 * @var  array  An array of status codes and messages
	 *
	 * @since  1.0.0
	 */
	public static $statuses = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		208 => 'Already Reported',
		226 => 'IM Used',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		418 => 'I\'m a Teapot',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		428 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		509 => 'Bandwidth Limit Exceeded',
		510 => 'Not Extended',
		511 => 'Network Authentication Required',
	);

	/**
	 * @var  int  The HTTP status code
	 *
	 * @since  1.0.0
	 */
	public $status = 200;

	/**
	 * @var  array  An array of headers
	 *
	 * @since  1.0.0
	 */
	protected $headers = array();

	/**
	 * @var  string  The content of the response
	 *
	 * @since  1.0.0
	 */
	protected $content;

	/**
	 * @var  string  mime type of the return body
	 */
	protected $contentType = 'text/html';

	/**
	 * @var  string  The charset used for the response
	 *
	 * @since  2.0.0
	 */
	protected $charset;

	/**
	 * Constructor
	 *
	 * @param  string  $content
	 * @param  int     $status
	 * @param  array   $headers
	 *
	 * @since  1.0.0
	 */
	public function __construct($content = '', $status = 200, array $headers = array())
	{
		// process the passed data
		$this->setContent($content);
		$this->setStatusCode($status);
		foreach ($headers as $k => $v)
		{
			$this->setHeader($k, $v);
		}
	}

	/**
	 * Sets this responses' Request instance
	 *
	 * @param  Request  $request
	 *
	 * @since  1.1.0
	 */
	public function setRequest($request)
	{
		$this->request = $request;
	}

	/**
	 * Sets the response status code
	 *
	 * @param   int  $status  The status code
	 *
	 * @return  Response
	 *
	 * @since  1.0.0
	 */
	public function setStatusCode($status = 200)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * Adds a header to the queue
	 *
	 * @param   string  $name     The header name
	 * @param   string  $value    The header value
	 * @param   bool    $replace  Whether to replace existing value for the header
	 *
	 * @return  Response
	 *
	 * @since  1.0.0
	 */
	public function setHeader($name, $value, $replace = true)
	{
		if ($replace or ! isset($this->headers[$name]))
		{
			$this->headers[$name] = array($value);
		}
		else
		{
			array_push($this->headers[$name], $value);
		}

		return $this;
	}

	/**
	 * Gets header information from the queue
	 *
	 * @param   string  $name     The header name, or null for all headers
	 * @param   mixed   $default  Default return when header not set
	 * @param   bool    $all      Whether to return all or just the last
	 *
	 * @return  array|string
	 *
	 * @since  2.0.0
	 */
	public function getHeader($name = null, $default = null, $all = false)
	{
		if (func_num_args() == 0)
		{
			return $this->headers;
		}
		elseif ( ! isset($this->headers[$name]))
		{
			return $default;
		}

		return $all ? $this->headers[$name] : end($this->headers[$name]);
	}

	/**
	 * Sets the body content for the response
	 *
	 * @param   string  $content   The response content
	 * @param   string  $mimeType  mime type for the content
	 *
	 * @return  Response
	 *
	 * @since  2.0.0
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * Get the body content for the response
	 *
	 * @return  mixed
	 *
	 * @since  2.0.0
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Sets the body content for the response
	 *
	 * @param   string  $contentType  The response content's mime type
	 *
	 * @return  Response
	 *
	 * @since  2.0.0
	 */
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;

		return $this;
	}

	/**
	 * Get the mime type for the response
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getContentType()
	{
		return $this->contentType;
	}

	/**
	 * Sets the response charset.
	 *
	 * @param   string  $charset  Character set
	 *
	 * @return  Response
	 *
	 * @since  2.0.0
	 */
	public function setCharset($charset)
	{
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Retrieves the response charset.
	 *
	 * @return  string  Character set
	 *
	 * @since  2.0.0
	 */
	public function getCharset()
	{
		return $this->charset;
	}

	/**
	 * Output both the content and the headers
	 *
	 * @return  Response
	 *
	 * @since  2.0.0
	 */
	public function send()
	{
		$this->sendHeaders();
		$this->sendContent();

		if (function_exists('fastcgi_finish_request'))
		{
			fastcgi_finish_request();
		}

		return $this;
	}

	/**
	 * Sends the headers if they haven't already been sent.
	 *
	 * @return  Response
	 *
	 * @throws  \RuntimeException
	 *
	 * @since  1.0.0
	 */
	public function sendHeaders()
	{
		$input = $this->request->getInput();

		if (headers_sent())
		{
			throw new \RuntimeException('FOU-023: Cannot send headers, headers already sent.');
		}

		// Send the protocol/status line first, FCGI servers need different status header
		if ($input->getServer('FCGI_SERVER_VERSION'))
		{
			$message = isset(static::$statuses[$this->status])
				? static::$statuses[$this->status]
				: '(unknown statuscode)';
			header('Status: '.$this->status.' '.$message);
		}
		else
		{
			$protocol = $input->getServer('SERVER_PROTOCOL') ? $input->getServer('SERVER_PROTOCOL') : 'HTTP/1.1';
			header($protocol.' '.$this->status.' '.static::$statuses[$this->status]);
		}

		if ( ! isset($this->headers['Content-Type']))
		{
			$this->setHeader('Content-Type', $this->contentType.'; charset='.$this->charset);
		}

		foreach ($this->headers as $name => $values)
		{
			foreach ($values as $value)
			{
				// Create the header and send it
				is_string($name) and $value = "{$name}: {$value}";
				header($value, true);
			}
		}

		return $this;
	}

	/**
	 * Send the content to the output
	 *
	 * @return  Response
	 *
	 * @since  2.0.0
	 */
	abstract public function sendContent();

	/**
	 * Returns the body as a string.
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	abstract public function __toString();
}
