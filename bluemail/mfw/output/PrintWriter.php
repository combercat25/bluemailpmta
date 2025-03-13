<?php namespace ma\mfw\output
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
    /**
     * @name            PrintWriter.class 
     * @description     It's a class that deals with output methods
     * @package		ma\mfw\output
     * @category        Helper Class
     * @author		Miami Team			
     */
    class PrintWriter extends Base
    {
        /**
         * @name printValue
         * @description prints Values in the screen
         * @access static
         * @param mixed $input
         * @param boolean $exit
         * @param string $parentWrapper
         * @param string $style
         * @return
         */  
        public static function printValue($input,$exit = true,$parentWrapper = 'pre',$style = '')
        {
            # forcing the controller to display an error page instead of the requested one
            $controller = Packager::get("controller");

            if(isset($controller))
            {
                $controller->setShowMasterView(false);
                $controller->setShowPageView(false);
            }
            
            echo $parentWrapper != null && $parentWrapper != '' ? '<'.$parentWrapper.' style="'.$style.'" >' : '';
            print_r($input);
            echo $parentWrapper != null && $parentWrapper != '' ? '</'.$parentWrapper.'>' : '';
            if($exit) exit;
        }   
    }
}