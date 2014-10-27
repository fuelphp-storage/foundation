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

use Fuel\Dependency\Container;
use SplStack;
use Countable;

/**
 *
 */
class Stack implements Countable
{
	/**
	 * @var SplStack
	 */
	protected $stack;

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->stack = new SplStack;
		$this->container = $container;
	}

	/**
	 * Create a new instance and pushes it on the stack.
	 *
	 * @param array $arguments
	 *
	 * @return object
	 */
	public function push($instance)
	{
		$this->stack->push($instance);

		return $instance;
	}

	/**
	 * Pop a instance off the stack and return it
	 *
	 * @return object
	 */
	public function pop()
	{
		if ( ! $this->stack->isEmpty())
		{
			return $this->stack->pop();
		}
	}

	/**
	 * Get the currect/top instance off the stack
	 *
	 * @return object
	 */
	public function top()
	{
		if ( ! $this->stack->isEmpty())
		{
			return $this->stack->top();
		}
	}

	/**
	 * Get the first/bottom instance off the stack
	 *
	 * @return object
	 */
	public function bottom()
	{
		if ( ! $this->stack->isEmpty())
		{
			return $this->stack->bottom();
		}
	}

	/**
	 * Get the number of instances on the stack
	 *
	 * @return integer
	 */
	public function count()
	{
		return count($this->stack);
	}

	/**
	 * Pass all other calls directly on to the stack
	 *
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return call_user_func_array([$this->stack, $name], $args);
	}
}
