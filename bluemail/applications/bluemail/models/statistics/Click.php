<?php namespace ma\applications\bluemail\models\statistics
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
    use ma\mfw\application\Model as Model;
    /**
     * @name            Click.model 
     * @description     The Click model
     * @package		ma\applications\bluemail\models\statistics
     * @category        Model
     * @author		Miami Team			
     */
    class Click extends Model
    {
        #table 
        
        /**
         * @schema
         * @readwrite
         */
        protected $_schema = 'stats';
        
        /**
         * @table
         * @readwrite
         */
        protected $_table = 'clicks';

        # Columns 

        /**
	 * @column
	 * @readwrite
         * @autoincrement
         * @primary
	 * @type integer
         * @nullable false
	 * @length 
	 */
        protected $_id;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable false
	 * @length
	 */
        protected $_drop_id;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 100
	 */
        protected $_email;

        /**
	 * @column
	 * @readwrite
	 * @type timestamp
         * @nullable false
	 * @length
	 */
        protected $_action_date;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 100
	 */
        protected $_list;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 20
	 */
        protected $_ip;

        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_country;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_region;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length
	 */
        protected $_city;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 2
	 */
        protected $_language;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 
	 */
        protected $_device_type;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 100
	 */
        protected $_device_name;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 
	 */
        protected $_os;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 
	 */
        protected $_browser_name;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable true
	 * @length 100
	 */
        protected $_browser_version;
        
        /**
	 * @column
	 * @readwrite
	 * @type integer
         * @nullable true
	 * @length
	 */
        protected $_action_occurences;
    }
}