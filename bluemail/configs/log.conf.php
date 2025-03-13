<?php if (!defined('IS_MFW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
/**
 * @framework       Miami Framework
 * @version         1.1
 * @author          Miami Team
 * @copyright       Copyright (c) 2017 - 2018.	
 * @license		
 * @link	
 * @name            log.conf.php 
 * @description     Logging configuration file that contains some logging constants to configure the logging mechanism
 * @package         .
 * @category        Config File
 * @author          Miami Team			
 */

# file paths of all our log files 
define('ERROR_FILE',ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . DEFAULT_LOGS_DIRECTORY . DS . 'error.log');
define('DEBUG_FILE',ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . DEFAULT_LOGS_DIRECTORY . DS . 'debug.log');

# Max size that the log file can handle ( if it's more than we create a new file ) 
define('MAX_RECYCLING_SIZE','1.5');

# some log codes 
define('E_EXIT' , 1);
define('E_DEBUG' , 100);
define('E_INFO' , 101);

