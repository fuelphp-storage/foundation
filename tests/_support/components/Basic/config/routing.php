<?php

return [
	'/testroute' => [
		'controller' => 'Basic\Controller\TestController',
		'action' => 'actionIndex',
	],
	'/params/{string:myParam}' => [
		'controller' => 'Basic\Controller\ParamsController',
		'action' => 'actionIndex',
	]
];
