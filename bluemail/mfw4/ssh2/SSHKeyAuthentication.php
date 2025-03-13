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
     * @name            SSHKeyAuthentication.class 
     * @description     It's a class of key authentications types
     * @package		ma\mfw\ssh2
     * @category        SSH
     * @author		Miami Team			
     */
    class SSHKeyAuthentication extends SSHAuthentication
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
        private $_publicKey; 
        
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        private $_privateKey;
        
        /**
         * @name __construct
         * @description ssh class constructor
         * @access public
         * @return SSHPasswordAuthentication
         */
        public function __construct($username, $publicKey , $privateKey) 
        {
            $this->_username = $username;
            $this->_publicKey = $publicKey;
            $this->_privateKey = $privateKey;
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
         * @name getUsername
         * @description get public key
         * @access public
         * @return string
         */
        function getPublicKey()
        {
            return $this->_publicKey;
        }

        /**
         * @name getPrivateKey
         * @description get private key
         * @access public
         * @return string
         */
        function getPrivateKey() 
        {
            return $this->_privateKey;
        }

    }  
}