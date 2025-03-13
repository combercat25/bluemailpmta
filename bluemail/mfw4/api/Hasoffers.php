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
    use ma\mfw\core\Base as Base; 
    use ma\mfw\types\Strings as Strings;
    use ma\mfw\types\Arrays as Arrays;
    use ma\mfw\os\System as System;
    use ma\mfw\application\Application as Application;
    use ma\mfw\exceptions\types\BackendException as BackendException;
    /**
     * @name            Hasoffers.class 
     * @description     It's a class that deals with Hasoffers API methods
     * @package		ma\mfw\api
     * @category        API
     * @author		Miami Team			
     */
    class Hasoffers extends Base
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
        protected $_previewURLPrefix = 'aff_id';

        /**
         * @name getResponse
         * @description get Response
         */
        public function getResponse($data)
        {
            $result = [];

            if (filter_var($this->_url, FILTER_VALIDATE_URL))
            {
                # Initiate the REST call via curl
                $ch = \curl_init($this->_url . '?' . http_build_query($data));
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

            $parameters = [
                'api_key' => $this->_key,
                'Target' => 'Affiliate_Offer',
                'Method' => 'findAll'
            ];

            $response = $this->getResponse($parameters);
 
            if ($response != null)
            {
                $json = json_decode($response, true);
                
                if (count($json) && key_exists('response', $json))
                {
                    # check if there is an error
                    if ($json['response']['status'] == -1)
                    {
                        throw new BackendException('Error while getting offers : ' . $json['response']['errorMessage']);
                    }

                    # check if there is no offer data
                    if (count($json['response']['data']) == 0)
                    {
                        throw new BackendException('No Offers Found !');
                    }

                    foreach ($json['response']['data'] as $row)
                    {
                        $offerData = $row['Offer'];

                        # check if there is no offer data
                        $existanceCondition = count($offerData) > 0;

                        # check if this offer is Active
                        $statusCondition = strtolower(Strings::trim($offerData['approval_status'])) == 'approved';

                        if ($existanceCondition && $statusCondition)
                        {
                            if (count($ids) == 0 || in_array(intval(Strings::trim($offerData['id'])), $ids))
                            {
                                $offer = [];
                                $offer['id'] = Strings::trim($offerData['id']);
                                $offer['campaign-id'] = Strings::trim($offerData['id']);
                                $offer['name'] = Strings::trim($offerData['name']);
                                $offer['description'] = base64_encode(Strings::trim($offerData['description']));
                                $offer['rules'] = Strings::trim($offerData['require_terms_and_conditions']) == '1' ? base64_encode(Strings::trim($offerData['terms_and_conditions'])) : base64_encode('No Rules !');
                                $offer['epc'] = $offer['rate'] = '$' . number_format(floatval(Strings::trim($offerData['default_payout'])), 2);
                                $offer['expiring-date'] = date_format(date_create(Strings::trim($offerData['expiration_date'])), 'Y-m-d');
                                $offer['launch-date'] = date('m-d-Y');
                                
                                # get from names 
                                $offer['offer_names'] = [];
                                $fromNames = explode(PHP_EOL, $offerData['email_instructions_from']);

                                foreach ($fromNames as $fromName)
                                {
                                    $fromName = Strings::trim($fromName);

                                    if ($fromName != '' && strpos($fromName, '================') === FALSE && strpos($fromName, '<b>') === FALSE && strpos($fromName, '<p>') === FALSE && strpos($fromName, '<h1>') === FALSE && strpos($fromName, '<span>') === FALSE)
                                    {
                                        $offer['offer_names'][] = $fromName;
                                    }
                                }

                                # get from names 
                                $offer['offer_subjects'] = [];
                                $subjects = explode(PHP_EOL, $offerData['email_instructions_subject']);

                                foreach ($subjects as $subject)
                                {
                                    $subject = Strings::trim($subject);

                                    if ($subject != '' && strpos($subject, '================') === FALSE && strpos($subject, '<b>') === FALSE && strpos($subject, '<p>') === FALSE && strpos($subject, '<h1>') === FALSE && strpos($subject, '<span>') === FALSE)
                                    {
                                        $offer['offer_subjects'][] = $subject;
                                    }
                                }

                                # unsub and suppression
                                $offer['unsubscribe-link'] = Strings::trim($offerData['dne_unsubscribe_url']);
                                $offer['suppression-list-link'] = Strings::trim($offerData['dne_download_url']);
                                
                                # set varticals 
                                $this->_setVerticals($offer);

                                # set countries
                                $this->_setCountries($offer);

                                # set creatives 
                                $this->_setCreatives($offer);

                                $offers[] = $offer;
                            }
                        }
                    }
                }
            }

            return $offers;
        }

        /**
         * @name getOffers
         * @description get offers
         */
        function getOffer($offerId)
        {
            $offer = $this->getOffers([$offerId]);
            return $offer;
        }

        /**
         * @name _setVerticals
         * @description set verticals to offer 
         * @param array $offer
         * @return
         */
        protected function _setVerticals(array &$offer)
        {
            $verticals = [];

            $parameters = [
                'api_key' => $this->_key,
                'Target' => 'Affiliate_Offer',
                'Method' => 'getCategories',
                'ids[]' => intval($offer['id'])
            ];

            $response = $this->getResponse($parameters);

            if ($response != null)
            {
                $json = json_decode($response, true);
                
                if (count($json) && key_exists('response', $json))
                {
                    $categories = $json['response']['data'][0]['categories'];

                    # check if there is an error
                    if ($json['response']['status'] == 1 && count($categories))
                    {
                        foreach ($categories as $category)
                        {
                            $verticals[] = Strings::trim($category['name']);
                        }
                    }
                }
            }

            $offer['vertical'] = join('/',$verticals);
        }

        /**
         * @name _setCountries
         * @description set countries to offer 
         * @param array $offer
         * @return
         */
        protected function _setCountries(array &$offer)
        {
            $countries = '';

            $parameters = [
                'api_key' => $this->_key,
                'Target' => 'Affiliate_Offer',
                'Method' => 'getTargetCountries',
                'ids[]' => intval($offer['id'])
            ];

            $response = $this->getResponse($parameters);

            if ($response != null)
            {
                $json = json_decode($response, true);

                if (count($json) && key_exists('response', $json))
                {
                    $results = $json['response']['data'][0]['countries'];

                    # check if there is an error
                    if ($json['response']['status'] == 1 && count($results))
                    {
                        foreach ($results as $value)
                        {
                            $countries .= Strings::trim($value['code']) . RDS;
                        }
                    }
                }
            }
            
            $offer['flag'] = trim($countries, RDS) == '' ? 'US' : rtrim($countries, RDS);
        }

        /**
         * @name _generateTrackingLink
         * @description generate tracking link by offer id 
         * @param integer $offerId
         * @return string tracking link
         */
        protected function _generateTrackingLink($offerId)
        {
            $trackingLink = '';

            $parameters = [
                'api_key' => $this->_key,
                'Target' => 'Affiliate_Offer',
                'Method' => 'generateTrackingLink',
                'offer_id' => $offerId
            ];

            $response = $this->getResponse($parameters);

            if ($response != null)
            {
                $json = json_decode($response, true);

                if (count($json) && key_exists('response', $json))
                {
                    $results = $json['response']['data'];

                    # check if there is an error
                    if ($json['response']['status'] == 1 && count($results) > 0)
                    {
                        $trackingLink = $results['click_url'];
                    }
                }
            }

            return $trackingLink;
        }

        /**
         * @name getCreatives
         * @description set creatives to offer
         * @param array $offer
         * @return
         */
        protected function _setCreatives(&$offer)
        {
            $creatives = [];

            $parameters = [
                'api_key' => $this->_key,
                'Target' => 'Affiliate_OfferFile',
                'Method' => 'findAll',
                'filters[offer_id]' => intval($offer['id']),
                'filters[type]' => 'email creative',
                'limit' => 10000
            ];

            $response = $this->getResponse($parameters);

            if ($response != null)
            {
                $json = json_decode($response, true);

                if (count($json) && key_exists('response', $json))
                {
                    $results = $json['response']['data']['data'];

                    # check if there is an error
                    if ($json['response']['status'] == 1 && count($results))
                    {
                        $trackingLink = $this->_generateTrackingLink(intval($offer['id']));
                        
                        foreach ($results as $value)
                        {
                            $code = str_replace(['tracking_link', $trackingLink, '{' . $trackingLink . '}'], $trackingLink, $value['OfferFile']['code']);
                            $code = str_replace(['{unsubscribe_link}', $offer['unsubscribe-link'], '{' . $offer['unsubscribe-link'] . '}'], $offer['unsubscribe-link'], $code);

                            $creative = [
                                'code' => $code
                            ];

                            $links = [];
                            $checkLinks = [];

                            # get all links
                            $doc = new \DOMDocument();
                            $doc->loadHTML($creative['code']);
                            $xml = simplexml_import_dom($doc);

                            if ($xml != null)
                            {
                                $anchors = $xml->xpath('//a');

                                if (count($anchors))
                                {
                                    foreach ($anchors as $anchor)
                                    {
                                        $link = trim((string) $anchor['href']);

                                        if (filter_var(trim($link), FILTER_VALIDATE_URL) && !in_array(trim($link), $checkLinks))
                                        {
                                            $type = 'other';

                                            if (Strings::indexOf($link, $trackingLink) >= 0 || Strings::indexOf($link,$this->_previewURLPrefix) >= 0)
                                            {
                                                $type = 'preview';
                                            } 
                                            else if (Strings::indexOf($link, "unsub") >= 0 || Strings::indexOf($link, "optout") >= 0 || Strings::indexOf($link, "remove") >= 0 || Strings::indexOf($link, "/oo/oo") >= 0)
                                            {
                                                $type = 'unsub';
                                            }

                                            $links[] = ['type' => $type, 'link' => trim($link)];
                                            $checkLinks[] = trim($link);
                                        }
                                    }
                                }
                            }

                            $creative['code'] = $creative['code'];
                            $creative['links'] = $links;

                            if (count($creative['links']) > 0)
                            {
                                $creatives[] = $creative;
                            }
                        }
                    }
                }
            }

            $offer['creatives'] = $creatives;
        }

        /**
         * @name getSuppressionFileLink
         * @description get suppression file link
         * @param integer $offerId
         * @return string link
         */
        public function getSuppressionFileLink($offer)
        {
            $link = $offer['suppression_list'];
            $components = parse_url($link);

            if ($components['host'] == 'mailer.optizmo.net') 
            {
                putenv("PHANTOMJS_EXECUTABLE=/usr/local/bin/phantomjs");
                $result = System::executeCommand('/usr/local/bin/casperjs ' . APPS_FOLDER. DS . Application::getPrefix() . DS . 'jobs' . DS . 'optizmo_scrabber.js ' . $link, true);
                $link = count($result['output']) ? trim($result['output'][0]) : '';
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

            $startDate = date('01-m-Y');
            $endDate = date('d-m-Y');

            $parameters = [
                'Method' => 'getConversions',
                'api_key' => Arrays::getElement(explode('|',$this->_key),1),
                'NetworkId' => Arrays::getElement(explode('|',$this->_key),0),
                'filters[Stat.date][conditional]' => 'BETWEEN',
                'filters[Stat.date][values][0]' => $startDate,
                'filters[Stat.date][values][1]' => $endDate,
                'limit' => 10000000000,
                'fields' => ['Stat.approved_payout']
            ];

            $response = $this->getResponse($parameters);

            if($response != null)
            {
                $json = json_decode($response,true);

                if(count($json) && key_exists('response',$json))
                {
                    $results = $json['response']['data']['data'];

                    if(count($results))
                    {
                        foreach ($results as $row)
                        {
                            $earnings += floatval($row['Stat']['approved_payout']);
                        }
                    }
                }
            }
            return $earnings;
        }
    }
}



