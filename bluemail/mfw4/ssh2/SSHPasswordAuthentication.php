<?php namespace ma\mfw\ssh2
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
    use ma\mfw\ssh2\SSHAuthentication as SSHAuthentication;
    /**
     * @name            SSHPasswordAuthentication.class 
     * @description     It's a class of username/password authentications types
     * @package		ma\mfw\ssh2
     * @category        SSH
     * @author		Miami Team			
     */
    class SSHPasswordAuthentication extends SSHAuthentication
    {
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        private $_username; 
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        private $_password; 
        
        /**
         * @name __construct
         * @description ssh class constructor
         * @access public
         * @return SSHPasswordAuthentication
         */
        public function __construct($username, $password) 
        {
            $this->_username = $username;
            $this->_password = $password;
        }
        
        /**
         * @name getUsername
         * @description get username
         * @access public
         * @return string
         */
        function getUsername() 
        {
            return $this->_username;
        }

        /**
         * @name getPassword
         * @description get get password
         * @access public
         * @return string
         */
        function getPassword() 
        {
            return $this->_password;
        }
    }  
}