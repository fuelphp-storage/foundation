<?php
/**
 * easyProperty.com
 *
 * @link      www.easyproperty.com
 * @copyright Copyright (c) 2016 easyproperty.com
 * @license   Proprietary
 */

namespace Basic\Controller;

use Fuel\Foundation\Controller\AbstractController;

class ParamsController extends AbstractController
{
	public function actionIndex()
	{
		return 'got: ' . $this->getRouteParam('myParam');
	}
}
