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


/**
 * Autoloader
 *
 * Custom PSR-0 Autoloader to replace the slow composer one
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Autoloader
{
	/**
	 */
    protected $prefixes = array();

	/**
	 */
    protected $fallbackPaths = array();

	/**
	 */
    protected $classMap = array();

	/**
	 */
    protected $cacheFile;

	/**
	 */
    protected $cacheExpire;

	/**
	 * Import the Composer data and activate the Fuel autoloader
	 */
	public function __construct($composer, $cacheFile = null, $cacheExpire = 86400)
	{
		// Import composer data
		$this->fallbackPaths = $composer->getFallbackDirs();
		$this->prefixes = $composer->getPrefixes();
		$this->classMap = $composer->getClassMap();

		// store the cache filename and expiration
		$this->setCache($cacheFile, $cacheExpire);

		// register outselfs as an autoloader
		$this->register();
	}

	/**
	 * Flush the class map to cache if configured
	 */
	public function __destruct()
	{
		if ($this->cacheFile and is_writable($this->cacheFile))
		{
			if (isset($classMap['FuelExpirationTimestamp']))
			{
				if ($classMap['FuelExpirationTimestamp'] < time())
				{
					unlink($this->cacheFile);
					return;
				}
			}
			else
			{
				// set an expiration if needed
				if ($this->cacheExpire)
				{
					$classMap['FuelExpirationTimestamp'] = time() + $this->cacheExpire;
				}
			}
			file_put_contents($this->cacheFile, '<?php'."\n\n".'return '.var_export($this->classMap, true).';');
		}
	}

	/**
	 * Define the autoloader cache file, and it's expiry
	 */
	public function setCache($cacheFile, $cacheExpire = 86400)
	{
		// if an array is passed, get it's data out
		if (is_array($cacheFile))
		{
			extract($cacheFile);
		}

		// store the passed information
		$this->cacheFile = $cacheFile;
		$this->cacheExpire = $cacheExpire;

		// Load the cached class map if present
		if ($this->cacheFile and file_exists($this->cacheFile))
		{
			$this->addClassMap(include $this->cacheFile);
		}
	}

    /**
     * Registers a set of classes, merging with any others previously set.
     *
     * @param string       $prefix  The classes prefix
     * @param array|string $paths   The location(s) of the classes
     * @param bool         $prepend Prepend the location(s)
     */
    public function add($prefix, $paths, $prepend = false)
    {
        if ( ! $prefix)
        {
            if ($prepend)
            {
                $this->fallbackPaths = array_merge((array) $paths, $this->fallbackPaths);
            }
            else
            {
                $this->fallbackPaths = array_merge($this->fallbackPaths, (array) $paths);
            }
            return;
        }

        if ( ! isset($this->prefixes[$prefix]))
        {
            $this->prefixes[$prefix] = (array) $paths;
            return;
        }

        if ($prepend)
        {
            $this->prefixes[$prefix] = array_merge((array) $paths, $this->prefixes[$prefix]);
        }
        else
        {
            $this->prefixes[$prefix] = array_merge($this->prefixes[$prefix], (array) $paths);
        }
	}

    /**
     * @param array $classMap Class to filename map
     */
    public function addClassMap(array $classMap)
    {
        if ($this->classMap)
        {
            $this->classMap = array_merge($this->classMap, $classMap);
        }
        else
        {
            $this->classMap = $classMap;
        }
    }

	/**
	 *
	 */
    public function getPrefixes()
    {
        return $this->prefixes;
    }

	/**
	 *
	 */
    public function getFallbackDirs()
    {
        return $this->fallbackPaths;
    }

	/**
	 *
	 */
    public function getClassMap()
    {
        return $this->classMap;
    }

	/**
	 * Resolves an class.
	 *
	 * @param   string   $clas  class to load
	 * @return  boolean  whether or not the class is loaded
	 */
	public function resolve($class)
	{
		// classes can't have dots in them, must be a failed DiC identifier lookup
		if (strpos($class, '.') !== false)
		{
			return false;
		}

		// do we have this class in the class map?
		if (isset($this->classMap[$class]))
		{
			if ($this->classMap[$class] === false)
			{
				// it does not exist, don't bother
				return false;
			}

			// match found, load it
			include $this->classMap[$class];
			return true;
		}

		// split namespace and classname
		if (($pos = strrpos($class, '\\')) === false)
		{
			$classPath = '';
			$className = $class;
		}
		else
		{
			$classPath = strtr(substr($class, 0, $pos), '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
			$className = substr($class, $pos + 1);
		}

		// PSR-0 classes always have a classpath
		if ($classPath)
		{
			// not in the class map, look for it
			foreach($this->prefixes as $prefix => $paths)
			{
				if (strpos($class, $prefix) === 0)
				{
					foreach($paths as $path)
					{
						if (file_exists($file = $path.DS.$classPath.$className.'.php'))
						{
							// found, update the classmap and load the file
							$this->classMap[$class] = $file;
							include $file;
							return true;
						}
					}
				}
			}
		}

		// not in the prefix list either, check the fallback dirs
        foreach ($this->fallbackPaths as $path)
        {
			if (file_exists($file = $path.DS.$classPath.$className.'.php'))
			{
				// found, update the classmap and load the file
				$this->classMap[$class] = $file;
				include $file;
				return true;
			}

			if (file_exists($file = $path.DS.$className.'.php'))
			{
				// found, update the classmap and load the file
				$this->classMap[$class] = $file;
				include $file;
				return true;
			}
        }

		// we can't find this class, update the classmap so we don't look for it again
		$this->classMap[$class] = false;
		return false;
	}

	/**
	 * Registers the autoloader function.
	 *
	 * @param   bool    $placement  register placement, append or prepend
	 * @return  $this
	 */
	public function register($placement = 'append')
	{
		$prepend = ($placement === 'append') ? false : true;
		spl_autoload_register(array($this, 'resolve'), true, $prepend);

		return $this;
	}

	/**
	 * Unregisters the autoloader function.
	 *
	 * @return  $this
	 */
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'resolve'));

		return $this;
	}
}
