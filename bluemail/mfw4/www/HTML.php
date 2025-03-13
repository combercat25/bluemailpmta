<?php namespace ma\mfw\www
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
     * @name            HTML.class 
     * @description     It's a class that deals with HTML functions
     * @package		ma\mfw\www
     * @category        Word Wide Web Class
     * @author		Miami Team			
     */
    class HTML extends Base
    {
        /**
         * @name specialChars
         * @description transforming html special characters 
         * @access static
         * @param string $value 
         * @param boolean $doubleEncode 
         * @return string
         */
        public static function specialChars($value, $doubleEncode = TRUE)
        {
            $value = (string) $value;
            if ($doubleEncode === TRUE)
            {
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            else
            {
                if (version_compare(PHP_VERSION, '5.2.3', '>='))
                {
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', FALSE);
                }
                else
                {
                    $value = str_replace(array('<', '>', '\'', '"'), array('&lt;', '&gt;', '&#39;', '&quot;'), preg_replace('/&(?!(?:#\d++|[a-z]++);)/ui', '&amp;', $value));
                }
            }
            return $value;
        }

        /**
         * @name nl2br
         * @description inserts HTML line breaks before all newlines in a string
         * @access static
         * @param string $value  
         * @return string
         */
        public static function nl2br($value)
        {
            if (version_compare(PHP_VERSION, '5.3', '>='))
            {
                return nl2br($value, TRUE);
            }
            else
            {
                return nl2br($value);
            }
        }

        /**
         * @name createFavIconTag
         * @description creates an HTML tag for the favicon
         * @access static
         * @param string $skinURL
         * @param string $image
         * @return string
         */
        public static function createFavIconTag($skinURL,$image)
        {
            return '<link rel="shortcut icon" href="' .$skinURL. DS . 'img' . DS . $image.'" />';
        }  
    }
}