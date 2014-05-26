<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Whoops;

use Whoops\Handler\Handler;

/**
 * Handler for error messages in a production environment
 */
class ProductionHandler extends Handler
{
    /**
     * @return int
     */
    public function handle()
    {
		try
		{
			if ($application = \Application::getInstance())
			{
				if ($environment = $application->getEnvironment())
				{
					if ($environment->getName() == 'production')
					{
						// get the exception
						$exception = $this->getException();

						// write the error to the log
						$application->getLog()->error(json_encode(array(
							'type'    => get_class($exception),
							'message' => $exception->getMessage(),
							'file'    => $exception->getFile(),
							'line'    => $exception->getLine()
						)));

						// and bail out of a generic oops
						echo $application->getViewManager()->forge('errors/production');

						return Handler::QUIT;
					}
				}
			}
		}
		catch (\Exception $e)
		{
			// no application loaded, ignore this handler
		}

        return Handler::DONE;
    }
}
