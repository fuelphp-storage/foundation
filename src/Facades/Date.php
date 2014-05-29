<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Facades;

use Fuel\Common\Date as CommonDate;
use DateTimeInterface;

/**
 * Date Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Date extends Base
{
	/**
	 * Creates an instance of the Date class
	 *
	 * @param  string|int           $time      date/time as a UNIX timestamp or a string
	 * @param  string|DateTimeZone  $timezone  timezone $time is in
	 * @param  array                $config    any custom configuration for this object
	 *
	 * @return  Date
	 */
	public static function forge()
	{
		return static::getDic()->resolve('date', func_get_args());
	}

	/**
	 * Returns a Date object with the current time, corrected with gmtOffset
	 *
	 * @return  Date
	 */
	public static function time($timezone = null)
	{
		return static::forge(null, $timezone);
	}

	/**
	 * Returns the warnings and errors from the last parsing operation
	 *
	 * @return  array  array of warnings and errors found while parsing a date/time string.
	 */
	public static function getLastErrors()
	{
		return CommonDate::getLastErrors();
	}

	/**
	 * Sets the default timezone, the current display timezone of the application
	 *
	 * @return  DateTimeZone
	 */
	public static function defaultTimezone($timezone = null)
	{
		return CommonDate::defaultTimezone($timezone);
	}

	/**
	 * Returns the number of days in the requested month
	 *
	 * @param   int  month as a number (1-12)
	 * @param   int  the year, leave empty for current
	 *
	 * @throws  InvalidArgumentException  if the month given is out of range
	 *
	 * @return  int  the number of days in the month
	 */
	public static function daysInMonth($month, $year = null)
	{
		static $days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

		$year  = ! empty($year) ? (int) $year : (int) date('Y');
		$month = (int) $month;

		if ($month < 1 or $month > 12)
		{
			throw new \InvalidArgumentException('FOU-016: Invalid input for month given.');
		}
		elseif ($month == 2)
		{
			if ($year % 400 == 0 or ($year % 4 == 0 and $year % 100 != 0))
			{
				return 29;
			}
		}

		return $days_in_month[$month-1];
	}

	/**
	 * Returns the time ago
	 *
	 * @param  int|DateTimeInterface   timestamp or something implementing DateTimeInterface
	 * @param  int|DateTimeInterface   timestamp or something implementing DateTimeInterface
	 * @param  string                  Unit to return the result in
	 *
	 * @return  bool|string  Time ago
	 */
	public static function timeAgo($time, $from = null, $unit = null)
	{
		static $periods = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade');
		static $lengths = array(60, 60, 24, 7, 4.35, 12, 10);

		// storage for the result
		$result = '';

		// do we have a to-time?
		if ($time)
		{
			if ( ! $time instanceOf DateTimeInterface)
			{
				if ( ! $time = static::forge($time))
				{
					return false;
				}
			}
		}

		// do we have a from-time?
		if ($from)
		{
			if ( ! $from instanceOf DateTimeInterface)
			{
				if ( ! $from = static::forge($from))
				{
					return false;
				}
			}
		}
		else
		{
			// if no from-time, default to now
			$from = static::time();
		}

		$difference = $from->getTimestamp() - $time->getTimestamp();

		for ($j = 0; isset($lengths[$j]) and $difference >= $lengths[$j] and (empty($unit) or $unit != $periods[$j]); $j++)
		{
			$difference /= $lengths[$j];
		}

        $difference = round($difference);

		$period = $difference == 1 ? $periods[$j] : \Inflector::pluralize($periods[$j]);

		\Lang::load('date', true);

		$result = \Lang::get('date.text', array(
			'time' => \Lang::get('date.'.$period, array('t' => $difference))
		));

		return $result;
	}

	/**
	 * Get an object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::forge();
	}
}
