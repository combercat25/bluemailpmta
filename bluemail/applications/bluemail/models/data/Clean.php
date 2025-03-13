<?php namespace ma\applications\bluemail\models\data
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
     * @name            Clean.model 
     * @description     The Clean model
     * @package		ma\applications\bluemail\models\data
     * @category        Model
     * @author		Miami Team			
     */
    class Clean extends Model
    {
        #table 
        
        /**
         * @schema
         * @readwrite
         */
        protected $_schema = '';
        
        /**
         * @table
         * @readwrite
         */
        protected $_table = 'clean';

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
	 * @type text
         * @nullable false
	 * @length 500
	 */
        protected $_email;
        
        /**
         * @column
         * @readwrite
         * @type text
         * @nullable true
         * @length 100
         */
        protected $_fname;

        /**
         * @column
         * @readwrite
         * @type text
         * @nullable true
         * @length 100
         */
        protected $_lname;

        /**
         * @column
         * @readwrite
         * @type text
         * @nullable true
         * @length
         */
        protected $_offers_excluded;
    }

}