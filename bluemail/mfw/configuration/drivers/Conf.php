<?php namespace ma\mfw\configuration\drivers
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
    use ma\mfw\caching\Driver as Driver;
    use ma\mfw\types\Arrays as Arrays;
    use ma\mfw\exceptions\types\ConfigurationException as ConfigurationException;
    /**
     * @name            Conf.class 
     * @description     The .conf.php configuration files parsing driver 
     * @package		ma\mfw\configuration\drivers
     * @category        Configuration Parsing Class
     * @author		Miami Team			
     */
    class Conf extends Driver
    {
        /**
         * @name parse
         * @description parses a php configuration file with an associative array format
         * @access public
         * @param string $path
         * @param boolean $objectFormat
         * @return mixed
         * @throws ConfigurationException
         */
        public function parse($path,$objectFormat = true) 
        {
            if (empty($path)) 
            {
                throw new ConfigurationException("\$path argument is not valid");
            }
            
            if (!file_exists(ROOT_PATH . DS . DEFAULT_CONFIGS_DIRECTORY . DS . "{$path}.conf.php")) 
            {
                throw new ConfigurationException("The file that you provided was not found");
            }
            
            if (!isset($this->_parsed[$path])) 
            {
                $config = array();
                include(ROOT_PATH . DS . DEFAULT_CONFIGS_DIRECTORY . DS . "{$path}.conf.php");
                $this->_parsed[$path] = ($objectFormat) ? Arrays::arrayToObject($config) : $config;
            }
            
            return $this->_parsed[$path];
        }
    }
}