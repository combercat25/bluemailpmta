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
    use ma\mfw\application\Application as Application;
    use ma\mfw\database\Database as Database;
    use ma\mfw\http\Request as Request;
    use ma\mfw\http\Response as Response;
    use ma\mfw\http\Session as Session;
    use ma\mfw\www\URL as URL;  
    use ma\mfw\types\Strings as Strings;
    use ma\applications\bluemail\models\admin\Sponsor as Sponsor;
    use ma\applications\bluemail\helpers\PagesHelper as PagesHelper;
    /**
     * @name            Images.controller 
     * @description     The Images controller
     * @package		ma\applications\bluemail\controllers
     * @category        Controller
     * @author		Miami Team			
     */
    class Images extends Controller 
    {
        /**
         * @name init
         * @description initializing proccess before the action method executed
         * @once
         * @protected
         */
        public function init() 
        {
            # connect to the default database 
            Database::secureConnect();

            # check authentication
            $user = Session::get('bluemail_connected_user');  
            
            if(!isset($user))
            {
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'authentication' . RDS . 'login.html');
            }
        }

        /**
         * @name index
         * @description the index action
         * @before init
         * @after setMenu,closeConnection
         */
        public function index() 
        {
            # get all sponsors from the database
            $sponsors = Sponsor::all(true,array('status_id = ? ',1));
            $results = Session::getThenDel('results');
            
            # set the data to the template
            $this->getPageView()->set('sponsors',$sponsors);
            $this->getPageView()->set('results',$results);

            # check for message 
            PagesHelper::checkForMessageToPage($this);
        }
 
        /**
         * @name upload
         * @description the upload action
         * @before init
         * @after setMenu,closeConnection
         */
        public function upload() 
        {     
            # retrieves the data from post
            $this->setShowMasterView(false);
            $this->setShowPageView(false);
            
            $offerImage = Request::getParameterFromFILES('offer-image');
            $unsubImage = Request::getParameterFromFILES('unsub-image');
            $optoutImage = Request::getParameterFromFILES('optout-image');
            
            $messageFlag = "danger";
            $message = "Please check be sure that you have selected everything!";
            
            if(isset($offerImage) && count($offerImage) && $offerImage['size'] > 0 && isset($unsubImage) && count($unsubImage) && $unsubImage['size'] > 0 && isset($optoutImage) && count($optoutImage) && $optoutImage['size'] > 0 )
            {
       
                # move offer image file  
                $filePath = trim($offerImage['name']);
                $pathParts = pathinfo($filePath);
                $extension = key_exists('extension',$pathParts) ? $pathParts['extension'] : 'jpg';
                $offerImageName = Strings::generateRandomText(8,true,false,true,false) . '.' . $extension;
                move_uploaded_file($offerImage['tmp_name'],ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'images' . DS . $offerImageName);
                
                # move unsub image file  
                $filePath = trim($unsubImage['name']);
                $pathParts = pathinfo($filePath);
                $extension = key_exists('extension',$pathParts) ? $pathParts['extension'] : 'jpg';
                $offerUnsubName = Strings::generateRandomText(8,true,false,true,false) . '.' . $extension;
                move_uploaded_file($unsubImage['tmp_name'],ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'images' . DS . $offerUnsubName);
                
                # move optout image file  
                $filePath = trim($optoutImage['name']);
                $pathParts = pathinfo($filePath);
                $extension = key_exists('extension',$pathParts) ? $pathParts['extension'] : 'jpg';
                $offerOptOutName = Strings::generateRandomText(8,true,false,true,false) . '.' . $extension;
                move_uploaded_file($optoutImage['tmp_name'],ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'images' . DS . $offerOptOutName);
                
                $html = '';
                $file = APPS_FOLDER . DS . Application::getPrefix() . DS . DEFAULT_ASSETS_DIRECTORY . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'interface' . DS . 'creative.tpl';

                if(file_exists($file))
                {
                    $html = file_get_contents($file);
                }
                
                $html = str_replace([
                    '{preview_image}',
                    '{unsub_image}',
                    '{optout_image}',
                ],[
                    "http://[domain]/img/" . $offerImageName,
                    "http://[domain]/img/" . $offerUnsubName,
                    "http://[domain]/img/" . $offerOptOutName
                ],$html);
                
                
                $productionServers = \ma\applications\bluemail\models\admin\Server::all(true,array('status_id = ?',array(1)),array('id','name','main_ip','username','password','ssh_port'));
                
                $index = 0;

                foreach ($productionServers as $server) 
                {
                    $sshAuthenticator = new \ma\mfw\ssh2\SSHPasswordAuthentication($server['username'],$server['password']);
                    $sshConnector = new \ma\mfw\ssh2\SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                    if($sshConnector->isConnected())
                    {
                        $sshConnector->scp('send',array(ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'images' . DS . $offerImageName,'/var/www/html/img/' . $offerImageName));
                        $sshConnector->scp('send',array(ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'images' . DS . $offerUnsubName,'/var/www/html/img/' . $offerUnsubName));
                        $sshConnector->scp('send',array(ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'images' . DS . $offerOptOutName,'/var/www/html/img/' . $offerOptOutName));
                        $sshConnector->disconnect();
                    }
                    
                    $index++;
                }
                    
                $messageFlag = "success";
                $message = "Images Uploaded Successfully!";

                Session::set('results',$html);
            }
            
            # stores the message in the session 
            Session::set('proccess_message_flag',$messageFlag);
            Session::set('proccess_message',$message);
            
            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'images.html'); 
        }

        /**
         * @name setMenu
         * @description set the current menu to the template
         * @protected
         */
        public function setMenu() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_images',true);
        }

        /**
         * @name closeConnection
         * @description close any open connections
         * @protected
         */
        public function closeConnection() 
        {
            # disconnect from all databases 
            Database::secureDisconnect();
        }  
    } 
}