<?php namespace ma\mfw\database
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
    use ma\mfw\exceptions\types\DatabaseException as DatabaseException;
    /**
     * @name            Database.class 
     * @description     A database factory class, which loads database drivers
     * @package		ma\mfw\database
     * @category        Database Class
     * @author		Miami Team			
     */
    class Database extends Base
    {    
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */
        protected $_defaultDatabase;
        
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_availableDatabases;

        /**
         * @name initialize
         * @description initializes the database driver based on the given type 
         * @access public
         * @return mixed
         * @throws DatabaseException
         */
        public function initialize() 
        {
            if(!empty($this->_defaultDatabase) && $this->_defaultDatabase != '')
            {
                return new Connector(array(
                    "availableServices" => $this->_availableDatabases ,
                    "defaultDatabase" => $this->_defaultDatabase,
                    "currentDatabase" => $this->_defaultDatabase
                ));
            }
            else
            {
                throw new DatabaseException("No Default Database Found !");
            }
            
            return null;
        } 

        /**
         * @name secureConnect
         * @description connects the database in a secure way
         * @access static
         * @return Connector
         */
        public static function getCurrentDatabaseConnector()
        {
            # retrieve the database object from the packager
            $database = Packager::get("database");

            if($database == null || !$database instanceof Connector)
            {
                throw new DatabaseException("Connection to database lost");
            }

            return $database;
        }
        
        /**
         * @name secureConnect
         * @description connects the database in a secure way
         * @access static
         * @return
         */
        public static function switchToDatabase($databaseKey = null)
        {
            if(isset($databaseKey))
            {
                # switch to a different database
                self::getCurrentDatabaseConnector()->setCurrentDatabase($databaseKey);
            }
        }
        
        /**
         * @name secureConnect
         * @description connects the database in a secure way
         * @access static
         * @return
         */
        public static function switchToDefaultDatabase()
        { 
            self::switchToDatabase(self::getCurrentDatabaseConnector()->getDefaultDatabase());
        }
        
        /**
         * @name secureConnect
         * @description connects the database in a secure way
         * @access static
         * @return
         */
        public static function secureConnect()
        {
            self::getCurrentDatabaseConnector()->connectAllDatabases();
        }
 
        /**
         * @name secureDisconnect
         * @description disconnects the database in a secure way
         * @access static
         * @return
         */
        public static function secureDisconnect()
        {
            self::getCurrentDatabaseConnector()->disconnectAllDatabases();
        }
    }
}