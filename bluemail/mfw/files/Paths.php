<?php namespace ma\mfw\files
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
    use ma\mfw\registry\Packager as Packager;
    use ma\mfw\application\Application as Application;
    /**
     * @name            Paths.class 
     * @description     It's a class that deals with paths methods
     * @package		ma\mfw\files
     * @category        Helper Class
     * @author		Miami Team			
     */
    class Paths extends Base
    {
        /**
         * @name getCurrentApplicationRealPath
         * @description gets the real path of the current application's folder
         * @access static
         * @return string
         */
        public static function getCurrentApplicationRealPath()
        {
            $projectFolder = defined('PROJECT_FOLDER') && PROJECT_FOLDER != '' ? DS . PROJECT_FOLDER : '';
            return ROOT_PATH . $projectFolder . DS . DEFAULT_APPS_DIRECTORY . DS . Application::getPrefix(); 
        }

        /**
         * @name getCurrentApplicationSkinRealPath
         * @description gets the real path of the current application's skin folder
         * @access static
         * @return string
         */
        public static function getCurrentApplicationSkinRealPath()
        {
            return self::getCurrentApplicationRealPath() . DS . DEFAULT_SKINS_DIRECTORY . DS . Packager::get('skin'); 
        } 
    }
}