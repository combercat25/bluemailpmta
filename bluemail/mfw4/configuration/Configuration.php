<?php namespace ma\mfw\configuration
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
    use ma\mfw\configuration\drivers\Ini as Ini;
    use ma\mfw\configuration\drivers\Conf as Conf;
    use ma\mfw\exceptions\types\ConfigurationException as ConfigurationException;
    /**
     * @name            Configuration.class 
     * @description     It deals with configuration files 
     * @package		ma\mfw\configuration
     * @category        Configuration Parsing Class
     * @author		Miami Team			
     */
    class Configuration extends Base
    {
        
        /**
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_type;

        /**
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_options;        

        /**
         * @name initialize
         * @description Initializing the driver
         * @access public
         * @return mixed
         * @throws ConfigurationException
         */
        public function initialize() 
        {
            if (!$this->type) 
            {
                throw new ConfigurationException("Invalid type");
            }
            switch ($this->type) 
            {
                case "ini": 
                {
                    return new Ini($this->options);
                }
                case "conf": 
                {
                    return new Conf($this->options);
                }
                default: 
                {
                    throw new ConfigurationException("Invalid type");
                }
            }
        }
    }
}