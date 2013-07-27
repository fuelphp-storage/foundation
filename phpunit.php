<?php
include './vendor/autoload.php';

/**
 * Alias the facade classes so the tests can run
 */
class_alias('Fuel\Foundation\Facades\Alias', 'Alias', false);
class_alias('Fuel\Foundation\Facades\Application', 'Application', false);
class_alias('Fuel\Foundation\Facades\Composer', 'Composer', false);
class_alias('Fuel\Foundation\Facades\Config', 'Config', false);
class_alias('Fuel\Foundation\Facades\Dependency', 'Dependency', false);
class_alias('Fuel\Foundation\Facades\Environment', 'Environment', false);
class_alias('Fuel\Foundation\Facades\Error', 'Error', false);
class_alias('Fuel\Foundation\Facades\Fuel', 'Fuel', false);
class_alias('Fuel\Foundation\Facades\Input', 'Input', false);
class_alias('Fuel\Foundation\Facades\Package', 'Package', false);
class_alias('Fuel\Foundation\Facades\Request', 'Request', false);
//class_alias('Fuel\Foundation\Facades\Response', 'Response', false);
class_alias('Fuel\Foundation\Facades\Security', 'Security', false);
class_alias('Fuel\Foundation\Facades\View', 'View', false);
