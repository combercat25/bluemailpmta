<?php namespace ma\applications\bluemail\models\production
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
     * @name            IpStatus.model 
     * @description     The IpStatus model
     * @package		ma\applications\bluemail\models\production
     * @category        Model
     * @author		Miami Team			
     */
    class IpStatus extends Model
    {
        #table 
        
        /**
         * @schema
         * @readwrite
         */
        protected $_schema = 'production';
        
        /**
         * @table
         * @readwrite
         */
        protected $_table = 'ip_status';

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
        protected $_ip_id;

        /**
	 * @column
	 * @readwrite
	 * @type timestamp
         * @nullable false
	 * @length
	 */
        protected $_status_date;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 255
	 */
        protected $_x_store_info;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 255
	 */
        protected $_x_message_delivery;
        
        /**
	 * @column
	 * @readwrite
	 * @type text
         * @nullable false
	 * @length 255
	 */
        protected $_x_message_info;
    }
}