<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation;

/**
 * Uri class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Uri
{
	/**
	 * @var  string  The URI string
	 */
	protected $uri = '';

	/**
	 * @var  array  The URI segments
	 */
	protected $segments = '';

	/**
	 * Constructor
	 * the segments.
	 *
	 * @param   string  The URI
	 * @return  void
	 */
	public function __construct($uri)
	{
		$this->uri = trim($uri, '/');

		if (empty($this->uri))
		{
			$this->segments = array();
		}
		else
		{
			$this->segments = explode('/', $this->uri);
		}
	}

	/**
	 * Returns the URI string
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return $this->get();
	}

	/**
	 * Returns the full URI string
	 *
	 * @return  string  The URI string
	 */
	public function get()
	{
		return $this->uri;
	}

	/**
	 * Get the specified URI segment, return default if it doesn't exist.
	 *
	 * Segment index is 1 based, not 0 based
	 *
	 * @param   string  $segment  The 1-based segment index
	 * @param   mixed   $default  The default value
	 * @return  mixed
	 */
	public function getSegment($segment, $default = null)
	{
		if (isset($this->segments[$segment - 1]))
		{
			return $this->segments[$segment - 1];
		}

		return result($default);
	}

	/**
	 * Returns all the URI segments
	 *
	 * @return  array  The URI segments
	 */
	public function getSegments()
	{
		return $this->segments;
	}

	/**
	 * Replace all * wildcards in a URI by the current segment in that location
	 *
	 * @return  string
	 */
	public function replaceSegments($url)
	{
		// get the path from the url
		$parts = parse_url($url);

		// explode it in it's segments
		$segments = explode('/', trim($parts['path'], '/'));

		// fetch any segments needed
		$wildcards = 0;
		foreach ($segments as $index => &$segment)
		{
			if (strpos($segment, '*') !== false)
			{
				$wildcards++;
				if (($new = $this->getSegment($index+1)) === null)
				{
					throw new \OutofBoundsException('FOU-001: Segment replace on ['.$url.'] failed. No segment exists for wildcard ['.$wildcards.'].');
				}
				$segment = str_replace('*', $new, $segment);
			}
		}

		// re-assemble the path
		$parts['path'] = '/'.implode('/', $segments);

		// and rebuild the url with the new path
		if (empty($parts['host']))
		{
			// if a relative url was given, fake a host so we can remove it after building
			$url = substr(http_build_url('http://__removethis__/', $parts), 22);
		}
		else
		{
			// a hostname was present, just rebuild it
			$url = http_build_url('', $parts);
		}

		// return the newly constructed url
		return $url;
	}

	/**
	 * Converts the current URI segments to an associative array.  If
	 * the URI has an odd number of segments, an empty value will be added.
	 *
	 * @param  int  segment number to start from. default value is the first segment
	 * @return  array  the assoc array
	 */
	public function toAssoc($start = 1)
	{
		$segments = array_slice($this->getSegments(), ($start - 1));
		count($segments) % 2 and $segments[] = null;

		return \Arr::toAssoc($segments);
	}
}
