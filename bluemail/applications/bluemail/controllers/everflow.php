<?php namespace \var\bluemail\applications\bluemail\controllers\api
{
    if (!defined('MAILTNG_FMW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
    /**
     * @framework       MailTng Framework
     * @version         1.1
     * @author          MailTng Team
     * @copyright       Copyright (c) 2015 - 2016.	
     * @license		
     * @link	
     */
    use ma\mailtng\core\Base as Base; 
    use ma\mailtng\types\Strings as Strings;
    use ma\mailtng\www\URL as URL;
    use ma\mailtng\os\System as System;
    use ma\mailtng\logging\Logger as Logger;
    /**
     * @name            everflow.class 
     * @description     It's a class that deals with everflow API methods
     * @package		\var\bluemail\applications\bluemail\controllers\api
     * @category        API
     * @author		MailTng Team			
     */
    class everflow extends Base
    {
        /**
         * @readwrite
         * @access protected 
         * @var array
         */
        protected $_url;

        /**
         * @readwrite
         * @access protected 
         * @var String
         */
        protected $_email;

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
         * @name getResponse
         * @description parses the xml returned by a response 
         * @param string $url
         * @param array $parameters
         */
        public function getResponse($url,$parameters,$key)
        {
			
			
		
             $curl = curl_init();

             curl_setopt_array($curl, array(
	         CURLOPT_URL => "$url".'/'."$parameters",
	         CURLOPT_RETURNTRANSFER => true,
	         CURLOPT_ENCODING => "",
	         CURLOPT_MAXREDIRS => 10,
	         CURLOPT_TIMEOUT => 30,
	         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	         CURLOPT_CUSTOMREQUEST => "GET",
	         CURLOPT_HTTPHEADER => array(
		             "x-eflow-api-key: $key",
		             "content-type: application/json"
	                                    ),
                                           ));

             $response1 = curl_exec($curl);
             $err = curl_error($curl);

             curl_close($curl);

           if ($err) {
	      
			//$myfile = fopen("log.txt", "w") or die("Unable to open file!");
            $txt = $err;
			 # log the message error
            Logger::error($txt);
            //fwrite($myfile, $txt);
            //fclose($myfile);
                     } 
					 
			else {
							 
			//$myfile = fopen("log.txt", "w") or die("Unable to open file!");
            $json = $response1;
			$result = json_decode($json, true);
			//$output = print_r($result, true);
            //fwrite($myfile, $output);
            //fclose($myfile);
	        
                            }
            
            return $result;
		
        }

        /**
         * @name getOffers
         * @description get offers
         */
        function getOffers($offerIds)
        {
            $offers = [];
            
            if(is_array($offerIds))
            {
                foreach ($offerIds as $offerId)
                {
                    $offers[] = $this->getOffer($offerId);
                }
            }
            
            return $offers;
        }
        
        /**
         * @name getOffer
         * @description get offer by id 
         * @param integer $campaignId is the ID of the offer in the sponsure ex: 9735
         * @return array the offer
         */
        public function getOffer($campaignId)
        {
            $offer = array();
			
            
            try
            {
                
                $response = $this->getResponse(trim($this->_url,RDS) . RDS . 'offers' , $campaignId, $this->_key);
				

                if (count($response)) 
                {   
                    # convert offer data into array 
                    $offerResponse = $response;

                    if(count($offerResponse) && trim($offerResponse['offer_status']) == 'active')
                    { 
                        # fill the offer 
                        $offer['campaign-id'] = $campaignId;
                        $offer['id'] = $offerResponse['network_offer_id'];
                        $offer['name'] = $offerResponse['name'];

                        $flags = array();
 
                        if(key_exists('countries',$offerResponse['relationship']['ruleset']))
                        {     
                            $allowedCountries = count($offerResponse['relationship']['ruleset']['countries']) == 1 ? array($offerResponse['relationship']['ruleset']['countries']) : $offerResponse['relationship']['ruleset']['countries'];
                            
                            if(count($allowedCountries))
                            {
								
                                //$countries = $offerResponse['relationship']['ruleset']['countries'];
								$countries = key_exists('country_code',$offerResponse['relationship']['ruleset']['countries']) ? array($offerResponse['relationship']['ruleset']['countries']) : $offerResponse['relationship']['ruleset']['countries'];
                               
                                foreach ($countries as $country) 
                                {
									
                                    $flags[] = $country['country_code'] == 'GB' ? 'UK' : $country['country_code'];
									
                                }
                            }
                      							
                        }
                        

                        $offer['flag'] = trim(join('/', $flags),'/');
                        $offer['description'] = base64_encode($offerResponse['html_description']);
                        $offer['rate'] = $offerResponse['relationship']['payouts']['entries'][0]['payout_amount'];
                        $offer['launch-date'] = date('Y-m-d');
                        $offer['expiring-date'] = date('Y-m-d');
                        $offer['vertical'] = $offerResponse['relationship']['category']['name'];
                        $offer['rules'] = base64_encode($offerResponse['html_description']);
                        $offer['suppression-list-link'] = key_exists('suppression_file_link', $offerResponse['relationship']['email_optout']) ? $offerResponse['relationship']['email_optout']['suppression_file_link'] : '';
                        $offer['epc'] = $offerResponse['currency_id'];
						$offer_names = explode(PHP_EOL,$offerResponse['relationship']['email']['from_lines']);
                        $offer['offer_names'] = $offer_names;
                        $offer['offer_names'] = is_array($offer['offer_names']) ? $offer['offer_names'] : array($offer['offer_names']);
						
						$offer_subjects = explode(PHP_EOL,$offerResponse['relationship']['email']['subject_lines']);
                        $offer['offer_subjects'] = $offer_subjects;
                        $offer['offer_subjects'] = is_array($offer['offer_subjects']) ? $offer['offer_subjects'] : array($offer['offer_subjects']);
                        $offer['key'] = 's2';
                        
                        # getting creatives
                        $offer['creatives'] = $this->getOfferCreatives($offerResponse,$campaignId);
                    } 
                }
            } 
            catch (\SoapFault $e) 
            {
                # log the message error
                Logger::error($e);
            }

            return $offer;
        }
        
        /**
         * @name getOfferCreatives
         * @description get the offer creatives list 
         * @param array $offerResponse
         * @return array the creatives result
         */
        public function getOfferCreatives($offerResponse,$campaignId)
        {
            $results = array();
            
            if(isset($offerResponse) && count($offerResponse) && key_exists('creatives',$offerResponse['relationship']))
            {
                $creatives = is_array($offerResponse['relationship']['creatives']['entries']) ? $offerResponse['relationship']['creatives']['entries'] : array($offerResponse['relationship']['creatives']['entries']);
                
                foreach ($creatives as $creative) 
                {
                    if(isset($creative['creative_type'])&& trim(strtolower($creative['creative_type']))== 'email' && trim(strtolower($creative['creative_status'])) == 'active')
                    {
                        $fileDirectory = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'creatives' . DS . Strings::generateRandomText(5,true,true,true,false);
                        $fileName = 'crt_' . Strings::generateRandomText(5,true,true,true,false) . '.zip';

                        # create a temp directory
                        System::executeCommand('mkdir -p ' . $fileDirectory,true);
                        
                        # download the zip file
                        if(filter_var($offerResponse['relationship']['creative_bundle']['url'],FILTER_VALIDATE_URL) && file_put_contents($fileDirectory . DS . $fileName,file_get_contents($offerResponse['relationship']['creative_bundle']['url'])))
                        {
                            # unzip the downloaded file 
                            System::executeCommand("unzip " . $fileDirectory . DS . $fileName . " -d " . $fileDirectory . DS,true);
                            
                            //$images = glob($fileDirectory."/*.{jpg,jpeg,png,gif}", GLOB_BRACE);
                            $creativeContent = trim(mb_convert_encoding($creative['html_code'], "UTF-8"),'?');
                            
                            if(count($images))
                            {
                                foreach ($images as $image) 
                                {
                                    $filePath = trim($image);
                                    $pathParts = pathinfo($filePath);
                                    $extension = key_exists('extension',$pathParts) ? $pathParts['extension'] : 'jpg';
                                    $fileName = Strings::generateRandomText(8,true,false,true,false) . '.' . $extension;

                                    if(!file_exists(ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'images' . DS . $fileName))
                                    {
                                        # move the image to our tmp images folder 
                                        System::executeCommand('mv ' . $filePath . ' ' . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'images' . DS . $fileName);
                                    }

                                    $creativeContent = str_replace(basename($filePath),URL::getCurrentApplicationURL() . "/tmp/images/" . $fileName, $creativeContent);
                                }
                            }
                            
                            $links = array();
                            $checkLinks = array();



                                 # creat all links 
                                $tag="[LINK]";
								if(filter_var($offerResponse['tracking_url'],FILTER_VALIDATE_URL))
								{
									
                                $tracking_url=$offerResponse['tracking_url'];
								$network_offer_creative_id=$creative['network_offer_creative_id'];
								#replace all tags by the real links
								$tracking_url_creative=$tracking_url."?creative_id=".$network_offer_creative_id;
								$creativeContent = str_replace($tag,$tracking_url_creative, $creativeContent);
								# add links
								#offer link
								$links[] = array('type' => 'preview' , 'link' => trim($tracking_url_creative));
								#unseb link
								$unsub_link=$offerResponse['relationship']['email_optout']['unsub_link'];
								$links[] = array('type' => 'unsub' , 'link' => trim($unsub_link));
								#Preview the leading page
								$preview_url=$offerResponse['preview_url'];
								$links[] = array('type' => 'other' , 'link' => trim($preview_url));
								}

                            $creativeArray['links'] = $links;
                            $creativeArray['code'] = $creativeContent;
                            $results[] = $creativeArray;


                          
                        }
                        
                        # remove the temp directory
                        System::executeCommand('rm -rf ' . $fileDirectory);
                    }
                }
            }

            return $results;
        }
		
		   public function getSuppressionList($campaignId){
			   
            
			
			$response = $this->getResponse(trim($this->_url,RDS) . RDS . 'offers' , $campaignId, $this->_key);
           
            if (count($response)) {
				 $offerResponse = $response;
            if(count($offerResponse) && trim($offerResponse['offer_status']) == 'active')
                    {
				
			$offerSupLink['suppression-list-link'] = key_exists('suppression_file_link', $offerResponse['relationship']['email_optout']) ? $offerResponse['relationship']['email_optout']['suppression_file_link'] : '';
                return $offerSupLink['suppression-list-link'];
					}
            }else{
                return "Suppression file not found!!";
            }
        }
        
        
        /**
         * @name getCreativeContent
         * @description get creative content
         * @param integer $campaignId
         * @param integer $creativeId
         * @return array the creatives result
         */
       /* public function getCreativeContent($campaignId,$creativeId)
        {
            $content = '';  
            $parameters = array('api_key' => $this->getKey(),'affiliate_id' => $this->_affiliateId, 'campaign_id' => intval($campaignId), 'creative_id' => intval($creativeId));
            $response = $this->getResponse(trim($this->_url,RDS) . RDS . 'offers.asmx' . RDS . 'GetCreativeCode', $parameters);

            if (count($response) && key_exists('creative_files',$response) && key_exists('creative_file',$response['creative_files']))
            {
                $content = $response['creative_files']['creative_file']['file_content'];
            }
            
            return $content; 
        }
        */
        
        
        /**
         * @name getMonthEarning
         * @description get Month earning
         * @return float earnings
         */
        public function getMonthEarning()
        {
            $earnings = 0.0;
			
       $url=$this->_url;
	   $parameters="reporting/daily";
	   $key=$this->_key;
	   $date=date('Y-m-d');

$curl = curl_init();

curl_setopt_array($curl, array(
	CURLOPT_URL => "$url".'/'."$parameters",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "POST",
	CURLOPT_POSTFIELDS => '{
  "from": '.$date.',
  "to": '.$date.',
  "timezone_id": 0,
  "currency_id": ""
}',
	CURLOPT_HTTPHEADER => array(
		"x-eflow-api-key: $key",
		"content-type: application/json"
	),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	echo "cURL Error #:" . $err;
} else {
	// $myfile = fopen("log2.txt", "w") or die("Unable to open file!");
            $json = $response;
			$result = json_decode($json, true);
			//$output = print_r($result, true);
            //fwrite($myfile, $output);
            //fclose($myfile);
}

return $response;
			
			
			
	

            /*if (count($response) && key_exists('periods',$response) && key_exists('period',$response['periods']) && count($response['periods']['period']) > 3 && $response['periods']['period'][3] != null)
            {
                $monthly = $response['periods']['period'][3];
                $earnings += floatval(str_replace("$","",$monthly['current_revenue']));
            }
            
            return $earnings;
			
			*/
        }
    }
}



