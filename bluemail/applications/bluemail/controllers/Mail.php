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
    use ma\mfw\http\Response as Response;
    use ma\mfw\application\Application as Application;
    use ma\mfw\http\Session as Session;
    use ma\mfw\www\URL as URL;  
    use ma\mfw\types\Arrays as Arrays;
    use ma\mfw\configuration\Configuration as Configuration;
    use ma\mfw\globals\Server as GloblServers;
    use ma\mfw\http\Request as Request;
    use ma\mfw\types\Strings as Strings;
    use ma\mfw\files\Paths as Paths;
    use ma\applications\bluemail\models\admin\Isp as Isp;
    use ma\applications\bluemail\models\admin\Ip as Ip;
    use ma\applications\bluemail\models\admin\Domain as Domain;
    use ma\applications\bluemail\models\admin\Server as Server;
    use ma\applications\bluemail\models\admin\Sponsor as Sponsor;
    use ma\applications\bluemail\models\admin\Offer as Offer;
    use ma\applications\bluemail\models\admin\OfferFromName as OfferName;
    use ma\applications\bluemail\models\admin\OfferSubject as OfferSubject;
    use ma\applications\bluemail\models\admin\OfferCreative as OfferCreative;
    use ma\applications\bluemail\models\admin\OfferLink as OfferLink;
    use ma\applications\bluemail\models\admin\Vertical as Vertical;
    use ma\applications\bluemail\models\admin\Header as Header;
    use ma\applications\bluemail\models\admin\ServerProvider as ServerProvider;
    use ma\mfw\ssh2\SSH as SSH;
    use ma\mfw\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
    use ma\applications\bluemail\models\data\DataType as DataType;
    use ma\applications\bluemail\models\data\DataList as DataList;
    use ma\applications\bluemail\models\production\Drop as Drop;
    use ma\applications\bluemail\models\admin\Vmta as Vmta;
    use ma\applications\bluemail\helpers\PagesHelper as PagesHelper;
    use ma\mfw\exceptions\types\PageException as PageException;

    /**
     * @name            Mail.controller 
     * @description     The Mail controller
     * @package		ma\applications\bluemail\controllers
     * @category        Controller
     * @author		Miami Team			
     */
    class Mail extends Controller 
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

            # check authorization access
            if(!in_array(Arrays::getElement($user,'application_role_id'),array(1,2)))
            {
                throw new PageException("403 Access Denied",403);
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
            # get the connected user
            $user = Session::get('bluemail_connected_user'); 
                
            $arguments = func_get_args();          
            $dropId = isset($arguments) && count($arguments) ? $arguments[0] : null;

            if(isset($dropId) && is_numeric($dropId))
            {
               
                # get the drop from the database
                $drop = Drop::first(true,array('id = ?',$dropId));
                $this->getPageView()->set('drop',$drop['post_data']);
                $this->getPageView()->set('proccess',$drop['sent_progress']);
            }
                
            # get all servers from the database
            $serverProviders = ServerProvider::all(true,array('status_id = ? ',1),array('id','name'));
            
            if(!in_array($user['application_role_id'],[1]))
            {
                $servers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,provider_id FROM admin.servers WHERE server_type_id > 1 AND authorized_users LIKE '%,{$user['id']},%'",true);
            }
            else
            {
                $servers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id','name','provider_id'));
            }
            
            # check for providers that has no servers in production 
            foreach ($serverProviders as $index => $provider) 
            {
                $count = 0;
                
                foreach ($servers as $server) 
                {
                    if($server['provider_id'] == $provider['id'])
                    {
                        $count++;
                    }
                }
                
                if($count == 0)
                {
                    unset($serverProviders[$index]);
                }
            }
            
            # get all sponsors from the database
            $sponsors = Sponsor::all(true,array('status_id = ? ',1));
            
            # get all sponsors from the database
            $verticals = Vertical::all(true,array('status_id = ? ',1));
            
            # get all providers from the database
            $dataTypes = DataType::all(true,array('status_id = ? ',1),array('*'),'id','ASC');

            # get all isps from the database
            if(!in_array($user['application_role_id'],[1]))
            {
                $isps = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.isps WHERE status_id = 1 AND authorized_users LIKE '%,{$user['id']},%'",true);
            }
            else
            {
                $isps = Isp::all(true,array('status_id = ? ',1));
            }
 
            # get all domains from the database
            $domains = Domain::all(true,array('status_id = ? AND domain_status = ?',array(1,'Redirect')));
      
            # get the header template 
            $header = '';
            $file = APPS_FOLDER . DS . Application::getPrefix() . DS . DEFAULT_ASSETS_DIRECTORY . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'interface' . DS . 'header.tpl';

            if(file_exists($file))
            {
                $header = file_get_contents($file);
            }
            
            $headers = Header::all(true,array('user_id = ?',intval(Arrays::getElement($user,'id'))));
                
            # get the header template 
            $help = '';
            $file = APPS_FOLDER . DS . Application::getPrefix() . DS . DEFAULT_ASSETS_DIRECTORY . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'interface' . DS . 'help.tpl';

            if(file_exists($file))
            {
                $help = file_get_contents($file);
            }

            # get pmta port
            $configuration = new Configuration(array( "type" => "ini" ));
            $result = $configuration->initialize()->parse(Paths::getCurrentApplicationRealPath() . DS . DEFAULT_CONFIGS_DIRECTORY . DS . 'pmta',false);
            $pmtaPort = (count($result) > 0 && key_exists('pmta_http_port',$result)) ? $result['pmta_http_port'] : 8080;

            # set the data into the template data system 
            $this->getPageView()->set('servers',$servers);
            $this->getPageView()->set('serverProviders',$serverProviders);
            $this->getPageView()->set('isps',$isps);
            $this->getPageView()->set('domains',$domains);
            $this->getPageView()->set('header',$header);
            $this->getPageView()->set('headers',$headers);
            $this->getPageView()->set('help',str_replace(array("\r","\n"),"",$help));
            $this->getPageView()->set('dataTypes',$dataTypes);  
            $this->getPageView()->set('sponsors',$sponsors); 
            $this->getPageView()->set('verticals',$verticals); 
            $this->getPageView()->set('pmtaPort',$pmtaPort);
        }
        
       /**
         * @name send
         * @description create a new mail instance to mail it with PowerMTA
         * @before init
         * @after closeConnection
         */
        public function send() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # get the connected user
                $user = Session::get('bluemail_connected_user');

                # retrieve all form data
                $data = Request::getAllDataFromPOST();

                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
             
                # insert the user id 
                $data['user-id'] = Arrays::getElement($user,'id',0);
                    
                $isDrop = array_key_exists('drop',$data) ? true : false;
                $serversIds = Arrays::getElement($data,"servers",[]);
                $vmtas = Arrays::getElement($data,"selected-vmtas",[]);
                $lists = Arrays::getElement($data,"lists",null);
                $limit = intval(Arrays::getElement($data,"data-count",0));
                $recipientsEmails = array_key_exists('recipients-emails',$data) ? explode(";",Arrays::getElement($data,'recipients-emails')) : null;

                $receipientsCount = 0;

                if(count($serversIds) == 0)
                {
                    die(json_encode(array("type" => "error" , "message" => "No Server Selected !")));
                }

                if(!in_array($user['application_role_id'],[1])) 
                {
                    $servers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id FROM admin.servers WHERE server_type_id > 1 AND id IN (" . implode(',',$serversIds) . ") AND authorized_users LIKE '%,{$user['id']},%'",true);
                }
                else
                {
                    $servers = Server::all(true,array("id IN (" . implode(',',$serversIds) . ")",""),array('id'));
                }

                if(count($servers) != count($serversIds))
                {
                    die(json_encode(array("type" => "error" , "message" => "Please refresh your page , it looks like there are some unauthorized servers selected !")));
                }

                # vmtas validation
                if(count($vmtas) == 0)
                {
                    die(json_encode(array("type" => "error" , "message" => "Please check your VMTAs , it looks like there is no VMTAs selected !")));
                }

                # limit validation
                if($isDrop == true && $limit == 0)
                {
                    die(json_encode(array("type" => "error" , "message" => "Please Enter Data Limit !")));
                }
                
                # vmtas validation
                 if($isDrop == true && $limit < count($vmtas))
                {
                    die(json_encode(array("type" => "error" , "message" => "Please You're Sending just " . $limit . " email with " . count($vmtas) . " Vmtas ! \nYou have to send " . count($vmtas) . " emails Max With these " . count($vmtas) . " Vmtas !")));
                }
                
                # recipients validation
                if(count($recipientsEmails))
                {
                    $invalidEmails = false;

                    foreach ($recipientsEmails as $email) 
                    {
                        $email = preg_replace( "/\r|\n/","", trim($email));

                        if(!empty($email) && !filter_var($email,FILTER_VALIDATE_EMAIL))
                        {
                            $invalidEmails = true;
                        }

                        if(filter_var($email, \FILTER_VALIDATE_EMAIL))
                        {
                            $receipientsCount++;
                        }
                    }

                    if($invalidEmails == true)
                    {
                        die(json_encode(array("type" => "error" , "message" => "Please check your recipients , it looks like there is some invalid emails !")));
                    }
                }

                # recipients count validation
                if ($receipientsCount == 0)
                {
                    die(json_encode(array("type" => "error" , "message" => "Please insert at least one recipient!")));
                }

                # lists validation
                if($isDrop == true)
                {
                    if(count($lists))
                    {
                        foreach ($lists as &$list) 
                        {
                            $list = explode("|",$list);

                            if(count($list) == 3)
                            {
                                $tableName = trim($list[2]);

                                # switch the default database 
                                Database::switchToDefaultDatabase();

                                $authorised = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.data_lists WHERE name = '$tableName' AND authorized_users LIKE '%,{$user['id']},%'",true);

                                if(!in_array($user['application_role_id'],[1]) && count($authorised) == 0)
                                {
                                    die(json_encode(array("type" => "error" , "message" => "Please refresh the data lists section , There are some Unauthorized lists there !")));
                                }

                            }
                        }
                    }
                    else
                    {
                        die(json_encode(array("type" => "error" , "message" => "Please select the data lists you want to send to!")));
                    }

                    # add lists 
                    $data['lists'] = implode(',',Arrays::getElement($data,'lists'));
                }
                else
                {
                    $data['lists'] = null;
                }
     
                # write the form into a file
                $fileDirectory = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'drops';
                $fileName = 'drop_' . Strings::generateRandomText(20,true,true,true,false) . '.drp';

                # convert the form data into json to store it into a file so that the mailing script will read it 
                $jsonVersionOfDrop = json_encode($data,JSON_UNESCAPED_UNICODE);

                if($jsonVersionOfDrop != '' && file_put_contents($fileDirectory . DS . $fileName, $jsonVersionOfDrop)) 
                {          
                    # execute the job that takes care of sent proccess 
                    chdir(APPS_FOLDER. DS . Application::getPrefix() . DS  . 'jobs/');
                    exec("nohup /opt/jdk1.8.0_131/bin/java -Dfile.encoding=UTF8 -jar bluemail.jar send_proccess " . $fileDirectory . DS . $fileName . '>>' . ROOT_PATH . DS . 'tmp/logs/drops.log' . ' 2>>' . ROOT_PATH . DS . 'tmp/logs/drops.log &');
                }
                else
                {
                    die(json_encode(array("type" => "error" , "message" => "Error occured while trying to create the drop file !")));
                } 

                # show output message
                if(array_key_exists('drop',$data))
                {
                    die(json_encode(array("type" => "success" , "message" => "Drop has been procceed Successfully !")));
                }
                else
                {
                    die(json_encode(array("type" => "success" , "message" => "Test has been procceed Successfully !")));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name headers
         * @description the headers action
         * @before init
         * @after setMenu,closeConnection
         */
        public function headers() 
        { 
            $arguments = func_get_args(); 
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'lists';
            
            # get the connected user
            $user = Session::get('bluemail_connected_user'); 
                
            if(isset($page))
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_production_mail_headers',true);
                
                switch ($page) 
                {
                    case 'lists' :
                    {
                        # set the template for the page view 
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'mail' . DS . 'headers' . DS . 'lists' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active 
                        $this->getMasterView()->set('menu_production_mail_headers_list',true);
                        
                        # get the data from the database
                        $list = Database::getCurrentDatabaseConnector()->query()->from('admin.headers',array('id','name','value'))->where('user_id = ?',Arrays::getElement($user,'id'))->all();
                                    
                        # get all the columns names 
                        $columns = array('id','name','value');

                        # set the list into the template data system 
                        $this->getPageView()->set('list',$list);

                        # set the columns list into the template data system 
                        $this->getPageView()->set('columns',$columns);

                        # check for message 
                        PagesHelper::checkForMessageToPage($this);
                        break;
                    }
                    case 'add' :
                    {
                         # set the template for the page view 
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'mail' . DS . 'headers' . DS . 'add' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active 
                        $this->getMasterView()->set('menu_production_mail_headers_add',true); 

                        break;
                    }
                    case 'edit' :
                    {
                       
                        $id = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;
                        
                        if(isset($id) && is_numeric($id))
                        {
                            
                            # set the template for the page view 
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'mail' . DS . 'headers' . DS . 'edit' . '.' . $this->getDefaultExtension());
                            
                            # set the menu item to active 
                            $this->getMasterView()->set('menu_production_mail_headers_add',true);
                        
                            # retrieve the server by id
                            $header = Header::first(true,array("id" => $id));
                            
                            # set the data to the template
                            $this->getPageView()->set('header',$header);
                        }
                        break;
                    }
                    case 'delete' :
                    {
                        $id = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;

                        $message = "Something went wrong !";
                        $messageFlag = 'error';

                        if(isset($id) && is_numeric($id))
                        {
                            # delete the server
                            $header = new Header(array("id" => $id));
                            $header->delete();
                            $message = "Record deleted successfully !";
                            $messageFlag = 'success';
                        }

                        # stores the message in the session 
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);

                        # redirect to show list 
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'types' . RDS . 'lists.html');
                        
                        break;
                    }
                    case 'save' :
                    {
                        # get the connected user
                        $user = Session::get('bluemail_connected_user'); 

                        # retrieves the data from post
                        $id = Request::getParameterFromPOST('header-id');
                        $value = Request::getParameterFromPOST('header');
                        $name = Request::getParameterFromPOST('name');
                        
                        if(isset($name) && isset($value))
                        {
                            $message = "Something went wrong !";
                            $messageFlag = 'error';

                            if($id != NULL && is_numeric($id))
                            {
                                # update case
                                $header = new Header(array("id" => $id));
                                $header->setUser_id(intval(Arrays::getElement($user,'id',1)));
                                $header->setName($name);
                                $header->setValue(base64_encode($value));
                                $header->setType('');

                                $result = $header->save(); 

                                if($result > -1)
                                {
                                    $message = "Record updated succesfully !";
                                    $messageFlag = 'success';
                                }
                            }
                            else
                            {
                                # insert case
                                $header = new Header(array("id" => $id));
                                $header->setUser_id(intval(Arrays::getElement($user,'id',1)));
                                $header->setName($name);
                                $header->setValue(base64_encode($value));
                                $header->setType('');

                                $result = $header->save(); 

                                if($result > -1)
                                {
                                    $message = "Record stored succesfully !";
                                    $messageFlag = 'success';
                                }
                           }

                           # stores the message in the session 
                           Session::set('proccess_message_flag',$messageFlag);
                           Session::set('proccess_message',$message);
                        }

                        # redirect to show list 
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail' . RDS . 'headers' . RDS . 'lists.html');
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail' . RDS . 'headers' . RDS . 'lists.html');
                    }    
                }
            }
        }
        
        /**
         * @name getServers
         * @description gets the servers by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getServers() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                if($this->getDefaultContentType() == 'text/json')
                {
                    # get the connected user
                    $user = Session::get('bluemail_connected_user');
            
                    # get all servers from the database
                    $serverProviders = ServerProvider::all(true,array('status_id = ? ',1),array('id','name'));
                    
                    if(!in_array($user['application_role_id'],[1]))
                    {
                        $servers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,main_ip,provider_id FROM admin.servers WHERE server_type_id > 1 AND authorized_users LIKE '%,{$user['id']},%'",true);
                    }
                    else
                    {
                        $servers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id','name','main_ip','provider_id'));
                    }

                    # check for providers that has no servers in production 
                    foreach ($serverProviders as $index => $provider) 
                    {
                        $count = 0;

                        foreach ($servers as $server) 
                        {
                            if($server['provider_id'] == $provider['id'])
                            {
                                $count++;
                            }
                        }

                        if($count == 0)
                        {
                            unset($serverProviders[$index]);
                        }
                    }
            
                    die(json_encode(array("providers" => $serverProviders , "servers" => $servers)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getServers
         * @description gets the servers by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getVmtasText() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $vmtas = Request::getParameterFromPOST("vmtas");
                
                if(isset($vmtas) && !empty($vmtas) && $this->getDefaultContentType() == 'text/json')
                {
                    $vmtas = base64_decode($vmtas);
                    $vmtas = explode(",",$vmtas);
                    $values = array();
                    
                    if(is_array($vmtas) && count($vmtas))
                    {
                        foreach ($vmtas as $vmta) 
                        {
                            if(filter_var($vmta,FILTER_VALIDATE_IP))
                            {
                                $values[] = "'" . $vmta . "'";
                            }
                        }
                    }
                   
                    $results = Vmta::all(true,array("ip_value IN (?)","no_quote :" . implode(",",$values)));
                    
                    
                    if(!empty($results))
                    {
                        $serverIds = array();
                        
                        foreach ($results as $vmta) 
                        {
                            if(!in_array($vmta['server_id'],$serverIds))
                            {
                                $serverIds[] = intval($vmta['server_id']);
                            }
                        }
                    }

                    die(json_encode(array("vmtas" => $vmtas , "servers" => $serverIds)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getVmtas
         * @description gets the vmtas by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getVmtas() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $serverIds = isset($arguments) && count($arguments) ? implode(",",$arguments) : null;

                if(isset($serverIds) && !empty($serverIds) && $this->getDefaultContentType() == 'text/json')
                {
                    # get the connected user
                    $user = Session::get('bluemail_connected_user');
            
                    # get the server from the database
                    if(!in_array($user['application_role_id'],[1]))
                    {
                        $servers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,main_ip FROM admin.servers WHERE server_type_id > 1 AND id IN ($serverIds) AND authorized_users LIKE '%,{$user['id']},%'",true);
                    }
                    else
                    {
                        $servers = Server::all(true,array("id IN ($serverIds)",""),array('id','name','main_ip'));
                    }
                    
                    if(count($servers) == count(explode(',',$serverIds)))
                    {
                        # get the ips from the database
                        $vmtas = Database::getCurrentDatabaseConnector()->executeQuery("SELECT i.*,"
                                            . " s.name AS server,"
                                            . " s.id AS serverid"
                                            . " FROM admin.vmtas i"
                                            . " LEFT JOIN admin.servers s ON s.id = i.server_id"
                                            . " WHERE i.status_id = 1 AND i.server_id IN ($serverIds)"
                                            . " ORDER BY s.id ASC", true);
                    }

                    die(json_encode(array( "vmtas" => $vmtas , "servers" => $servers)));
                }
            } 
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getOffers
         * @description gets the offers by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getOffers() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $sponsorId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                
                if(isset($sponsorId) && is_numeric($sponsorId) && $this->getDefaultContentType() == 'text/json')
                {
                    $user = Session::get('bluemail_connected_user');
                    
                    # get the offers
                    if(!in_array($user['application_role_id'],[1]))
                    {
                        $offers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name,production_id,flag FROM admin.offers WHERE sponsor_id = $sponsorId AND status_id = 1 AND authorized_users LIKE '%,{$user['id']},%'",true);
                    }
                    else
                    {
                        $offers = Offer::all(true,array('sponsor_id = ? AND status_id = ?',array($sponsorId,1)),array('id','name','production_id','flag'));
                    }

                    # print the result 
                    die(json_encode(array( "offers" => $offers)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getOfferCreatives
         * @description gets the offer creatives by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getOfferCreatives() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $offerId = isset($arguments) && count($arguments) ? $arguments[0] : null;

                if(isset($offerId) && is_numeric($offerId) && $this->getDefaultContentType() == 'text/json')
                {
                    # get the count of data by list
                    $creatives = OfferCreative::all(true,array('offer_id = ? AND status_id = ?',array($offerId,1)),array('*'),'id','ASC');
                    
                    # print the result 
                    die(json_encode(array("creatives" => $creatives)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getOfferAssets
         * @description gets the offer assets by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getOfferAssets() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $offerId = isset($arguments) && count($arguments) ? $arguments[0] : null;

                if(isset($offerId) && is_numeric($offerId) && $this->getDefaultContentType() == 'text/json')
                {
                    $offer = Offer::first(true,array('id=?',$offerId));
                    
                    # get the count of data by list
                    $creatives = OfferCreative::all(true,array('offer_id = ? AND status_id = ?',array($offerId,1)),array('*'),'id','ASC');
                    $offerNames = OfferName::all(true,array('offer_id = ? AND status_id = ?',array($offerId,1)),array('*'),'id','ASC');
                    $offerSubjects = OfferSubject::all(true,array('offer_id = ? AND status_id = ?',array($offerId,1)),array('*'),'id','ASC');
                    
                    # print the result 
                    die(json_encode(array("vertical-id" => $offer['vertical_id'], "from-names" => $offerNames , "subjects" => $offerSubjects , "creatives" => $creatives)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getCreative
         * @description gets the creative by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getCreative() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $creativeId = isset($arguments) && count($arguments) ? $arguments[0] : null;

                if(isset($creativeId) && is_numeric($creativeId) && $this->getDefaultContentType() == 'text/json')
                {
                    # get the count of data by list
                    $creative = OfferCreative::first(true,array('id = ? AND status_id = ?',array($creativeId,1)),array('value'));
                    
                    $links = OfferLink::all(true,array('creative_id = ?',$creativeId));
                    $creativeHTML = $creative['value'];
                    
                    foreach ($links as $link) 
                    {
                        $tag = strtolower($link['type']) == 'preview' ? '[url]' : '[unsub]';      
                        $creativeHTML = str_replace($link['value'],'http://[domain]/' . $tag,$creativeHTML);
                    }
                    
                    $creativeHTML .= PHP_EOL . '<span style="color:#888;font-size:11px;font-family:verdana;display:block;text-align:center;margin-top:10px">click <a href="http://[domain]/[optout]">here</a> to remove yourself from our emails list</span>'; 

                    # print the result 
                    die(json_encode(array( "creative" => $creativeHTML)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name generateLinks
         * @description generate links for auto reply into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */ 
        public function generateLinks() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $offerId = isset($arguments) && count($arguments) ? $arguments[0] : null;

                if(isset($offerId) && is_numeric($offerId) && $this->getDefaultContentType() == 'text/json')
                {
                    # get the count of data by list
                    $creative = OfferCreative::first(true,array('offer_id = ? AND status_id = ?',array($offerId,1)),array('id'));
                    $links = OfferLink::all(true,array('creative_id = ?',$creative['id']));
                    
                    $table = "<table class='table table-bordered table-striped table-condensed'>";
                    $table .= "<thead><tr>";
                    $table .= "<td>Type</td><td>Link</td>";
                    $table .= "</tr></thead>";
                    $table .= "<tbody>";

                    foreach ($links as $link) 
                    {
                        $type = strtoupper($link['type']) == 'PREVIEW' ? 'Offer Click :' : 'Offer Unsub :';
                        $act = strtoupper($link['type']) == 'PREVIEW' ? 'c' : 'u';
                        $link = "r.php?t=$act&d=0&l=0&c=0&cr=" . $creative['id'];        
                        $table .= "<tr>";
                        $table .= "<td>" . $type . "</td>";
                        $table .= "<td>" . 'http://[domain]' . RDS . $link . "</td>";
                        $table .= "</tr>";
                    }

                    $link = "opt.php?d=0&l=0&c=0&cr=" . $creative['id'] . "&em=";
                    $table .= "<tr>";
                    $table .= "<td>Server OptOut :</td>";
                    $table .= "<td>" . 'http://[domain]' . RDS . $link . "</td>";
                    $table .= "</tr>";

                    $table .= "</tbody></table>";
                    
                    # print the result 
                    die(json_encode(array("links" => $table)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getDataLists
         * @description gets the data lists by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDataLists() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $arguments = func_get_args(); 
                $dataTypeId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                $ispId = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;
                $flag = isset($arguments) && count($arguments) > 2 ? $arguments[2] : null;
                $offerId = isset($arguments) && count($arguments) > 3 ? intval($arguments[3]) : 0;

                if(isset($dataTypeId) && is_numeric($dataTypeId) && $this->getDefaultContentType() == 'text/json')
                {
                    $lists = array();
                    
                    $ispResult = Isp::first(true,array('id = ?',$ispId),array('name'));
                    $typeResult = DataType::first(true,array('id = ?',$dataTypeId),array('name'));
                    
                    # switch th database 
                    Database::switchToDatabase('lists');

                    if(count($ispResult) && count($typeResult))
                    {
                        # get the connected user
                        $user = Session::get('bluemail_connected_user'); 
            
                        # specify the schema
                        $schema = strtolower(trim($ispResult['name']));
                        
                        # specify the type
                        $type = strtolower(trim($typeResult['name']));
                        
                        # get tables 
                        $tables = Database::getCurrentDatabaseConnector()->getAvailableTables($schema);

                        if(count($tables))
                        {
                            foreach ($tables as $table) 
                            {
                                if(!empty($table))
                                {                
                                    $tableCondition = $type . '_' . $flag;
                                    
                                    if(strpos($table,$tableCondition) > -1)
                                    {
                                        # switch the default database 
                                        Database::switchToDefaultDatabase();
                    
                                        $authorised = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.data_lists WHERE name = '{$schema}.{$table}' AND authorized_users LIKE '%,{$user['id']},%'",true);
                                        
                                        # switch the lists database 
                                        Database::switchToDatabase('lists');
                    
                                        if(in_array(Arrays::getElement($user,'application_role_id'),array(1)) || count($authorised))
                                        {
                                            $count = 0;
                                            $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT COUNT(id) AS count FROM {$schema}.{$table}",true);

                                            if(count($result) && key_exists('count',$result[0]))
                                            {
                                                $count = intval($result[0]['count']);
                                                $remain = intval($result[0]['count']);
                                            }

                                            if($count > 0)
                                            {
                                                if($offerId > 0)
                                                {
                                                    $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT COUNT(id) AS count FROM {$schema}.{$table} WHERE COALESCE(offers_excluded,'') SIMILAR TO '%($offerId,|,$offerId,|,$offerId)%' OR offers_excluded = '$offerId' ",true);

                                                    if(count($result) && key_exists('count',$result[0]))
                                                    {
                                                        $remain = $count - intval($result[0]['count']);
                                                    }
                                                }

                                                $lists[] = array('id' => $schema . '.' . $table , 'name' => $table , 'count' => $count,'remain' => $remain);
                                            } 
                                        }
                                    }
                                }
                            }
                        }
                    }
                 
                    # print the result 
                    die(json_encode(array( "lists" => $lists)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name getDataListChunks
         * @description gets the Data List chunks into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDataListChunks() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $data = Request::getAllDataFromPOST();
                
                if(count($data) && key_exists('list',$data) && $data['list'] != '' && $this->getDefaultContentType() == 'text/json')
                {
                    $subLists = array(); 
                    $dataList = DataList::first(true,['name = ?',$data['list']]);
                    $id = 0;
                    
                    if(count($dataList))
                    {
                        $id = intval($dataList['id']);
                    }
                    
                    Database::switchToDatabase('lists');
                    
                    $maxId = intval(Arrays::getElement(Arrays::getElement(Database::getCurrentDatabaseConnector()->executeQuery('SELECT id as max FROM ' . $data['list'] . ' ORDER BY id DESC LIMIT 1',true),0),'max'));
                    $maxPerList = 100000000000;
                    $loops = intval(ceil($maxId / $maxPerList));
                    $min = 1;
                    $max = $maxPerList;
                    $name = Arrays::getElement(explode(".",$data['list']),1);
                    $offerId = Arrays::getElement(explode(".",$data['offer-id']),0);
                    
                    $condition = $offerId > 0 ? "AND ( COALESCE(offers_excluded,'') NOT SIMILAR TO '%($offerId,|,$offerId,|,$offerId)%' AND offers_excluded != '$offerId' OR offers_excluded IS NULL OR offers_excluded = '' )" : '';
                    
                    for ($index = 1; $index <= $loops; $index++) 
                    {                       
                        $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT COUNT(id) as count FROM {$data['list']} WHERE id BETWEEN $min AND $max $condition", true);
                        $value = "$id|{$data['list']}";
                        $subLists[] = array('value' => $value , 'name' => $name , 'count' => intval($result[0]['count']) , 'index' => $index);
                        $min = $max +1;
                        $max = $max + $maxPerList;
                    }

                    # print the result 
                    die(json_encode(array( "sub-lists" => $subLists)));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name addVmtas
         * @description add Vmtas
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function addVmtas() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $data = Request::getAllDataFromPOST();
                
                if(count($data) && $this->getDefaultContentType() == 'text/json')
                {
                    # get the connected user
                    $user = Session::get('bluemail_connected_user'); 
            
                    $serverId = intval(Arrays::getElement($data,'server-id',0));
                    $vmtaname = strtolower(str_replace('-','_', trim(trim(Arrays::getElement($data,'vmta-name','vmta_custom'),'_'),'-')));
                    
                    if($serverId == 0)
                    {
                        die(json_encode(array('type' => 'error','message' => 'Please Select a Server !')));
                    }
                    
                    $server = Server::first(true,array('server_type_id = ? AND status_id = ? AND id = ?',array(2,1,$serverId)),array('id','name','main_ip','username','password','ssh_port'));
                    
                    if(count($server) == 0)
                    {
                        die(json_encode(array('type' => 'error','message' => 'Please Select a Server !')));
                    }
                    
                    $vmtas = explode(PHP_EOL,Arrays::getElement($data,'vmtas',''));
                    $vmtasType = Arrays::getElement($data,'vmtas-type',''); 

                    if(count($vmtas) == 0)
                    {
                        die(json_encode(array('type' => 'error','message' => 'No Vmtas filled !')));
                    }
                    
                    $mapping = [];
                    
                    foreach ($vmtas as $row) 
                    {
                        if(strpos($row,';') > 0)
                        {
                            $parts = explode(';',$row);
                            
                            if(count($parts) == 2)
                            {
                                $ip = Ip::first(true,['value = ?',preg_replace( "/\r|\n/","",$parts[0])]);
                                
                                if(count($ip))
                                {
                                    $mapping[] = ['ip' => $ip, 'domain' => preg_replace( "/\r|\n/","",$parts[1]),'username'=>'','password'=>'','smtphost'=>''];
                                }
                            }
                            elseif (count($parts) == 5)
                            {
                                $ip = Ip::first(true,['value = ?',preg_replace( "/\r|\n/","",$parts[0])]);
                                
                                if(count($ip))
                                {
                                    $mapping[] = ['ip' => $ip, 'domain' => preg_replace( "/\r|\n/","",$parts[1]),'username'=>$parts[2],'password'=>$parts[3],'smtphost'=>$parts[4]];
                                }
    
                            }
                        }
                    }
                    
                    if(count($mapping) == 0)
                    {
                        die(json_encode(array('type' => 'error','message' => 'No ips selected !!')));
                    }
                   
                    $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                    $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                    if($sshConnector->isConnected())
                    {
                        $assetsDirectory = Paths::getCurrentApplicationRealPath() . DS . DEFAULT_ASSETS_DIRECTORY;
                        
                        if(strcmp($vmtasType, 'Smtp') == 0)
                        {
                            $vmtaTemplate = $assetsDirectory . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'installation' . DS . 'vmtasmtp.tpl';
                        }
                        else
                        {
                            $vmtaTemplate = $assetsDirectory . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'installation' . DS . 'vmta.tpl';
                        }
                        
                        $res = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id,name FROM admin.vmtas WHERE server_id = $serverId  AND name LIKE '%{$vmtaname}_%' ORDER BY id DESC",true);
                        $index = 1;
                        
                        if(count($res))
                        {
                            $num = intval(preg_replace( "/\r|\n/","",str_replace($vmtaname . '_','',$res[0]['name'])));
                            $index = $num+1;
                        }
                        
                        foreach ($mapping as $map) 
                        {
                            # create a vmta 
                            $vmtaObject = new Vmta();
                            $vmtaObject->setStatus_id(1);
                            $vmtaObject->setServer_id($serverId);
                            $vmtaObject->setIp_id($map['ip']['id']);
                            $vmtaObject->setName($vmtaname . '_' . $index);
                            $vmtaObject->setType('Custom');
                            $vmtaObject->setIp_value($map['ip']['value']);
                            $vmtaObject->setDomain($map['domain']);
                            $vmtaObject->setUsername($map['username']);
                            $vmtaObject->setPassword($map['password']);
                            $vmtaObject->setSmtphost($map['smtphost']);
                            $vmtaObject->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                            $vmtaObject->setCreated_at(date("Y-m-d"));
                            $vmtaObject->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                            $vmtaObject->setLast_updated_at(date("Y-m-d"));
                            $vmtaObject->save();
                            
                            if(strcmp($vmtasType, 'Smtp') == 0)
                            {
                                $vmta = str_replace(array('$P{DKIM}','$P{VMTA}','$P{IP}','$P{DOMAIN}','$P{USERNAME}','$P{PASSWORD}','$P{SMTPHOST}'),array('',$vmtaname . '_' . $index,$map['ip']['value'],$map['domain'],$map['username'],$map['password'],$map['smtphost']),file_get_contents($vmtaTemplate)) . PHP_EOL; 
                            }
                            else
                            {
                                $vmta = str_replace(array('$P{DKIM}','$P{VMTA}','$P{IP}','$P{DOMAIN}'),array('',$vmtaname . '_' . $index,$map['ip']['value'],$map['domain']),file_get_contents($vmtaTemplate)) . PHP_EOL; 
                            }
                            
                            $sshConnector->scp('send',array('/etc/pmta/vmtas/' . $vmtaname . '_' . $index . '.conf'),$vmta);
                            $index++;
                        }

                        $sshConnector->cmd("chown -R pmta:pmta /etc/pmta/;",true);
                        $sshConnector->cmd("service pmta reload",true);
                        $sshConnector->disconnect();
                    }
                    else
                    {
                        die(json_encode(array('type' => 'error','message' => 'Could not connect to the server selected !')));
                    }
                    
                    # print the result 
                    die(json_encode(array("type" => "success" , "message" => "VMTAs saved successfully !")));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name resetVmtas
         * @description reset Vmtas
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function resetVmtas() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                $data = Request::getAllDataFromPOST();
                
                if(count($data) && $this->getDefaultContentType() == 'text/json')
                {
                    $serverId = intval(Arrays::getElement($data,'server-id',0));
                    
                    if($serverId == 0)
                    {
                        die(json_encode(array('type' => 'error','message' => 'Please Select a Server !')));
                    }
                    
                    $server = Server::first(true,array('server_type_id = ? AND status_id = ? AND id = ?',array(2,1,$serverId)),array('id','name','main_ip','username','password','ssh_port'));
                    
                    if(count($server) == 0)
                    {
                        die(json_encode(array('type' => 'error','message' => 'Please Select a Server !')));
                    }
                    
                    $vmtas = Vmta::all(true,['type = ? AND server_id = ?',['Custom',$serverId]]);
                    
                    if(count($vmtas))
                    { 
                        $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                        $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                        if($sshConnector->isConnected())
                        {
                            foreach ($vmtas as $vmta) 
                            {
                                # remove the vmta file 
                                $sshConnector->cmd("rm -rf /etc/pmta/vmtas/" . $vmta['name'] . '.conf',true);
                                Vmta::deleteAll(['id = ?',$vmta['id']]);
                            }
                            
                            $sshConnector->cmd("chown -R pmta:pmta /etc/pmta/;",true);
                            $sshConnector->cmd("service pmta reload",true);
                            $sshConnector->disconnect();
                        }
                    }

                    # print the result 
                    die(json_encode(array("type" => "success" , "message" => "VMTAs reseted successfully !")));
                }
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name setMenu
         * @description set the current menu to the template
         * @protected
         */
        public function setMenu() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_mail',true);
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
