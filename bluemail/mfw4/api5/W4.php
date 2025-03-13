<?php namespace ma\mfw\api
{
    if (!defined('IS_MFW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
    /**
     * @framework       Miami Framework
     * @version         1.1
     * @author         
     * @copyright       Copyright (c) 2017 - 2018.
     * @license		
     * @link	
     */
    use ma\mfw\core\Base as Base;
    use ma\mfw\os\System as System;
    use ma\mfw\types\Strings as Strings;
    use ma\mfw\types\Arrays as Arrays;
    use ma\mfw\www\URL as URL;
    use ma\mfw\application\Application as Application;
    use ma\mfw\exceptions\types\BackendException as BackendException;
    /**
     * @name            W4.class 
     * @description     It's a class that deals with W4 methods
     * @package		ma\mfw\helpers\www
     * @category        Word Wide Web Class
     * @author					
     */
    class W4 extends Base
    {
        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_url;

        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_username;

        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_password;
        
        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_key;

        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_affiliateId;
    
        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_previewURLPrefix = 'cid';
        
        /**
         * @name getResponse
         * @description get Response
         */
        public function getResponse($url,$data)
        {
            $result = [];
            $url = empty($url) ? $this->_url : $url;
            
            if (filter_var($url, FILTER_VALIDATE_URL))
            {
                # Initiate the REST call via curl
                $ch = \curl_init($url . '?' . http_build_query($data));
                \curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:26.0) Gecko/20100101 Firefox/26.0");
                \curl_setopt($ch, CURLOPT_FAILONERROR, true);
                \curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                \curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                \curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                \curl_setopt($ch, CURLOPT_VERBOSE, false);

                # Don't return headers
                \curl_setopt($ch, CURLOPT_HEADER, false);

                # Return data after call is made
                \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                # Execute the REST call
                $response = \curl_exec($ch);

                $result = $response;

                # Close the connection
                curl_close($ch);
            }

            return $result;
        }
        
        
        /**
         * @name getOffers
         * @description get offers
         */
        function getOffers($ids = [])
        {
            $offers = [];
            
            $params = [
                'key_id' => $this->_key,
                'campaign_ids' => '[' . join(',', $ids) . ']',
                'status' => 'approved',
                'limit' => '500',
            ];
            
            $response = $this->getResponse(rtrim($this->_url,RDS) . RDS . 'pubs_campaign_available' . RDS . 'get' .  RDS,$params,true);

            if($response != null && $response != "")
            {
                $json = json_decode($response,true);
                
                # check if there is an error
                if (intval($json['success']) != 1)
                {
                    throw new BackendException('Error while getting offers : ' . $json['message']);
                }

                # check if there is no offer data
                if (count($json['data'])  == 0 || count($json['data']['results'])  == 0 )
                {
                    throw new BackendException('No Offers Found !');
                }
                
                foreach ($json['data']['results'] as $offerData)
                {
                    if(is_array($offerData) && count($offerData))
                    {
                        $offer = [];
                        $offer['campaign-id'] = trim(preg_replace('/\s\s+/','',$offerData['campaign_id']));
                        $offer['id'] = trim(preg_replace('/\s\s+/','',$offerData['campaign_id']));
                        $offer['name'] = trim(preg_replace('/\s\s+/','',$offerData['campaign_name']));
                        $offer['description'] = base64_encode(trim(preg_replace('/\s\s+/','',$offerData['campaign_description'])));
                        $offer['vertical'] = array_filter($offerData['categories']);
                        $offer['launch-date'] = date('m-d-Y');
                        $offer['expiring-date'] = date('m-d-Y');
                        $offer['rules'] = base64_encode(trim(preg_replace('/\s\s+/','',$offerData['restrictions'])));
                        $offer['suppression-list-link'] = '';
                        $offer['epc'] = '$' . trim(preg_replace('/\s\s+/','',$offerData['payout']));
                        $offer['rate'] = $offer['epc'];
                        $offer['flag'] = 'US';
                        $offer['key'] = 'sid';
                        
                        foreach ($offerData['country_ids'] as $countryId)
                        {
                            $offer['flag'] .= '/' .  strtoupper(trim($countryId));
                        }
                        
                        # get subjects and fromnames and creative
                        $offer['creatives'] = $this->getCreatives($offer);
                        
                        $offers[] = $offer; 
                    } 
                }
            }
 
            return $offers;
        }
    
        /**
         * @name getCreatives
         * @description get creatives
         */
        public function getCreatives(&$offer)
        {
            $creatives = [];
            
            if(count($offer)) 
            {
                $params = [
                    'key_id' => $this->_key,
                    'campaign_id' => $offer['id']
                ];

                $response = $this->getResponse(rtrim($this->_url,RDS) . RDS . 'pubs_creative_email' . RDS . 'get' .  RDS,$params,true);
            
                if($response != null && $response != "")
                {
                    $json = json_decode($response,true);
                    $data = json_decode($json['data'],true);
                    
                    if (count($data) && count($data['results']))
                    {
                        # add subjects to offer 
                        $subjects = json_decode($data['results']['subject_lines'],true);

                        if(count($subjects))
                        {
                            $offer['offer_subjects'] = [];
                            
                            foreach ($subjects as $subject)
                            {
                                $offer['offer_subjects'][] = $subject['content'];
                            }
                        }
                        
                        # add from names to offer 
                        $fromNames = json_decode($data['results']['from_lines'],true);

                        if(count($fromNames))
                        {
                            $offer['offer_names'] = [];
                            
                            foreach ($fromNames as $fromName)
                            {
                                $offer['offer_names'][] = $fromName['content'];
                            }
                        }
                        
                        # add emails to offer 
                        $emails = json_decode($data['results']['emails'],true);

                        if(count($emails))
                        {
                            foreach ($emails as $email)
                            {
                                $creative = [];
                                $creative['code'] = $email['content'];

                                $links = [];
                                $checkLinks = [];
                                $isPreview = false;

                                # get all links
                                $doc = new \DOMDocument();
                                $doc->loadHTML($creative['code']);
                                $xml = simplexml_import_dom($doc);

                                if($xml != null)
                                {
                                    $anchors = $xml->xpath('//a');

                                    if (count($anchors))
                                    {
                                        foreach ($anchors as $anchor)
                                        {
                                            $link = trim((string) $anchor['href']);

                                            if(filter_var(trim($link), FILTER_VALIDATE_URL) && !in_array(trim($link),$checkLinks))
                                            {
                                                $type = 'other';

                                                if(Strings::indexOf($link,$this->_previewURLPrefix . '=' . $this->_affiliateId) >= 0 && Strings::indexOf($link,strval($this->_affiliateId)) >= 0)
                                                {
                                                    $type = 'preview';
                                                    $isPreview = true;
                                                }
                                                else if(Strings::indexOf($link,"unsub") >= 0 || Strings::indexOf($link,"optout") >= 0 || Strings::indexOf($link,"remove") >= 0)
                                                {
                                                    $type = 'unsub';
                                                }

                                                $links[] = array('type' => $type , 'link' => trim($link));
                                                $checkLinks[] = trim($link);
                                            }
                                        }
                                    }
                                }

                                if(count($links) && $isPreview == false)
                                {
                                    $preview = $links[0]['link'];

                                    foreach ($links as &$linkRow) 
                                    {
                                        if(trim($linkRow['link']) == trim($preview))
                                        {
                                            $linkRow['type'] = 'preview';
                                        }
                                    }
                                }
                                
                                $creative['links'] = $links;
                                $creatives[] = $creative;
                            }
                        }
                    }
                }
            }
            
            return $creatives;
        }
        
        /**
         * @name getSuppressionFileLink
         * @description get suppression file link
         * @param integer $offerId
         * @return string link
         */
        public function getSuppressionFileLink($offer)
        {
            $link = '';
            $params = [
                'key_id' => $this->_key,
                'campaign_id' => $offer['production_id'],
                'md5' => 'false'
            ];

            $response = $this->getResponse(rtrim($this->_url,RDS) . RDS . 'pubs_campaign_suppression' . RDS . 'get' .  RDS,$params,true);

            if($response != null && $response != "")
            {
                $json = json_decode($response,true);
                $data = json_decode($json['data'],true);
                    
                if (count($data) && key_exists('download_link',$data))
                {
                    $link = str_replace([','], '', $data['download_link']);

                    # check if the supp is hosted in optizmo
                    if (filter_var($link, FILTER_VALIDATE_URL)) 
                    {
                        $components = parse_url($link);

                        if ($components['host'] == 'mailer.optizmo.net') 
                        {
                            $result = System::executeCommand('/usr/local/bin/casperjs ' . APPS_FOLDER. DS . Application::getPrefix() . DS . 'jobs' . DS . 'optizmo_scrabber.js ' . $link, true);
                            $link = count($result['output']) ? trim($result['output'][0]) : '';
                        }
                    }
                } 
            }

            return $link;
        }

        /**
         * @name getMonthEarning
         * @description get Month earning
         * @return float earnings
         */
        public function getMonthEarning()
        {
            $earnings = 0.0;

            

            return $earnings;
        }
    }
}



