<?php namespace ma\mfw\security
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
    use ma\mfw\core\Base as Base;
    use ma\mfw\www\URL as URL;
    use ma\mfw\http\Response as Response;
    /**
     * @name            Security.class 
     * @description     It's a class that deals with security methods
     * @package		ma\mfw\security
     * @category        Core Class
     * @author		Miami Team			
     */
    class Security extends Base
    {
        /**
         * @name applicationExists
         * @description checks if an application exists
         * @access public
         * @param string $applicationPrefix
         * @return boolean
         */
        public static function applicationExists($applicationPrefix) 
        {
            if(isset($applicationPrefix) && $applicationPrefix != '' && is_dir(APPS_FOLDER. DS . $applicationPrefix . DS))
            {
                return true;
            }
            return false;
        } 
    }
}
