<?php namespace ma\mfw\caching
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
    /**
     * @name            Driver.class 
     * @description     The main driver class that all upcoming drivers have to extends from it.
     * @package		ma\mfw\caching
     * @category        Caching Class
     * @author		Miami Team			
     */
    class Driver extends Base
    {
        /**
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_parsed = array();
        
        /**
         * @name initialize
         * @description Initializing the driver
         * @access public
         * @return mixed
         */
        public function initialize()
        {
            return $this;
        }  
    }
}