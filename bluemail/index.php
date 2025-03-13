<?php
/**
 * @framework       Miami Framework
 * @version         1.1
 * @author          Miami Team
 * @copyright       Copyright (c) 2017 - 2018.	
 * @license		
 * @link	 
 */

use ma\mfw\registry\Packager as Packager;
use ma\mfw\routing\Router as Router;
use ma\mfw\http\Request as Request;
use ma\mfw\http\Session as Session;

/**
 * @name            Index.php 
 * @description     Acts as a single point of access to the Framework 
 * @package         .
 * @category        Front Controller
 * @author          Miami Team			
 */

# to ensure scripts are not called from outside of the framework 
define("IS_MFW",true);  

# require the main configuration of the framework 
require_once 'configs/init.conf.php';

# require request init configurations ( application init and database , cache ... )
require_once 'configs/request.init.conf.php';

# start the session and release the lock 
Session::start();
Session::releaseLock();

# load the Router class and provide the url + extension
$router = new Router(array(
    "url" => Request::getParameterFromGET('url') != null ? Request::getParameterFromGET('url') : DEFAULT_CONTROLLER . RDS . DEFAULT_ACTION,
    "extension" => Request::getParameterFromGET('extension') != null ? Request::getParameterFromGET('extension') : "html"
));



# store the cache object in the registry
Packager::set("router", $router);

# dispatch the current request
$router->dispatch();