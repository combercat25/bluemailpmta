<?php namespace ma\mfw\libraries
{
    if (!defined('IS_MFW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
    /**
     * @framework       Miami Framework
     * @version         1.1
     * @author          Miami Team
     * @copyright       Copyright (c) 2017 - 2018.	
     * @license		
     * @link	
     */
    use ma\mfw\files\Paths as Paths;
    /**
     * @name            Libraries.class 
     * @description     The main libraries loader class
     * @package		ma\mfw\libraries
     * @category        Helper Class
     * @author		Miami Team			
     */
    class Libraries
    {
        /**
         * @name loadLibrary
         * @description loads a library by it's name
         * @access static
         * @param string $libraryName
         * @return mixed
         */
        public static function loadLibrary($libraryName) 
        {
            if(isset($libraryName))
            {
                $apiPath = Paths::getCurrentApplicationRealPath() . DS . DEFAULT_LIBS_DIRECTORY . DS . strtolower($libraryName) . DS;
                
                if(is_dir($apiPath))
                {
                    require_once $apiPath . $libraryName . '.php';
                }   
            }
        }
    } 
}