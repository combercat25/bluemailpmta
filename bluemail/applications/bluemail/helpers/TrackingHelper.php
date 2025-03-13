<?php namespace ma\applications\bluemail\helpers
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
    use ma\mfw\database\Database as Database;
    use ma\mfw\http\Client as Client;
    use ma\applications\bluemail\models\data\Opener as Opener;
    use ma\applications\bluemail\models\data\Clicker as Clicker;
    use ma\applications\bluemail\models\data\Lead as Lead;
    use ma\applications\bluemail\models\data\Unsubscriber as Unsubscriber;
    /**
     * @name            TrackingHelper.class 
     * @description     The TrackingHelper class
     * @package		ma\applications\bluemail\helpers
     * @category        Helper
     * @author		Miami Team			
     */
    class TrackingHelper
    {
        /**
         * @name getClientMeta
         * @description get client meta data
         * @access static
         * @return
         */
        public static function getClientMeta($agent,$ip,$language)
        {
            $client = new Client();
            $client->reset();
            $client->setAgent($agent);
            $client->setIp($ip);
            $client->checkIpMetaInformation();
            $client->checkOS();
            $client->checkBrowsers();
            $client->checkDeviceName();
            $client->setLanguage($language);
            return $client->retreiveInfo();
        }
        
        /**
         * @name getFlag
         * @description get flag
         * @access static
         * @return
         */
        public static function getFlag($country)
        {
            $europeCountries = array('AUSTRIA','GERMANY','POLAND','CYPRUS','FRANCE','CZECH REPUBLIC','LUXEMBOURG','ITALY','SWEDEN','SPAIN','UKRAINE','DENMARK','HUNGARY','BELGIUM','NETHERLANDS','PORTUGAL','ROMANIA','FINLAND','BULGARIA','NORWAY');

            if(strtoupper($country) == 'UNITED STATES')
            {
                return 'us';
            }
            else if(strtoupper($country) == 'UNITED KINGDOM')
            {
                return 'uk';
            }
            else if(strtoupper($country) == 'Australia')
            {
                return 'au';
            }
            else if(strtoupper($country) == 'Canada')
            {
                return 'ca';
            }
            else if(in_array(strtoupper($country),$europeCountries))
            {
                return 'eu';
            }
            else 
            {
                return 'ot';
            }
        }
        
        /**
         * @name getClientObject
         * @description get client object
         * @access static
         * @return
         */
        public static function getClientObject($tableType,$type)
        {
            if($type == 'o' || $type == 'u')
            {
                if(strpos($tableType,'leads') > -1)
                {  
                    return new Lead();
                }
                else if(strpos($tableType,'clickers') > -1)
                {
                    return new Clicker();
                }
                else
                {
                    return new Opener();
                }
            }
            else if($type == 'c')
            {
                if(strpos($tableType,'leads') > -1)
                {  
                    return new Lead();
                }
                else
                {
                    return new Clicker();
                }
            }
            else if($type == 'l')
            {
                return new Lead();
            }
            else if($type == 'opt')
            {
                return new Unsubscriber();
            }

            return null;
        }
        
        /**
         * @name getClientResult
         * @description get client result
         * @access static
         * @return
         */
        public static function getClientResult(&$oldData,$clientId)
        {
            $listName = str_replace($oldData['type'] . '_' . $oldData['flag'] . '_','',$oldData['table']);
            $flags = ['us','uk','au','ca','eu','ot'];
            $types = ['clean','openers','clickers','leads','unsubscribers'];

            foreach ($types as $type) 
            {
                foreach ($flags as $flag) 
                {
                    $table =  $type . '_' . $flag . '_' . $listName;
                    $schema = $oldData['schema'];
                    
                    # check if the table is not there create it
                    if(Database::getCurrentDatabaseConnector()->checkIfTableExists($schema,$table) == 'true')
                    {
                        $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT * FROM {$schema}.{$table} WHERE id = $clientId",true);
                    
                        if(count($result) > 0)
                        {
                            # update old data 
                            $oldData['table'] = $table;
                            $oldData['flag'] = $flag;
                            $oldData['type'] = $type;
                            $oldData['schema'] = $schema;
                            
                            return $result[0];
                        }
                    }
                }
            }
            
            return [];
        }
    }
}