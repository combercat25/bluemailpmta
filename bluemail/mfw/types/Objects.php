<?php namespace ma\mfw\types
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
    use ma\mfw\exceptions\types\ArgumentException as ArgumentException;
    /**
     * @name            Objects.class 
     * @description     It is a class that contains a bunch of Objects management functions to apply usual routines
     * @package		ma\mfw\types
     * @category        DataTypes Class
     * @author		Miami Team			
     */
    class Objects extends Base
    {
        /**
         * @name objectToXML
         * @description converts a given object into an xml format
         * @access public
         * @param mixed $object
         * @return mixed
         */
        public static function objectToXML($object)
        {
            $dom = new \DOMDocument("1.0", "UTF-8");
            $root = $dom->createElement(get_class($object));
            foreach($object as $key=>$value) 
            {
                $node = NULL;
                if (is_string($value) || is_numeric($value) || is_bool($value) || $value == NULL) 
                {
                    if ($value == NULL)
                    {
                        $node = $dom->createElement($key);
                    }
                    else
                    {
                        $node = $dom->createElement($key, (string) $value);
                    }
                } 
                else 
                {
                    $node = $dom->createElement($key);
                    if ($value != NULL) 
                    {
                        foreach ($value as $key => $value) 
                        {
                            $sub = $this->createNode($key, $value);
                            if ($sub != NULL)
                            {
                                $node->appendChild($sub);
                            }    
                        }
                    }
                }
                if($node != NULL)
                {
                    $root->appendChild($node);
                }      
            }
            $dom->appendChild($root); 
            return $dom->saveXML();
        }

        /**
         * @name objectToArray
         * @description converts an Object into an array
         * @access public | static 
         * @param object $object the object to copy variables from
         * @param array $array  the array to copy variables to
         * @return string
         */
        public static function objectToArray($object) 
        {
            $array = array();  
            
            if (is_object($object)) 
            {
                foreach ( (array) $object as $index => $node )
                {
                    $array[$index] = (is_object($node)) ? self::objectToArray ($node) : $node;
                }
            }
            else
            {
                throw new ArgumentException("objectToArray: Missing array and/or object");
            }

            return $array;
        }
        
        /**
         * @name getClassNameWithoutNameSpace
         * @description get class name without namespace in it  
         * @access public
         * @param mixed $object
         * @return mixed
         */
        public static function getClassNameWithoutNameSpace($object) 
        {
            $classname = get_class($object);
            $matches = array();
            if (preg_match('@\\\\([\w]+)$@', $classname, $matches)) 
            {
                $classname = $matches[1];
            }
            return $classname;
        }
    }
}