<?php namespace ma\mfw\application
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
    use ma\mfw\globals\Server as Server;
    use ma\mfw\security\Security as Security;
    /**
     * @name            Application.class 
     * @description     The main application class
     * @package		ma\mfw\application
     * @category        Core Class
     * @author		Miami Team			
     */
    class Application extends Base
    {
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_name;
       
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_folder;

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_prefix;

        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_settings = array();

        /**
         * @name addSetting
         * @description stores a setting of the application
         * @access public
         * @param string $key
         * @param mixed $config
         * @return
         */
        public function addSetting($key,$config) 
        {
            $this->_settings[$key] = $config;
        }

        /**
         * @name getSetting
         * @description gets setting by a given key
         * @access public
         * @param string $key
         * @return mixed
         */
        public function getSetting($key) 
        {
            return isset($this->_settings[$key]) ? $this->_settings[$key] : NULL;
        }

        /**
         * @name getSettings
         * @description gets all settings
         * @access public
         * @return array
         */
        public function getSettings() 
        {
            return $this->_settings;
        }
       
        /**
         * @name getCurrent
         * @description gets the current application
         * @access static
         * @return Application
         */
        public static function getCurrent() 
        {
             return Packager::get('application');
        }

        /**
         * @name getPrefix
         * @description gets the prefix(and also folder name) of the current application
         * @access static
         * @return string
         */
        public static function getPrefix() 
        {
            $urlRaw = Server::get('REQUEST_URI');
            $urlRawParts = preg_split('/[\/?&=]+/', $urlRaw, -1, PREG_SPLIT_NO_EMPTY);
            
            # check if there is a default application defined
            if(defined('DEFAULT_APPLICATION_PREFIX') && DEFAULT_APPLICATION_PREFIX != '' && Security::applicationExists(DEFAULT_APPLICATION_PREFIX))
            {
                return DEFAULT_APPLICATION_PREFIX;
            }

            if(is_array($urlRawParts) && count($urlRawParts))
            {
                if(PROJECT_FOLDER != '')
                {
                    return $urlRawParts[1];
                }
                else
                {
                    return $urlRawParts[0];
                }
            }

            # in case of nothing found
            return '';
        }    
    }
}