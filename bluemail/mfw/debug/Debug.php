<?php namespace ma\mfw\debug
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
     * @name            Debug.class 
     * @description     It's a class that deals with debug methods like getting backtrace call , method call .....
     * @package		ma\mfw\debug
     * @category        Helper Class
     * @author		Miami Team			
     */
    class Debug extends Base
    {
        /**
         * @name getBackTrace
         * @description retrieves the whole debug backtrace
         * @access static
         * @param integer $level
         * @param array
         * @return
         */
        public static function getBackTrace($level = null) 
        {
            $debugBackTrace = debug_backtrace();
            if(isset($level) && is_numeric($level) && $level > 0)
            {
                if(isset($debugBackTrace) && is_array($debugBackTrace) && count($debugBackTrace) === $level && is_array($debugBackTrace[$level-1]))
                {
                    return $debugBackTrace[$level-1];
                }
            }
            return $debugBackTrace;
        }
    }
}