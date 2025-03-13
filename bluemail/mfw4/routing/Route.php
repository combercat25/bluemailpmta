<?php namespace ma\mfw\routing
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
     * @name            Route.class 
     * @description     It represents the different kinds of routes that we can define in our framework's configuration
     * @package		ma\mfw\routing
     * @category        Routing Class
     * @author		Miami Team			
     */
    class Route extends Base 
    {
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_pattern;

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_controller;

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_action;

        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_parameters = array();
    }
}