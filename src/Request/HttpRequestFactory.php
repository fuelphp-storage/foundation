<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2017 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Request;

use UnexpectedValueException;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Temporary request factory until fuel has a http message implementation
 *
 * @package Fuel\Foundation\Request
 */
class HttpRequestFactory extends ServerRequestFactory
{
	public static function fromGlobals(
		array $server = null,
		array $query = null,
		array $body = null,
		array $cookies = null,
		array $files = null
	) {
		$server  = static::normalizeServer($server ?: $_SERVER);
		$files   = static::normalizeFiles($files ?: $_FILES);
		$headers = static::marshalHeaders($server);

		return new Http(
			$server,
			$files,
			static::marshalUriFromServer($server, $headers),
			static::get('REQUEST_METHOD', $server, 'GET'),
			'php://input',
			$headers,
			$cookies ?: $_COOKIE,
			$query ?: $_GET,
			$body ?: $_POST,
			static::marshalProtocolVersion($server)
		);

	}

	private static function marshalProtocolVersion(array $server)
	{
		if (! isset($server['SERVER_PROTOCOL'])) {
			return '1.1';
		}

		if (! preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
			throw new UnexpectedValueException(sprintf(
				'Unrecognized protocol version (%s)',
				$server['SERVER_PROTOCOL']
			));
		}

		return $matches['version'];
	}
}
