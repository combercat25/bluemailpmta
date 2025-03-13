<?php namespace ma\applications\bluemail\controllers
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
    use ma\mfw\application\Controller as Controller;
    use ma\mfw\database\Database as Database;
    use ma\mfw\http\Request as Request;
    use ma\mfw\types\Arrays as Arrays;
    use ma\applications\bluemail\models\admin\OfferLink as OfferLink;
    use ma\applications\bluemail\models\production\Drop as Drop;
    use ma\applications\bluemail\models\data\DataList as DataList;
    use ma\applications\bluemail\models\statistics\Open as Open;
    use ma\applications\bluemail\models\statistics\Click as Click;
    use ma\applications\bluemail\models\statistics\Lead as Lead;
    use ma\applications\bluemail\models\statistics\Unsub as Unsub;
    use ma\applications\bluemail\helpers\TrackingHelper as TrackingHelper;
    use ma\applications\bluemail\models\admin\Offer as Offer;
    /**
     * @name            Api.controller 
     * @description     The Api controller
     * @package		ma\applications\bluemail\controllers
     * @category        Controller
     * @author		Miami Team			
     */
    class Api extends Controller 
    {
        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_secretKey = 'x0ja8s4a3duqk9e2w6vga91hrvi7t14wrdxpv754aql055tr2ee2d59b6hop';

        /** 
         * @readwrite
         * @access protected 
         * @var string
         */ 
        protected $_httpVersion = "HTTP/1.1";
        
        /** 
         * @readwrite
         * @access protected 
         * @var array
         */ 
        protected $_supportedTypes = ['application/json','application/xml','text/html'];
        
        /**
         * @name index
         * @description the index action
         */
        public function index() 
        {
            $this->setShowMasterView(false);
            $this->setShowPageView(false);
            
            $apiKey = Request::getParameterFromPOST('api_key');
            $contentType = in_array(Request::getParameterFromPOST('result_type',''),$this->_supportedTypes) ? Request::getParameterFromPOST('result_type','') : 'application/json';
            $functionName = Request::getParameterFromPOST('func_name');
            
            if($apiKey != null && $apiKey == $this->_secretKey)
            {
                switch ($functionName) 
                {
                    case 'generate-link' :
                    {
                        $data = Request::getParameterFromPOST('data');
                        
                        if(count($data) == 0)
                        {
                            $this->displayResponse(500,$contentType,[],'No Data Passed !');
                        }
                        else
                        {
                            $dropId = intval(Arrays::getElement($data,'drop-id'));
                            $type = Arrays::getElement($data,'type');
                            $creativeId = intval(Arrays::getElement($data,'creative-id'));
                            $clientId = intval(Arrays::getElement($data,'client-id'));
                            $listId = intval(Arrays::getElement($data,'list-id'));
                            $ispId = 0;
                            $offerLinks = [];
                            $sponsor = null;
   
                            if(in_array($type,['c','u']))
                            {
                                # connect to the default database 
                                Database::secureConnect();

                                if ($creativeId > 0) 
                                {
                                    $offerLinks = OfferLink::all(true,['creative_id = ?',$creativeId]);
                                    $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT * FROM admin.sponsors WHERE id = (SELECT sponsor_id FROM admin.offers WHERE id = (SELECT offer_id FROM admin.offer_creatives WHERE id = $creativeId ))",true);
                                    
                                    if(count($result))
                                    {
                                        $sponsor = $result[0];
                                    }
                                }
                                elseif($dropId > 0)
                                {
                                    $drop = Drop::first(true,['id = ?',$dropId],['id','creative_id','offer_id','isp_id']);
                                    
                                    if(count($drop) && key_exists('creative_id',$drop))
                                    {
                                        $offerLinks = OfferLink::all(true,['creative_id = ?',intval($drop['creative_id'])]);
                                        $creativeId = intval($drop['creative_id']);
                                    }
                                    
                                    if(count($drop) && key_exists('offer_id',$drop))
                                    {
                                        $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT * FROM admin.sponsors WHERE id = (SELECT sponsor_id FROM admin.offers WHERE id = {$drop['offer_id']})",true);
                                    
                                        if(count($result))
                                        {
                                            $sponsor = $result[0];
                                        }
                                    }
                                    
                                    if(count($drop) && key_exists('isp_id',$drop))
                                    {
                                        $ispId = intval($drop['isp_id']);
                                    }
                                }
                                
                                # disconnect from all databases 
                                Database::secureDisconnect();
                                
                                # begin generating link
                                if(count($sponsor) && count($offerLinks))
                                {
                                    $url = '';
                                    $type = $type == 'c' ? 'preview' : 'unsub';
                                    
                                    foreach ($offerLinks as $row) 
                                    {
                                        if(count($row) && ($row['type'] == $type || ($type == 'unsub' && $row['type'] == 'other')))
                                        {
                                            # crate the link
                                            $sub1 = $listId;
                                            $sub2 = $dropId;
                                            $sub3 = $clientId;
                                            $sub4 = $ispId;
                                            
                                            if($sponsor['api_type'] == 'cake')
                                            {
                                                $subKey = 's';
                                                $url = $row['value'];
                                                $url = str_replace([
                                                    '?' . $subKey . '1=',
                                                    '&' . $subKey . '1=',
                                                    '?' . $subKey . '1',
                                                    '&' . $subKey . '1',
                                                    '?' . $subKey . '2=',
                                                    '&' . $subKey . '2=',
                                                    '?' . $subKey . '2',
                                                    '&' . $subKey . '2',
                                                    '?' . $subKey . '3=',
                                                    '&' . $subKey . '3=',
                                                    '?' . $subKey . '3',
                                                    '&' . $subKey . '3',
                                                    '?' . $subKey . '4=',
                                                    '&' . $subKey . '4=',
                                                    '?' . $subKey . '4',
                                                    '&' . $subKey . '4'],
                                                    '', $url);
                                                $url .= strpos($url,'?') > -1 ? '&' : '?';
                                                $url = $url . $subKey . '1=' . $sub1 . '&' . $subKey . '2=' . $sub2 . '&' . $subKey . '3=' . $sub3 . '&' . $subKey . '4=' . $sub4;
                                            }
                                            else
                                            {
                                                $subKey = 'c';
                                                $url = rtrim($row['value'],RDS);
                                                $url = str_replace([
                                                    '?' . $subKey . '1=',
                                                    '&' . $subKey . '1=',
                                                    '?' . $subKey . '1',
                                                    '&' . $subKey . '1',
                                                    '?' . $subKey . '2=',
                                                    '&' . $subKey . '2=',
                                                    '?' . $subKey . '2',
                                                    '&' . $subKey . '2',
                                                    '?' . $subKey . '3=',
                                                    '&' . $subKey . '3=',
                                                    '?' . $subKey . '3',
                                                    '&' . $subKey . '3',
                                                    '?' . $subKey . '4=',
                                                    '&' . $subKey . '4=',
                                                    '?' . $subKey . '4',
                                                    '&' . $subKey . '4'],
                                                    '', $url);
                                                $url = $url . RDS . $sub1 . RDS . $sub2 . RDS . $sub3 . RDS . $sub4;
                                            }
                                        }
                                    }

                                    $this->displayResponse(200,$contentType,['link' => $url]);
                                }
                                else
                                {
                                    $this->displayResponse(500,$contentType,[],'No Links Found!');
                                }
                            }
                        }
                        
                        break;
                    }
                    case 'procceed-tracking' :
                    {
                        $data = Request::getParameterFromPOST('data');
                        
                        if(count($data) == 0)
                        {
                            $this->displayResponse(500,$contentType,[],'No Data Passed !');
                        }
                        else
                        {
                            $dropId = intval(Arrays::getElement($data,'drop-id'));
                            $type = Arrays::getElement($data,'type');
                            $clientId = intval(Arrays::getElement($data,'client-id'));
                            $listId = intval(Arrays::getElement($data,'list-id'));
                            $agent = base64_decode(Arrays::getElement($data,'agent'));
                            $ip = Arrays::getElement($data,'ip');
                            $language = strtoupper(substr(base64_decode(Arrays::getElement($data,'lang')), 0, 2));
                            $message = Arrays::getElement($data,'message','No Message !');
                            
                            if($dropId > 0 && $listId > 0 && $clientId > 0)
                            {
                                # connect to the default database 
                                Database::secureConnect();
                                
                                # prepare client meta data 
                                $metaInfo = TrackingHelper::getClientMeta($agent, $ip, $language);
      
                                # get essential data 
                                $list = DataList::first(true,['id = ?',$listId]);
                                $drop = Drop::first(true,['id = ?',$dropId]);
                                $offer = Offer::first(true,['id = ?',$drop['offer_id']]);
                                
                                if(count($drop) && count($list) && count($offer) && strpos(Arrays::getElement($list,'name'), 'seeds') == FALSE)
                                {
                                    # prepare some data 
                                    $oldData = [
                                        'schema' => trim(Arrays::getElement(explode('.',Arrays::getElement($list,'name')),0)),
                                        'table' => trim(Arrays::getElement(explode('.',Arrays::getElement($list,'name')),1)),
                                        'type' => trim(Arrays::getElement(explode('_',trim(Arrays::getElement(explode('.',Arrays::getElement($list,'name')),1))),0)),
                                        'flag' => Arrays::getElement($list,'flag')
                                    ];
                                    
                                    $newData = [
                                        'schema' => $oldData['schema'],
                                        'table' => '',
                                        'type' => '',
                                        'flag' => TrackingHelper::getFlag($metaInfo['country'])
                                    ];
                                    
                                    $removeRecord = true;

                                    # switch to lists database
                                    Database::switchToDatabase('lists');
                                    
                                    # search for the fucking email 
                                    $clientResult = Database::getCurrentDatabaseConnector()->executeQuery("SELECT * FROM {$list['name']} WHERE id = $clientId",true);
                                    
                                    # in case of that fucking email has already moved to another table
                                    if(count($clientResult) == 0)
                                    {
                                        $clientResult = TrackingHelper::getClientResult($oldData,$clientId);
                                    }
                                    else
                                    {
                                        $clientResult = $clientResult[0];
                                    }

                                    # switch to default database
                                    Database::switchToDefaultDatabase();

                                    # if the fucking email was found
                                    if(count($clientResult))
                                    {
                                        # prepare the action
                                        $actionInfo = [
                                            'drop_id' => $dropId,
                                            'email' => base64_encode($clientResult['email']),
                                            'action_date' => date('Y-m-d H:i:s'),
                                            'list' => $list['name'],
                                            'ip' => $ip,
                                            'country' => $metaInfo['country'],
                                            'region' => $metaInfo['region'],
                                            'city' => $metaInfo['city'],
                                            'language' => $language,
                                            'device_type' => $metaInfo['device-type'],
                                            'device_name' => $metaInfo['device-name'],
                                            'os' => $metaInfo['os'],
                                            'browser_name' => $metaInfo['browser-name'],
                                            'browser_version' => $metaInfo['browser-version']
                                        ];

                                        # register the action first ( click / open / unsub )
                                        switch (trim($type)) 
                                        {
                                            case 'o':
                                            {   
                                                $count = Database::getCurrentDatabaseConnector()->query()->from('stats.opens')->where('drop_id = ? AND email = ?',[$dropId,base64_encode($clientResult['email'])])->count();

                                                if($count == 0)
                                                {
                                                    $openObject = new Open($actionInfo);
                                                    $openObject->save();
                                                }

                                                break;
                                            }
                                            case 'c':
                                            {
                                                $count = Database::getCurrentDatabaseConnector()->query()->from('stats.clicks')->where('drop_id = ? AND email = ?',[$dropId,base64_encode($clientResult['email'])])->count();

                                                if($count == 0)
                                                {
                                                    $clickObject = new Click($actionInfo);
                                                    $clickObject->save();
                                                }

                                                break;
                                            }
                                            case 'l':
                                            {
                                                $count = Database::getCurrentDatabaseConnector()->query()->from('stats.leads')->where('drop_id = ? AND email = ?',[$dropId,base64_encode($clientResult['email'])])->count();

                                                if($count == 0)
                                                {
                                                    $clickObject = new Lead($actionInfo);
                                                    $clickObject->save();
                                                }

                                                break;
                                            }
                                            case 'u':
                                            case 'opt':
                                            {
                                                $count = Database::getCurrentDatabaseConnector()->query()->from('stats.unsubs')->where('drop_id = ? AND email = ?',[$dropId,base64_encode($clientResult['email'])])->count();

                                                if($count == 0)
                                                {
                                                    $actionInfo['type'] = (trim($type) == 'opt') ? 'srv_unsub' : 'offer_unsub';
                                                    $actionInfo['message'] = $message;
                                                    $unsubObject = new Unsub($actionInfo);
                                                    $unsubObject->save();
                                                }

                                                break;
                                            }
                                        }
                                        
                                        # get the fucking verticals
                                        $verticals = [$offer['vertical_id']];

                                        if(key_exists('verticals',$clientResult) && $clientResult['verticals'] != 'NULL' && strlen($clientResult['verticals']) > 0)
                                        {
                                            $verticals = array_unique(array_merge($verticals,explode(',',$clientResult['verticals'])));
                                        }
                                        
                                        $verticals = (count($verticals) > 0) ? implode(',',$verticals) : 'NULL';
                                        
                                        # switch back to lists database 
                                        Database::switchToDatabase('lists');
                                        
                                        # create a fucking client object ( email row in the database )
                                        $client = TrackingHelper::getClientObject($oldData['type'], $type);
                                        
                                        # update new data 
                                        $newData['type'] = $client->getTable();
                                        $newData['table'] = $client->getTable() . '_' . $newData['flag'] . '_' . str_replace($oldData['type'] . '_' . $oldData['flag'] . '_','',$oldData['table']);
                                        
                                        $client->setTable($newData['table']);
                                        $client->setSchema($newData['schema']); 
                                        $client->setId($clientId);
                                        
                                        # check if the table is not there create it
                                        if(Database::getCurrentDatabaseConnector()->checkIfTableExists($client->getSchema(),$client->getTable()) == 'false')
                                        {
                                            $client->synchronizeWithDatabase($client->getTable(),$client->getSchema());

                                            # switch to default database
                                            Database::switchToDefaultDatabase();

                                            # create the list
                                            $list = new DataList();
                                            $list->setName("{$client->getSchema()}.{$client->getTable()}");
                                            $list->setIsp_id(intval(Arrays::getElement($drop,'isp_id',1)));
                                            $list->setStatus_id(1);
                                            $list->setFlag($newData['flag']);
                                            $list->setCreated_by(intval(Arrays::getElement($drop,'user_id',1)));
                                            $list->setCreated_at(date("Y-m-d"));
                                            $list->setLast_updated_by(intval(Arrays::getElement($drop,'user_id',1)));
                                            $list->setLast_updated_at(date("Y-m-d"));
                                            
                                            $list->save();

                                            # switch back to lists database 
                                            Database::switchToDatabase('lists');
                                        }
                                        
                                        if(in_array(trim($type),['o','u']))
                                        {
                                            # if the table
                                            if(in_array(trim($oldData['type']),['openers','clickers','leads']) && $oldData['flag'] == $newData['flag'])
                                            {
                                                $client->load();  
                                                $removeRecord = false;
                                            }
                                        }
                                        else if(trim($type) == 'c')
                                        {
                                            # if the table
                                            if(in_array(trim($oldData['type']),['clickers','leads']) && $oldData['flag'] == $newData['flag'])
                                            {
                                                $client->load();  
                                                $removeRecord = false;
                                            }
                                        } 
                                        else if(trim($type) == 'l')
                                        {
                                            # if the table
                                            if(in_array(trim($oldData['type']),['leads']) && $oldData['flag'] == $newData['flag'])
                                            {
                                                $client->load();  
                                                $removeRecord = false;
                                            }
                                        } 

                                        $client->setEmail($clientResult['email']);
                                        $client->setFname(Arrays::getElement($clientResult,'fname',''));
                                        $client->setLname(Arrays::getElement($clientResult,'lname',''));
                                        $client->setAction_date(date('Y-m-d H:i:s'));
                                        $client->setVerticals($verticals);
                                        $client->setIp($ip);
                                        $client->setAgent(base64_encode($agent));
                                        $client->setCountry($metaInfo['country']);
                                        $client->setRegion($metaInfo['region']);
                                        $client->setCity($metaInfo['city']);
                                        $client->setLanguage($language);
                                        $client->setDevice_type($metaInfo['device-type']);
                                        $client->setDevice_name($metaInfo['device-name']);
                                        $client->setOs($metaInfo['os']);
                                        $client->setBrowser_name($metaInfo['browser-name']);
                                        $client->setBrowser_version($metaInfo['browser-version']);
                    
                                        # check if this is an unsub to add the offer id 
                                        if(trim($type) == 'opt')
                                        {
                                            $client->setMessage($message);
                                            $client->setDrop_id($dropId);
                                        }
                                        elseif(trim($type) == 'u')
                                        {
                                            $offers = [$drop['offer_id']];

                                            # add offers excluded  
                                            if(key_exists('offers_excluded',$result) && $clientResult['offers_excluded'] != 'NULL' && strlen($clientResult['offers_excluded']) > 0)
                                            {
                                                $offers = array_unique(array_merge($offers,explode(',', trim($clientResult['offers_excluded'],','))));
                                            }

                                            $offers = (count($offers) > 0) ? implode(',',$offers) . ',' : 'NULL';
                                            $client->setOffers_excluded($offers);
                                        }

                                        # save the fucking client 
                                        if($removeRecord == false)
                                        {
                                            $client->save();
                                        }
                                        else
                                        {
                                            $client->save(false,true);
                                        }
                                        
                                        # check if we have to delete the old record
                                        if($removeRecord == true)
                                        {
                                            Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM {$oldData['schema']}.{$oldData['table']} WHERE id = $clientId");

                                            $count = Database::getCurrentDatabaseConnector()->query()->from("{$oldData['schema']}.{$oldData['table']}")->count();
                                            $newCount = Database::getCurrentDatabaseConnector()->query()->from("{$newData['schema']}.{$newData['table']}")->count();
                                            
                                            # switch to default database 
                                            Database::switchToDefaultDatabase();
                                                
                                            if($count == 0)
                                            {
                                                # set the list to empty status 
                                                Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.data_lists SET status_id = 3 WHERE name='{$oldData['schema']}.{$oldData['table']}'",true);
                                            }
                                            
                                            if($newCount == 0)
                                            {
                                                # set the list to empty status 
                                                Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.data_lists SET status_id = 3 WHERE name='{$newData['schema']}.{$newData['table']}'",true);
                                            }
                                        }
                                        
                                        $this->displayResponse(200,$contentType,['message' => 'Operation finished successfully !']);
                                    }
                                    else 
                                    {
                                        $this->displayResponse(500,$contentType,[],'Email not found!');
                                    }
                                }
                                else
                                {
                                    $this->displayResponse(500,$contentType,[],'Some Data not found!');
                                }

                                # disconnect from all databases 
                                Database::secureDisconnect();
                            }
                            else
                            {
                                $this->displayResponse(500,$contentType,[],'Some ids are incorrect !');
                            }
                        }   
                          
                        break;
                    }
                    default:
                    {
                        $this->displayResponse(500,$contentType,[],'Method not found!');
                        break;
                    }
                }
            }
            else
            {
                $this->displayResponse(500,$contentType,[],'incorrect api key!');
            }
        }

        /**
         * @name getHttpStatusMessage
         * @description gets status message
         * @once
         * @protected
         */
        public function getHttpStatusMessage($statusCode)
        {
            $httpStatus = [
                100 => 'Continue',
                101 => 'Switching Protocols',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                306 => '(Unused)',
                307 => 'Temporary Redirect',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported'
            ];
            
            return ($httpStatus[$statusCode]) ? $httpStatus[$statusCode] : $httpStatus[500];
        }
        
        /**
         * @name displayResponse
         * @description displays response
         * @once
         * @protected
         */
        public function displayResponse($statusCode,$contentType,$result,$errorMessage = null,$die = false)
        {
            $statusMessage = $this->getHttpStatusMessage($statusCode);
            header("{$this->_httpVersion} $statusCode $statusMessage");		
            $this->setDefaultContentType($contentType);
            
            $response = [];
            
            $response['contentType'] = $contentType;
            $response['statusCode'] = $statusCode;
            $response['statusMessage'] = $statusMessage;
            
            if(!empty($errorMessage))
            {
                $response['errorMessage'] = $errorMessage;
            }
            else
            {
                $response['results'] = $result;
            }

            if($die == true)
            {
                die(json_encode($response,JSON_FORCE_OBJECT));
            }
            else
            {
                echo json_encode($response,JSON_FORCE_OBJECT);
            }
        }
        
        /**
         * @name getScriptResult
         * @description gets scripts result
         * @once
         * @protected
         */
        public function getScriptResult($result)
        {
            return (count($result) && key_exists('output',$result) && count($result['output'])) ? json_decode($result['output'][0],true) : ['status' => 500,'message' => 'Internal Server Error !'];
        }
    } 
}