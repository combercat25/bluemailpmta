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
    use ma\mfw\exceptions\types\ParseException as ParseException;
    use ma\mfw\exceptions\types\ConfigurationException as ConfigurationException;
    /**
     * @name            Ini.class 
     * @description     The .ini configuration files parsing driver 
     * @package		ma\mfw\configuration\drivers
     * @category        Configuration Parsing Class
     * @author		Miami Team			
     */
    class Ini extends Driver
    {   
        /**
         * @name parse
         * @description parses an .ini file
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
            
            if (!file_exists("{$path}.ini")) 
            {
                throw new ConfigurationException("The file that you provided was not found");
            }
            
            if (!isset($this->_parsed[$path])) 
            {
                $config = array();
                
                ob_start();
                include("{$path}.ini");
                $string = ob_get_contents();
                ob_end_clean();
                
                $pairs = parse_ini_string($string);
                
                if ($pairs == false) 
                {
                    throw new ParseException("Could not parse configuration file");
                }
                
                foreach ($pairs as $key => $value) 
                {
                    $config = $this->_pair($config, $key, $value);
                }
                
                $this->_parsed[$path] = ($objectFormat) ? Arrays::arrayToObject($config) : $config;
            }
            
            return $this->_parsed[$path];
        }
        
        /**
         * @name _pair
         * @description pairs a configuration input
         * @access protected
         * @param array $config
         * @param string $key
         * @param string $value
         * @return array
         */
        protected function _pair($config, $key, $value) 
        {
            if (strstr($key, ".")) 
            {
                $parts = explode(".", $key, 2);
                if (empty($config[$parts[0]])) 
                {
                    $config[$parts[0]] = array();
                }
                $config[$parts[0]] = $this->_pair($config[$parts[0]], $parts[1], $value);
            } 
            else 
            {
                $config[$key] = $value;
            }
            return $config;
        }
    }
}