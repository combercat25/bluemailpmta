<?php namespace ma\mfw\pmta
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
     * @name            PmtaMailMergeSection.class 
     * @description     The PmtaMailMergeSection class
     * @package		ma\mfw\pmta
     * @category        Helper
     * @author		Miami Team			
     */
    class PmtaMailMergeSection extends Base
    {
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_rcptTo;
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_vmta;
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_from;
        
        /** 
         * @read
         * @access protected 
         * @var array
         */ 
        protected $_xdfns;

        /**
         * @name addXDFN
         * @description add XDFN to the existant XDFN parameters
         * @access public
         * @param string $key
         * @param string $value
         * @return
         */
        public function addXDFN($key,$value) 
        {
            if(isset($key) && isset($value))
            {
                $this->_xdfns[$key] = $value;
            }
        }
    }
}