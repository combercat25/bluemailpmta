<?php namespace ma\mfw\routing\routes
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
    use ma\mfw\routing\Route as Route;
    /**
     * @name            Regex.class 
     * @description     It has a match method that all other router classes will work with to creates the correct regular expression search string and returns any matches to the provided URL
     * @package		ma\mfw\routing\routes
     * @category        Routing Class
     * @author		Miami Team			
     */
    class Regex extends Route 
    {
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_keys;

        /**
         * @name matches
         * @description creates the correct regular expression search string and returns any matches to the provided URL
         * @access public
         * @param string $url  
         * @return boolean
         */
        public function matches($url) 
        {
            $pattern = $this->pattern;
            $values = array();
                    
            # check values
            preg_match_all("#^{$pattern}$#", $url, $values);

            if (sizeof($values) && sizeof($values[0]) && sizeof($values[1])) 
            {
                # values found, modify parameters and return
                $derived = array_combine($this->keys, $values[1]);
                $this->parameters = array_merge($this->parameters, $derived);

                return true;
            }
            return false;
        }
    }
}