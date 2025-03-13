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
    use ma\mfw\types\Arrays as Arrays;
    /**
     * @name            Simple.class 
     * @description     It deals with converting substrings matching the format of :property to regular expression wildcards
     * @package		ma\mfw\routing\routes
     * @category        Routing Class
     * @author		Miami Team			
     */
    class Simple extends Route 
    {
        /**
         * @name matches
         * @description converts substrings matching the format of :property to regular expression wildcards.
         * @access public
         * @param string $url  
         * @return boolean
         */
        public function matches($url) 
        {
            $pattern = $this->pattern;
            
            # get keys
            $keys = array();
            preg_match_all("#:([a-zA-Z0-9]+)#", $pattern, $keys);

            if (sizeof($keys) && sizeof($keys[0]) && sizeof($keys[1])) 
            {
                $keys = $keys[1];
            } 
            else 
            {
                # no keys in the pattern, return a simple match
                return preg_match("#^{$pattern}$#", $url);
            }

            # normalize route pattern
            $pattern = preg_replace("#(:[a-zA-Z0-9]+)#", "([a-zA-Z0-9-_]+)", $pattern);
            
            # check values
            $values = array();
            preg_match_all("#^{$pattern}$#", $url, $values);
            if (sizeof($values) && sizeof($values[0]) && sizeof($values[1])) 
            {
                # unset the matched url
                unset($values[0]);

                # values found, modify parameters and return
                $derived = array_combine($keys, Arrays::flatten($values));
                $this->parameters = array_merge($this->parameters, $derived);

                return true;
            }
            return false;
        }
    }
}