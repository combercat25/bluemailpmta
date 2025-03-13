<?php namespace ma\mfw\www
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
    use ma\mfw\application\Application as Application;
    use ma\mfw\security\Security as Security;
    /**
     * @name            URL.class 
     * @description     It's a class that deals with URL methods
     * @package		ma\mfw\www
     * @category        Word Wide Web Class
     * @author		Miami Team			
     */
    class URL extends Base
    {
        /**
         * @name getBaseURL
         * @description gets the URL of the framework
         * @access static 
         * @return string
         */
        public static function getBaseURL() 
        {
            $protocol = (filter_input(INPUT_SERVER, 'HTTPS') != null) && (filter_input(INPUT_SERVER, 'HTTPS') != 'off') ? 'https://' : 'http://';
            $host = filter_input(INPUT_SERVER, 'HTTP_HOST');
            $projectFolder = defined('PROJECT_FOLDER') && PROJECT_FOLDER != '' ? RDS . PROJECT_FOLDER : '';
            
            return $protocol . $host . $projectFolder;
        }

        /**
         * @name getCurrentApplicationURL
         * @description gets the URL of current application
         * @access static 
         * @return string
         */
        public static function getCurrentApplicationURL() 
        {
            # check if there is a default application defined
            if(defined('DEFAULT_APPLICATION_PREFIX') && DEFAULT_APPLICATION_PREFIX != '' && Security::applicationExists(DEFAULT_APPLICATION_PREFIX))
            {
                $applicationPrefix =  '';
            }
            else
            {
                $applicationPrefix = RDS . Application::getPrefix();
            }
            
            return self::getBaseURL() . $applicationPrefix;
        }

        /**
         * @name getCurrentApplicationSkinURL
         * @description gets the URL of the current application's skins
         * @access static 
         * @return string
         */
        public static function getCurrentApplicationSkinURL() 
        {
             return self::getBaseURL() . RDS . DEFAULT_LAYOUTS_DIRECTORY . RDS . Application::getCurrent()->getSetting('init')->layout;
        }
    }
}