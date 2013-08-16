<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Facades;

/**
 * Markdown Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Markdown
{
	/**
	 * @var  MarkdownExtra_Parser  The MD parser instance
	 */
	protected static $parser = null;

	/**
	 * Runs the given text through the Markdown parser.
	 *
	 * @param   string  Text to parse
	 * @return  string
	 */
	public static function parse($text)
	{
		return static::getInstance()->transform($text);
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// setup an instance if needed
		if ( ! static::$parser)
		{
			if (class_exists('dflydev\markdown\MarkdownExtraParser'))
			{
				static::$parser = new \dflydev\markdown\MarkdownExtraParser();
			}
			else
			{
				throw new \RuntimeException('Unable to create a Markdown instance. Did you install the "dflydev\markdown" composer package?');
			}
		}

		return static::$parser;
	}
}
