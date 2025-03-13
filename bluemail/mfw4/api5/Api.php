<?php namespace ma\mfw\api
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
    use ma\mfw\api\Cake as Cake;
    use ma\mfw\api\Hitpath as Hitpath;
    use ma\mfw\api\Hasoffers as Hasoffers;
    use ma\mfw\api\W4 as W4;
    /**
     * @name            Api.class 
     * @description     It's a class that deals with API methods
     * @package		ma\mfw\api
     * @category        API
     * @author		Miami Team			
     */
    class Api
    {
        public static function getAPIClass($sponsor)
        {
            $api = null;
            
            if(count($sponsor))
            {
                switch ($sponsor['api_type']) 
                {
                    case 'cake':
                    {
                        $api = new Cake(array(
                            "url" => $sponsor['api_url'],
                            "email" => $sponsor['username'],
                            "password" => $sponsor['password'],
                            "affiliateId" => $sponsor['affiliate_id'],
                            "key" => $sponsor['api_key']
                        ));
                        break;
                    }
                    case 'hitpath':
                    {
                        $api = new Hitpath(array(
                            "url" => $sponsor['api_url'],
                            "username" => $sponsor['username'],
                            "password" => $sponsor['password'],
                            "affiliateId" => $sponsor['affiliate_id'],
                            "key" => $sponsor['api_key']
                        ));
                        break;
                    }
                    case 'hasoffers':
                    {
                        $api = new Hasoffers(array(
                            "url" => $sponsor['api_url'],
                            "username" => $sponsor['username'],
                            "password" => $sponsor['password'],
                            "affiliateId" => $sponsor['affiliate_id'],
                            "key" => $sponsor['api_key']
                        ));
                        break;
                    }
                    case 'w4':
                    {
                        $api = new W4(array(
                            "url" => $sponsor['api_url'],
                            "username" => $sponsor['username'],
                            "password" => $sponsor['password'],
                            "affiliateId" => $sponsor['affiliate_id'],
                            "key" => $sponsor['api_key']
                        ));
                        break;
                    }
                }
            }
        
            return $api;
        }
    }
}



