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
    use ma\mfw\os\System as System;
    use ma\mfw\http\Request as Request;
    use ma\mfw\http\Response as Response;
    use ma\mfw\http\Session as Session;
    use ma\applications\bluemail\models\admin\Server as Server;
    use ma\mfw\types\Strings as Strings;
    use ma\mfw\api\Api as Api;
    use ma\mfw\ssh2\SSH as SSH;
    use ma\mfw\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
    use ma\mfw\application\Application as Application;
    use ma\mfw\www\URL as URL; 
    use ma\mfw\types\Arrays as Arrays;
    use ma\applications\bluemail\models\admin\awsProccesses as awsProccesses;
    use ma\applications\bluemail\models\admin\Status as Status;
    use ma\applications\bluemail\models\admin\ServerApis as ServerApis;
    use ma\mfw\globals\Server as GloblServers;
    use ma\applications\bluemail\helpers\PagesHelper as PagesHelper;
    use ma\mfw\exceptions\types\PageException as PageException;
    /**
     * @name            Isps.controller 
     * @description     The Isps controller
     * @package		ma\applications\bluemail\controllers
     * @category        Controller
     * @author		Miami Team			
     */
    class Accountapi extends Controller 
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
            if(!in_array(Arrays::getElement($user,'application_role_id'),array(1)))
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
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'isps' . RDS . 'lists.html');
        }
        
        /**
         * @name lists
         * @description the lists action
         * @before init
         * @after setMenu,closeConnection
         */
        public function lists() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_isps',true);
            $this->getMasterView()->set('menu_admin_isps_lists',true);

            # get the data from the database
            $list = ServerApis::all(true,array(),array('id','account_name','api_provider'),'id','ASC');
                                    
            # get all the columns names 
            $columns = array('id','account_name','api_provider');

            # set the list into the template data system 
            $this->getPageView()->set('list',$list);
            
            # set the columns list into the template data system 
            $this->getPageView()->set('columns',$columns);

            # check for message 
            PagesHelper::checkForMessageToPage($this);
        } 
        
        /**
         * @name add
         * @description the add action
         * @before init
         * @after setMenu,closeConnection
         */
        public function add() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_api',true);
            $this->getMasterView()->set('menu_admin_api_add',true);
            
            # get status list 
            $status = Status::all(true,array(),array('id','name'),'id','ASC');

            # set the list into the template data system 
            $this->getPageView()->set('status',$status);
        }
        
        
        /**
         * @name amazonaws
         * @description manage amazon aws instances
         * @before init
         * @after setMenu,closeConnection
         */
        public function amazonaws() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                   // \ma\mfw\output\PrintWriter::printValue($data);
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    
                    $accountId = $data['accountId'];
                    $region = $data['region'];
                    $system = $data['os'];
                    $account = ServerApis::first(true,array('id = ?',$accountId));
                    
                    $data['accessKey'] = $account['access_key'];
                    $data['secretKey'] = $account['secret_key'];
                    
                    # Manage the Operation System 
                    $centos = array("us-east-1" => "ami-3830342f" ,
                                    "us-east-2" => "ami-03237966" ,
                                    "us-west-1" => "ami-09f6d94c" ,
                                    "us-west-2" => "ami-39e5ae09" ,
                                    "eu-central-1" => "ami-86d4e59b" ,
                                    "ca-central-1" => "ami-bbb408df" ,
                                    "eu-west-1" => "ami-442d9933" ,
                                    "eu-west-2" => "ami-dcbaafb8" ,
                                    "eu-west-3" => "ami-011aad7c" ,
                                    "sa-east-1" => "ami-3d14bd20" ,
                        );
                    $amazon = array("us-east-1" => "ami-0b69ea66ff7391e80" ,
                                    "us-east-2" => "ami-00c03f7f7f2ec15c3" ,
                                    "us-west-1" => "ami-0245d318c6788de52" ,
                                    "us-west-2" => "ami-04b762b4289fba92b" ,
                                    "eu-central-1" => "ami-00aa4671cbf840d82" ,
                                    "ca-central-1" => "ami-085edf38cedbea498" ,
                                    "eu-west-1" => "ami-0ce71448843cb18a1" , 
                                    "eu-west-2" => "ami-00a1270ce1e007c27" ,
                                    "eu-west-3" => "ami-03b4b78aae82b30f1" ,
                                    "sa-east-1" => "ami-0a1f49a762473adbd" ,
                        );

                    if($system == "centos" && $centos[$region] == "")
                    {
                        die(json_encode(array("started" => false , "message" => "The operation system not found on Centos ! please choose the custom ID")));
                    }
                    else if($system == "amazon" && $amazon[$region] == "")
                    {
                        die(json_encode(array("started" => false , "message" => "The operation system not found on AMAZON ! please choose the custom ID")));
                    }
                    else
                    {
                        $data['os'] = ($system == "centos") ? $centos[$region] : $amazon[$region];
                    }
                    
                    # log file path 
                    $logFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'aws_' . $accountId . '.log';
                    # process  path 
                    $processFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'aws_' . $accountId . '.process';
                    
                     # empty the log file 
                    System::executeCommand("> " . $logFile);
                    System::executeCommand("echo 'Started' > " . $processFile);
                    
                    $data['logpath'] = $logFile;
                    $data['processpath'] = $processFile;
                    
                    # write the form into a file
                    $fileDirectory = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'aws';
                    $fileName = 'amazon_' . Strings::generateRandomText(20,true,true,true,false) . '.aws';
                        
                     # convert the form data into json to store it into a file so that the mailing script will read it 
                    $jsonVersionOfDrop = json_encode($data,JSON_UNESCAPED_UNICODE);

                    if($jsonVersionOfDrop != '' && file_put_contents($fileDirectory . DS . $fileName, $jsonVersionOfDrop)) 
                    {  
                        # execute the job that takes care of sent proccess
                        chdir(APPS_FOLDER. DS . Application::getPrefix() . DS  . 'jobs' . DS . 'aws-java-sample');
                        $pid = exec("nohup java -Dfile.encoding=UTF8 -jar aws-manager.jar create_proccess " . $fileDirectory . DS . $fileName . ' > ' . $logFile . ' 2> ' . $logFile . ' &');
                        die(json_encode(array("started" => true)));
                        
                    }
                    else
                    {
                        die(json_encode(array("type" => "error" , "message" => "Error occured while trying to create the drop file !")));
                    }    
                }
                
                // Gettign the new values from DB and display them ...
                $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT * FROM admin.aws WHERE accountid = '$accountId' AND region = '$region';", true);
                $resalutTable = array();

                foreach ($list as $row)
                {
                    $resalutTable[] = array('id'=>$row['id'] , 'label'=>$row['awsid'] , 'ipv4'=> $row['ip'] , 'status' => $row['status_id']);
                }

                die(json_encode(array("resaults" => $resalutTable)));
            }
            else
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_api',true);
                $this->getMasterView()->set('menu_admin_api_add',true);

                // Manage APIs 
                $accounts = ServerApis::all(true,array(),array('id','account_name'),'id','ASC');

                # set the list into the template data system 
                $this->getPageView()->set('accounts',$accounts);
            }
            
        }
        
        /**
         * @name proccess
         * @description the proccess action
         * @before init
         * @after setMenu,closeConnection
         */
        public function proccess() 
        {
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                $this->setShowMasterView(false);
                $this->setShowPageView(false);

                # retreive
                $data = Request::getAllDataFromPOST();
                
                $log = '';
                $status = 0;
                
                $logFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'aws_' . $data['accountId'] . '.log';
                $statusFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'aws_' . $data['accountId'] . '.process';

                if(file_exists($logFile))
                {
                    $content = file_get_contents($logFile);
                    $log = str_replace(PHP_EOL,'<br/>',$content);
                }
                
                if(file_exists($statusFile))
                {
                    $content = file_get_contents($statusFile);
                    $status = trim(trim($content,PHP_EOL));
                }
                
                die(json_encode(array("status" => $status , "log" => $log))); 
            }
        }
        
        /**
         * @name getawsinstances
         * @description manage amazon aws instances
         * @before init
         * @after setMenu,closeConnection
         */
        public function getawsinstances() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    
                    $accountId = $data['accountId'];
                    $region = $data['region'];

                    $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT * FROM admin.aws WHERE accountid = '$accountId' AND region = '$region';", true);
                    $resalutTable = array();
                    
                    foreach ($list as $row)
                    {
                        $resalutTable[] = array('id'=>$row['id'] , 'label'=>$row['awsid'] , 'ipv4'=> $row['ip'] , 'status' => $row['status_id']);
                    }
                    
                    die(json_encode(array("resaults" => $resalutTable)));
                      
                }
            }
            else
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_api',true);
                $this->getMasterView()->set('menu_admin_api_add',true);

                // Manage APIs 
                $accounts = ServerApis::all(true,array(),array('id','account_name'),'id','ASC');

                # set the list into the template data system 
                $this->getPageView()->set('accounts',$accounts);
            }
        }
        
        /**
         * @name cloud
         * @description manage start stop actions
         * @before init
         * @after setMenu,closeConnection
         */
        public function cloud() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    
                    $accountId = $data['accountId'];
                    $region = $data['region'];
                    
                    if($data['keepAlive'] == "" || $data['keepDown'] == "")
                    {
                        die(json_encode(array("started" => false , "message" => "Please insert start and stop delai ")));
                    }

                    # insert proccess
                    $proccess = new awsProccesses();
                    $proccess->setAws_ids($data['aws']);
                    $proccess->setStart($data['keepAlive']);
                    $proccess->setStop($data['keepDown']);
                    $proccess->setStatus("STOPING");

                    $data['cloud'] = $proccess->save();
                    
                    $aws = json_decode($data['aws']);
                    $data['accountIds'] = implode(";",$aws);
                    
                    # log file path 
                    $logFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'aws_' . $accountId . '.log';
                    # process  path 
                    $processFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'aws_' . $accountId . '.process';
                    
                     # empty the log file 
                    System::executeCommand("> " . $logFile);
                    System::executeCommand("echo 'Started' > " . $processFile);
                    
                    $data['logpath'] = $logFile;
                    $data['processpath'] = $processFile;
                                        
                    # write the form into a file
                    $fileDirectory = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'aws';
                    $fileName = 'amazon_' . Strings::generateRandomText(20,true,true,true,false) . '.aws';
                    
                     # convert the form data into json to store it into a file so that the mailing script will read it 
                    $jsonVersionOfDrop = json_encode($data,JSON_UNESCAPED_UNICODE);

                    if($jsonVersionOfDrop != '' && file_put_contents($fileDirectory . DS . $fileName, $jsonVersionOfDrop)) 
                    {  
                        # execute the job that takes care of sent proccess
                        chdir(APPS_FOLDER. DS . Application::getPrefix() . DS  . 'jobs' . DS . 'aws-java-sample');
                        $pid = exec("nohup java -Dfile.encoding=UTF8 -jar aws-manager.jar cloud " . $fileDirectory . DS . $fileName . ' > ' . $logFile . ' 2> ' . $logFile . ' &');
                        die(json_encode(array("started" => true)));
                        
                    }
                    else
                    {
                        die(json_encode(array("type" => "error" , "message" => "Error occured while trying to create the drop file !")));
                    } 
                    
                    //$id = Database::getCurrentDatabaseConnector()->executeQuery("INSERT INTO admin.aws_proccesses (aws_ids,start,stop,status) VALUES ('qqq' , 5 , 5, 'starting')" , false ,null, true , 1);
                    \ma\mfw\output\PrintWriter::printValue($id);
                }
            }
        }
        
        /**
         * @name removeAWS
         * @description Remove AWS Intance
         * @before init
         * @after setMenu,closeConnection
         */
        public function removeAWS() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $instances = json_decode($data['instance']);
                    $aws = json_decode($data['aws']);
                    $ips = $data['ips'];
                    $accountId = $data['accountId'];
                    $accountId = $data['accountId'];
                    $account = ServerApis::first(true,array('id = ?',$accountId));
                    
                    $data['accessKey'] = $account['access_key'];
                    $data['secretKey'] = $account['secret_key'];
                    $data['accountIds'] = implode(";",$aws);
                    $data['region'] = $data['region'];
                    
                    # log file path 
                    $logFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'aws_' . $accountId . '.log';
                    # process  path 
                    $processFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'aws_' . $accountId . '.process';
                    
                     # empty the log file 
                    System::executeCommand("> " . $logFile);
                    System::executeCommand("echo 'Started' > " . $processFile);
                    
                    $data['logpath'] = $logFile;
                    $data['processpath'] = $processFile;

                    # write the form into a file
                    $fileDirectory = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'aws';
                    $fileName = 'amazon_' . Strings::generateRandomText(20,true,true,true,false) . '.aws';
                    
                     # convert the form data into json to store it into a file so that the mailing script will read it 
                    $jsonVersionOfDrop = json_encode($data,JSON_UNESCAPED_UNICODE);
                    
                    if($jsonVersionOfDrop != '' && file_put_contents($fileDirectory . DS . $fileName, $jsonVersionOfDrop)) 
                    {          
                        $awsString = implode(";",$aws);
                         chdir(APPS_FOLDER. DS . Application::getPrefix() . DS  . 'jobs' . DS . 'aws-java-sample');
                         $pid = exec("nohup java -Dfile.encoding=UTF8 -jar aws-manager.jar remove_proccess " . $fileDirectory . DS . $fileName . ' > ' . $logFile . ' 2> ' . $logFile . ' &');
                    }
                    else
                    {
                        die(json_encode(array("type" => "error" , "message" => "Error occured while trying to create the drop file !")));
                    } 
                    
                    foreach ($ips as $ip) 
                    {
                       $serverAccount = Server::first(true,array('main_ip = ?',trim($ip)),array('id','name','main_ip','username','password','ssh_port','server_type'));
                       
                       if($serverAccount != null && count($serverAccount))
                       {
                           $serverID = $serverAccount['id'];
                            $server = new Server(array("id" => $serverID));
                            $res = $server->delete();

                             # update domains 
                             Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.domains SET ip_id = 0 , domain_status = 'Available' WHERE ip_id IN (SELECT id FROM admin.ips WHERE server_id = $serverID)");
                             Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.ips WHERE server_id = $serverID");
                             Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.vmtas WHERE server_id = $serverID");
                             
                       }
                       
                       Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.aws WHERE ip = '$ip';");
                        
                    }
                    
                    die(json_encode(array("started" => true)));
                }
                
            }
        }
        
        
        
        /**
         * @name UpdateServerAWS
         * @description update servers ips
         * @before init
         * @after setMenu,closeConnection
         */
        public function UpdateServerAWS() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $instances = json_decode($data['instance']);
                    $aws = json_decode($data['aws']);
                    $ips = $data['ips'];
                    $accountId = $data['accountId'];
                    $accountId = $data['accountId'];
                    $account = ServerApis::first(true,array('id = ?',$accountId));
                    //\ma\mfw\output\PrintWriter::printValue($data);
                    
                    $data['accessKey'] = $account['access_key'];
                    $data['secretKey'] = $account['secret_key'];
                    $data['accountIds'] = implode(";",$aws);
                    $data['region'] = $data['region'];
                    $updated = 0;
                    
                    foreach ($aws as $ip) 
                    {
                       $serverAWS = Database::getCurrentDatabaseConnector()->executeQuery("SELECT * FROM admin.aws WHERE awsid ='".$ip."';" , true);
                       $mainIP = trim($serverAWS[0]['ip']);
                       $id = trim($serverAWS[0]['serverid']);
                       $server = new Server(array("id" => $id));
                       $server->setMain_ip($mainIP);
                       $result = $server->save(); 

                       
                    }
                    if($updated > 0)
                    {
                        die(json_encode(array("resaults" => 1)));
                    }
                    else
                    {
                        die(json_encode(array("resaults" =>  0)));
                    }
                    
                        
                    die(json_encode(array("resaults" => 'Done')));
                }
                
            }
        }
        
        /**
         * @name stopAWS
         * @description stop AWS Intance
         * @before init
         * @after setMenu,closeConnection
         */
        public function stopAWS() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $instances = json_decode($data['instance']);
                    $aws = json_decode($data['aws']);
                    $ips = $data['ips'];
                    $accountId = $data['accountId'];
                    $accountId = $data['accountId'];
                    $account = ServerApis::first(true,array('id = ?',$accountId));
                    
                    $data['accessKey'] = $account['access_key'];
                    $data['secretKey'] = $account['secret_key'];
                    $data['accountIds'] = implode(";",$aws);
                    $data['region'] = $data['region'];
                    
                    # log file path 
                    $logFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'aws_' . $accountId . '.log';
                    # process  path 
                    $processFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'aws_' . $accountId . '.process';
                    
                     # empty the log file 
                    System::executeCommand("> " . $logFile);
                    System::executeCommand("echo 'Started' > " . $processFile);
                    
                    $data['logpath'] = $logFile;
                    $data['processpath'] = $processFile;

                    # write the form into a file
                    $fileDirectory = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'aws';
                    $fileName = 'amazon_' . Strings::generateRandomText(20,true,true,true,false) . '.aws';
                    
                     # convert the form data into json to store it into a file so that the mailing script will read it 
                    $jsonVersionOfDrop = json_encode($data,JSON_UNESCAPED_UNICODE);
                    
                    if($jsonVersionOfDrop != '' && file_put_contents($fileDirectory . DS . $fileName, $jsonVersionOfDrop)) 
                    {          
                        $awsString = implode(";",$aws);
                         chdir(APPS_FOLDER. DS . Application::getPrefix() . DS  . 'jobs' . DS . 'aws-java-sample');
                         $pid = exec("nohup java -Dfile.encoding=UTF8 -jar aws-manager.jar stop_proccess " . $fileDirectory . DS . $fileName . ' > ' . $logFile . ' 2> ' . $logFile . ' &');

                    }
                    else
                    {
                        die(json_encode(array("type" => "error" , "message" => "Error occured while trying to create the drop file !")));
                    } 
                    
                    die(json_encode(array("started" => true)));
                }
                
            }
        }
        
        /**
         * @name stopAWS
         * @description stop AWS Intance
         * @before init
         * @after setMenu,closeConnection
         */
        public function startAWS() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $instances = json_decode($data['instance']);
                    $aws = json_decode($data['aws']);
                    $ips = $data['ips'];
                    $accountId = $data['accountId'];
                    $accountId = $data['accountId'];
                    $account = ServerApis::first(true,array('id = ?',$accountId));
                    
                    $data['accessKey'] = $account['access_key'];
                    $data['secretKey'] = $account['secret_key'];
                    $data['accountIds'] = implode(";",$aws);
                    $data['region'] = $data['region'];
                    
                     # log file path 
                    $logFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'aws_' . $accountId . '.log';
                    # process  path 
                    $processFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'aws_' . $accountId . '.process';
                    
                     # empty the log file 
                    System::executeCommand("> " . $logFile);
                    System::executeCommand("echo 'Started' > " . $processFile);
                    
                    $data['logpath'] = $logFile;
                    $data['processpath'] = $processFile;

                    # write the form into a file
                    $fileDirectory = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'aws';
                    $fileName = 'amazon_' . Strings::generateRandomText(20,true,true,true,false) . '.aws';
                    
                     # convert the form data into json to store it into a file so that the mailing script will read it 
                    $jsonVersionOfDrop = json_encode($data,JSON_UNESCAPED_UNICODE);
                    
                    if($jsonVersionOfDrop != '' && file_put_contents($fileDirectory . DS . $fileName, $jsonVersionOfDrop)) 
                    {          
                        $awsString = implode(";",$aws);
                         chdir(APPS_FOLDER. DS . Application::getPrefix() . DS  . 'jobs' . DS . 'aws-java-sample');
                         $pid = exec("nohup java -Dfile.encoding=UTF8 -jar aws-manager.jar start_proccess " . $fileDirectory . DS . $fileName . ' > ' . $logFile . ' 2> ' . $logFile . ' &');                         
                    }
                    else
                    {
                        die(json_encode(array("type" => "error" , "message" => "Error occured while trying to create the drop file !")));
                    } 
                    
                    die(json_encode(array("started" => true)));
                }
                
            }
        }
        
        /**
         * @name addServersAws
         * @description Add Aws Insatnces into DB
         * @before init
         * @after setMenu,closeConnection
         */
        public function addServersAws() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    $instancesName = $data['serversNames'];
                    $index = 0;
                    
                    $ipsSelected = json_decode($data['ips']);
                    $keyPath = "/home/.keys/";
                    $shellFileRoot = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'aws' . DS . "addServerRoot.sh";
                    $shellFileEc2 = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'aws' . DS . "addServerEc.sh";
                    
                    foreach ($ipsSelected as $ip)
                    {
                        
                        $instance = Database::getCurrentDatabaseConnector()->executeQuery("SELECT * FROM admin.aws WHERE ip = '$ip';", true);
                        $id = $instance[0]['id'];
                        $accoountId = $instance[0]['accountid'];
                        $instanceIp = $instance[0]['ip'];
                        $password  = $instance[0]['password'];
                        $image = $instance[0]['image_id'];
                        $region = $instance[0]['region'];
                        $dns = $instance[0]['dns'];
                        $insatceKey = $keyPath.$instance[0]['keyname'].".pem";
                        $ipDash = str_replace('.', "-", $instanceIp);
                        
                        $accont = ServerApis::first(true, array('id = ?',$accoountId));
                        $cmd = "";
                        
                        if($dns == "" || $dns == null)
                        {
                            $instageinfo = "root@ec2-".$ipDash.".".$region.".compute.amazonaws.com";
                            
                            if(strlen($image) == 21)
                            {
                                $instageinfo = "ec2-user@ec2-".$ipDash.".".$region.".compute.amazonaws.com";
                                $cmd = "sh " . $shellFileEc2 . " " . $insatceKey . " " . $instageinfo . " " . $password;
                            }
                            else
                            {
                                $cmd = "sh " . $shellFileRoot . " " . $insatceKey . " " . $instageinfo . " " . $password;
                            }
                        }
                        else
                        {
                            $instageinfo = "root@".$dns;
                            if(strlen($image) == 21)
                            {
                                $instageinfo = "ec2-user@".$dns;
                                $cmd = "sh " . $shellFileEc2 . " " . $insatceKey . " " . $instageinfo . " " . $password;
                            }
                            else
                            {
                                $cmd = "sh " . $shellFileRoot . " " . $insatceKey . " " . $instageinfo . " " . $password;
                            }
                            
                        }
                       // \ma\mfw\output\PrintWriter::printValue($cmd);
                        exec($cmd);
                        
                        $rand = Strings::generateRandomText(3,true,false,true,false);
                        $index = $index + 1;
                        $serverName = $instancesName."_".$index;
                        $instancesServerName = ($instancesName != "") ? $serverName : $region."_".$accont['account_name']."_".$rand;
                        
                         # insert case
                        $server = new Server();
                        $server->setStatus_id(1);
                        $server->setProvider_id(1);
                        $server->setServer_type_id(2);
                        $server->setName("AWS_".$instancesServerName);
                        $server->setHost_name("");
                        $server->setMain_ip($ip);
                        $server->setUsername("root");
                        $server->setPassword($password);
                        $server->setServer_auth('0');
                        $server->setSsh_port(22);
                        $server->setExpiration_date(date("Y-m-d"));
                        $server->setCreated_by(intval(Arrays::getElement($data['user'],'id',1)));
                        $server->setCreated_at(date("Y-m-d"));
                        $server->setLast_updated_by(intval(Arrays::getElement($data['user'],'id',1)));
                        $server->setLast_updated_at(date("Y-m-d"));
                        $server->setServer_type(intval(1));

                        $result = $server->save();
                        
                        // Update AWS Status 
                        Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.aws SET status_id = 2,serverId = ".$result." WHERE id = " . $id);

                        if($result > -1)
                        {
                            $message = "Record stored succesfully !";
                            $messageFlag = 'success';
                        }
                        
                    }
                    \ma\mfw\output\PrintWriter::printValue("ddd");
                }
            }
        
        }
        
        /**
         * @name amazon
         * @description manage amazon instances
         * @before init
         * @after setMenu,closeConnection
         */
        public function amazon() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_api',true);
            $this->getMasterView()->set('menu_admin_api_add',true);
            
            $servers = Server::all(true,array('status_id = ?',array(1)),array('id','name','provider_id'),'id','desc');
            # set the data to the template
            $this->getPageView()->set('servers',$servers);
            
        }
        
        /**
         * @name stop ec2
         * @description stop ec2
         * @before init
         * @after setMenu,closeConnection
         */
        public function configureAmazonSTOP() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                
                if(count($data))
                {
                    $mainServer = $data['serverID'][0];
                    $instancesIds = explode(PHP_EOL, $data['ids']);
                    $region = $data['region'];
                    
                    $server = Server::first(true,array('id = ?',$mainServer),array('id','name','main_ip','username','password','ssh_port'));
                    
                    $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                    $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                    if($sshConnector->isConnected())
                    {
                        foreach ($instancesIds as $instances)
                        {
                            \ma\mfw\output\PrintWriter::printValue("Start Stoping the server " . $instances , false);
                            $stopResalut =  $sshConnector->cmd("aws ec2 stop-instances --instance-ids $instances" , true);
                            \ma\mfw\output\PrintWriter::printValue($stopResalut , false);
                        }
                        
                        \ma\mfw\output\PrintWriter::printValue("Done ...");
                    }
                    else
                    {
                        \ma\mfw\output\PrintWriter::printValue("Can not connect to the server : " . $server['name']);
                    }
                    
                }
                
            }
        }
        
        
        /**
         * @name start ec2
         * @description start ec2
         * @before init
         * @after setMenu,closeConnection
         */
        public function configureAmazonSTART() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                
                if(count($data))
                {
                    $mainServer = $data['serverID'][0];
                    $instancesIds = explode(PHP_EOL, $data['ids']);
                    
                    $server = Server::first(true,array('id = ?',$mainServer),array('id','name','main_ip','username','password','ssh_port'));
                    
                    $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                    $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                    if($sshConnector->isConnected())
                    {
                        foreach ($instancesIds as $instances)
                        {
                            \ma\mfw\output\PrintWriter::printValue("Start Stoping the server " . $instances , false);
                            $stopResalut =  $sshConnector->cmd("aws ec2 start-instances --instance-ids $instances" , true);
                            \ma\mfw\output\PrintWriter::printValue($stopResalut , false);
                        }
                        
                        \ma\mfw\output\PrintWriter::printValue("Done ...");
                    }
                    else
                    {
                        \ma\mfw\output\PrintWriter::printValue("Can not connect to the server : " . $server['name']);
                    }
                    
                }
                
            }
        }
        
        /**
         * @name manage
         * @description manage api accounts
         * @before init
         * @after setMenu,closeConnection
         */
        public function manage() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_api',true);
            $this->getMasterView()->set('menu_admin_api_add',true);
            
            // Manage APIs 
            $accounts = ServerApis::all(true,array(),array('id','account_name'),'id','ASC');
            
            // get default API to use 
            
            $api = Api::getAPIClass(array("api_type" => "linode" , "api_url" => "https://api.linode.com/v4"));
            
            // *********** GET REGIONS *********** //
             $regionsJson = json_decode($api->getRegions());
             $regions = array();
             if($regionsJson->results > 0)
             {
                 $resaultRegion = $regionsJson->data;
                 foreach ($resaultRegion as $region)
                 {
                    $regions[] = $region->id;

                 }
             }
             
             // *********** GET IMAGES *********** //
             $imagesJson = json_decode($api->getImages());
             $images = array();
             if($imagesJson->results > 0)
             {
                 $ResaultImages = $imagesJson->data;
                 foreach ($ResaultImages as $image)
                 {
                    $images[] = $image->id;
                 }
             }
             
             // *********** GET TYPES *********** //
             
             $typesJson = json_decode($api->getTypes());
              
             $types = array();
             if($typesJson->results > 0)
             {
                 $ResaultTypes = $typesJson->data;
                 foreach ($ResaultTypes as $type)
                 {
                     $types[] = array('id' => $type->id, 'label' => $type->label);
                 }
             }                         

            
            # set the list into the template data system 
            $this->getPageView()->set('accounts',$accounts);
            $this->getPageView()->set('regions',$regions);
            $this->getPageView()->set('images',$images);
            $this->getPageView()->set('types',$types);
            
        }
        
        /**
         * @name getServers
         * @description get Servers by API
         * @before init
         * @after setMenu,closeConnection
         */
        public function getServers() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    
                        # get the main values
                        $user = $data['user'];
                        $accountId = $data['accountId'];
                        $region = $data['region'];
                        $os = $data['os'];
                        $type = $data['type'];
                        $authType = $data['authType'];
                        $rootPassword = $data['rootPassword'];
                        $count = $data['count'];
                                                
                        $app = Application::getCurrent()->getSetting('init');
    
                    
                        $public_key = trim(file_get_contents($app->public_key));
                        
                        $account = ServerApis::first(true,array('id = ?',$accountId));
                        
                        $linode = array(
                            "api_type" => $account['api_provider'],
                            "api_url" => $account['api_url'],
                            "api_key" => $account['api_key']
                            );

                        $api = Api::getAPIClass($linode);
                                                 
                        $resalutTable = array();
                        
                        for ($index1 = 0; $index1 < $count; $index1++)
                        {
                            $rand = Strings::generateRandomText(3,true,false,true,false);
                            $res = json_decode($api->CreateServer($type, $region, $os, $rootPassword, "bluemail".$rand, $public_key));
                            $resalutTable[] = array('id'=>$res->id , 'label'=>$res->label , 'ipv4'=> $res->ipv4[0] , 'status'=>$res->status);
                        }

                    die(json_encode(array("resaults" => $resalutTable)));
                }
                
            }
        }
        
        /**
         * @name configureRdns
         * @description configure ip rdns
         * @before init
         * @after setMenu,closeConnection
         */
        public function configureRdns() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $accountId = $data['accountId'];
                    $instances = json_decode($data['ips']);
                    $domains = $data['domains'];
                    
                    $account = ServerApis::first(true,array('id = ?',$accountId));
                        
                    $linode = array(
                        "api_type" => $account['api_provider'],
                        "api_url" => $account['api_url'],
                        "api_key" => $account['api_key']
                        );

                    $api = Api::getAPIClass($linode);
                    
                    foreach ($instances as $instance)
                    {
                        $res = json_decode($api->configureRdsn($instance , $domains));
                    }
                    
                die(json_encode(array("resaults" => $res)));
                }
                
            }
        }
        
        
        /**
         * @name listServers
         * @description get all Servers by API
         * @before init
         * @after setMenu,closeConnection
         */
        public function listServers() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    
                        # get the main values
                        $user = $data['user'];
                        $accountId = $data['accountId'];

                        $app = Application::getCurrent()->getSetting('init');
        
                        $account = ServerApis::first(true,array('id = ?',$accountId));
                        
                        $linode = array(
                            "api_type" => $account['api_provider'],
                            "api_url" => $account['api_url'],
                            "api_key" => $account['api_key']
                            );

                        $api = Api::getAPIClass($linode);
                        $$resalutTable = array();
                        $res = json_decode($api->getAllServers());
                        if($res->results > 0)
                        {
                            $data = $res->data;
                            
                            foreach ($data as $server)
                            {
                                 $resalutTable[] = array('id'=>$server->id , 'label'=>$server->label , 'ipv4'=> $server->ipv4[0] , 'status'=>$server->status);
                            }
                        }
                        
                    die(json_encode(array("resaults" => $resalutTable)));
                }
                
            }
        }
        
        /**
         * @name removeInstances
         * @description get all Servers by API
         * @before init
         * @after setMenu,closeConnection
         */
        public function removeInstances() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $instances = json_decode($data['instance']);
                    $ips = json_decode($data['ips']);
                    $accountId = $data['accountId'];
                    
                    $account = ServerApis::first(true,array('id = ?',$accountId));
                        
                    $linode = array(
                        "api_type" => $account['api_provider'],
                        "api_url" => $account['api_url'],
                        "api_key" => $account['api_key']
                        );

                    $api = Api::getAPIClass($linode);
                    
                    foreach ($instances as $instance)
                    {
                        $res = json_decode($api->removeInstance($instance));
                    }
                    
                    // remove them from DB now ...
                    
                    foreach ($ips as $ip)
                    {
                       $serverAccount = Server::first(true,array('main_ip = ?',$ip),array('id','name','main_ip','username','password','ssh_port','server_type'));
                       if($serverAccount != null && count($serverAccount))
                       {
                           $serverID = $serverAccount['id'];
                            $server = new Server(array("id" => $serverID));
                            $res = $server->delete();

                             # update domains 
                             Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.domains SET ip_id = 0 , domain_status = 'Available' WHERE ip_id IN (SELECT id FROM admin.ips WHERE server_id = $serverID)");
                             Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.ips WHERE server_id = $serverID");
                             Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.vmtas WHERE server_id = $serverID");
                       }
                        
                    }
                    
                        
                    die(json_encode(array("resaults" => 'Done')));
                }
                
            }
        }
        
        
        /**
         * @name testIps
         * @description get all Servers by API
         * @before init
         * @after setMenu,closeConnection
         */
        public function testIps() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $ips = json_decode($data['ips']);
                    $emails =  explode(";",$data['emails']);
                    
                    $app = Application::getCurrent()->getSetting('init');
                    $public_key = $app->public_key;
                    $private_key = $app->private_key;
                    
                    
                    foreach ($ips as $ip)
                    {
                        $sshAuthenticator = new \ma\mfw\ssh2\SSHKeyAuthentication('root', $public_key, $private_key);
                        $sshConnector = new SSH($ip,$sshAuthenticator,22);
                        
                        if($sshConnector->isConnected())
                        {
                            $sshConnector->cmd('yum -y update');
                            $sshConnector->cmd('yum install -y mailx');
                            $sshConnector->cmd('yum install -y sendmail');
                            $sshConnector->cmd("echo 'Blue mail tester body' | mail -v -r 'Bluemail IP Tester : ".$ip." <no-reply@email.etihadguest.com>' -s 'Bluemail Tester for IP : ".$ip."' ".$email);
                            //$sshConnector->cmd("sudo mailx -r \"Bluemail IP Tester <no-reply@email.etihadguest.com>\" -s \"IP - ".$ip." \" ".$emails[0]." <<<$'\n Bluemail Body Content for Server ip -> ".$ip." \n'");
                        }
                        else
                        {
                            \ma\mfw\output\PrintWriter::printValue("can not connect to -> " . $ip);
                        }
                        
                        \ma\mfw\output\PrintWriter::printValue("dddd");
                    }
                        
                    die(json_encode(array("resaults" => 'Done')));
                }
                
            }
        }
        
        
        /**
         * @name addServers
         * @description add servers to DB
         * @before init
         * @after setMenu,closeConnection
         */
        public function addServers() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $ips = json_decode($data['ips']);
                    $accountId = $data['accountId'];
                    
                    
                    $account = ServerApis::first(true,array('id = ?',$accountId));
                    
                    $app = Application::getCurrent()->getSetting('init');
                    $public_key = $app->public_key;
                    $private_key = $app->private_key;
                    
                    
                    foreach ($ips as $ip)
                    {
                        $rand = Strings::generateRandomText(3,true,false,true,false);
                         # insert case
                        $server = new Server();
                        $server->setStatus_id(1);
                        $server->setProvider_id(1);
                        $server->setServer_type_id(2);
                        $server->setName("LINOD_".$account['account_name']."_".$rand);
                        $server->setHost_name("");
                        $server->setMain_ip($ip);
                        $server->setUsername("root");
                        $server->setPassword('');
                        $server->setServer_auth('1');
                        $server->setSsh_port(22);
                        $server->setExpiration_date(date("Y-m-d"));
                        $server->setCreated_by(intval(Arrays::getElement($data['user'],'id',1)));
                        $server->setCreated_at(date("Y-m-d"));
                        $server->setLast_updated_by(intval(Arrays::getElement($data['user'],'id',1)));
                        $server->setLast_updated_at(date("Y-m-d"));
                        $server->setServer_type(intval(1));

                        $result = $server->save(); 

                        if($result > -1)
                        {
                            $message = "Record stored succesfully !";
                            $messageFlag = 'success';
                        }
                    }
                    
                    # stores the message in the session 
                    Session::set('proccess_message_flag',$messageFlag);
                    Session::set('proccess_message',$message);
                        
                    die(json_encode(array("resaults" => 'Done')));
                }
                
            }
        }
        
        /**
         * @name add
         * @description the add action
         * @before init
         * @after setMenu,closeConnection
         */
        public function edit() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_isps',true);
            $this->getMasterView()->set('menu_admin_isps_add',true);
            
            $arguments = func_get_args(); 
            $id = isset($arguments) && count($arguments) ? $arguments[0] : null;

            if(isset($id) && is_numeric($id))
            {
                # retrieve the server by id
                $ServerApis = ServerApis::first(true,array('id = ?',$id));
                $status = Status::all(true);

                # set the data to the template
                $this->getPageView()->set('ServerApis',$ServerApis);
                $this->getPageView()->set('status',$status);
            }
        }
        
        /**
         * @name save
         * @description the save action
         * @before init
         * @after setMenu,closeConnection
         */
        public function save() 
        {    
            # get the connected user
            $user = Session::get('bluemail_connected_user'); 
            
            # retrieves the data from post
            $id = Request::getParameterFromPOST('api-account-id');
            $accountStatus = Request::getParameterFromPOST('status-id');
            $AccountName = Request::getParameterFromPOST('account-name');
            $Username = Request::getParameterFromPOST('api-user-name');
            $Password = Request::getParameterFromPOST('api-user-pass');
            $ApiUrl = Request::getParameterFromPOST('api-url');
            $ApiKey = Request::getParameterFromPOST('api-key');
            $SSHkey = Request::getParameterFromPOST('ssh-key');
            $Provider = Request::getParameterFromPOST('provider');
            $AccessKey = Request::getParameterFromPOST('access-key');
            $SecretKey = Request::getParameterFromPOST('secret-key'); 
            
            if(isset($AccountName))
            {
                $message = "Something went wrong !";
                $messageFlag = 'error';
                
                if($id != NULL && is_numeric($id))
                {
                    # update case
                    $Appiaccount = new ServerApis(array("id" => $id));
                    $Appiaccount->setStatus_id(intval($accountStatus));
                    $Appiaccount->setAccount_name($AccountName);
                    $Appiaccount->setUsername($Username);
                    $Appiaccount->setPassword($Password);
                    $Appiaccount->setApi_url($ApiUrl);
                    $Appiaccount->setApi_key($ApiKey);
                    $Appiaccount->setSsh_key($SSHkey);
                    $Appiaccount->setAccess_key($AccessKey);
                    $Appiaccount->setSecret_key($SecretKey);
                    $Appiaccount->setApi_provider($Provider);
                    $Appiaccount->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                    $Appiaccount->setCreated_at(date("Y-m-d"));
                    $Appiaccount->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                    $Appiaccount->setLast_updated_at(date("Y-m-d"));

                    $result = $Appiaccount->save();

                    if($result > -1)
                    {
                        $message = "Record updated succesfully !";
                        $messageFlag = 'success';
                    }
                }
                else
                {
                    # insert case
                   
                    $Appiaccount = new ServerApis();
                    $Appiaccount->setStatus_id(intval($accountStatus));
                    $Appiaccount->setAccount_name($AccountName);
                    $Appiaccount->setUsername($Username);
                    $Appiaccount->setPassword($Password);
                    $Appiaccount->setApi_url($ApiUrl);
                    $Appiaccount->setApi_key($ApiKey);
                    $Appiaccount->setSsh_key($SSHkey);
                    $Appiaccount->setAccess_key($AccessKey);
                    $Appiaccount->setSecret_key($SecretKey);
                    $Appiaccount->setApi_provider($Provider);
                    $Appiaccount->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                    $Appiaccount->setCreated_at(date("Y-m-d"));
                    $Appiaccount->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                    $Appiaccount->setLast_updated_at(date("Y-m-d"));

                    $result = $Appiaccount->save();
                    
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
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'accountapi' . RDS . 'lists.html'); 
        }

        /**
         * @name delete
         * @description the delete action
         * @before init
         * @after setMenu,closeConnection
         */
        public function delete() 
        {
            $arguments = func_get_args();
            $id = isset($arguments) && count($arguments) > 0 ? $arguments[0] : null;

            $message = "Something went wrong !";
            $messageFlag = 'error';

            if(isset($id) && is_numeric($id))
            {
                # delete the server
                $serverApi = new ServerApis(array("id" => $id));
                $serverApi->delete();
                $message = "Record deleted successfully !";
                $messageFlag = 'success';
            }

            # stores the message in the session 
            Session::set('proccess_message_flag',$messageFlag);
            Session::set('proccess_message',$message);

            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'accountapi' . RDS . 'lists.html');
        }
        
        /* HETZNER AREA  */
        
        /**
         * @name hetzner
         * @description manage dg api accounts hetzner
         * @before init
         * @after setMenu,closeConnection
         */
        public function hetzner() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_api',true);
            $this->getMasterView()->set('menu_admin_api_add',true);
            // gfbHrRdwqOeAuASbMRMXhmiUHMnpUK6wA8r18cQpnBiQ9vz6XhXo6qa2J0laQ4Li
            //
            //   curl -H "Authorization: Bearer gfbHrRdwqOeAuASbMRMXhmiUHMnpUK6wA8r18cQpnBiQ9vz6XhXo6qa2J0laQ4Li" 'https://api.hetzner.cloud/v1/locations'
            //   curl -H "Authorization: Bearer gfbHrRdwqOeAuASbMRMXhmiUHMnpUK6wA8r18cQpnBiQ9vz6XhXo6qa2J0laQ4Li" 'https://api.hetzner.cloud/v1/images'
            //            curl -H "Authorization: Bearer gfbHrRdwqOeAuASbMRMXhmiUHMnpUK6wA8r18cQpnBiQ9vz6XhXo6qa2J0laQ4Li" 'https://api.hetzner.cloud/v1/server_types'            
            // Manage APIs 
            $accounts = ServerApis::all(true,array(),array('id','account_name'),'id','ASC'); 
            
            # set the list into the template data system 
            $this->getPageView()->set('accounts',$accounts);
            
        }
        
        /**
         * @name getServersht
         * @description get Servers by API DigitalOceon
         * @before init
         * @after setMenu,closeConnection
         */
        public function getServersht() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    
                        # get the main values
                        $user = $data['user'];
                        $accountId = $data['accountId'];
                        $region = $data['region'];
                        $os = $data['os'];
                        $type = $data['type'];
                        $authType = $data['authType'];
                        $rootPassword = $data['rootPassword'];
                        $count = $data['count'];
                        $names = "";
                                                
                        $app = Application::getCurrent()->getSetting('init');
    
                    
                        
                        $account = ServerApis::first(true,array('id = ?',$accountId));
                        
                        $hitzner = array(
                            "api_type" => $account['api_provider'],
                            "api_url" => $account['api_url'],
                            "api_key" => $account['api_key']
                            );
                        
                        $api = Api::getAPIClass($hitzner);

                        $resalutTable = array();
                        
                        
                        for ($index1 = 0; $index1 < $count; $index1++)
                        {
                            $names = "";
                            $rand = Strings::generateRandomText(3,true,false,true,false);
                            $name = "bluemail-".$rand;
                            $res = json_decode($api->CreateServer($name , $type, $region, $os , "aapp"));
                            if(count($res))
                            {
                                $server = $res->server;
                                $ipnet = $server->public_net;
                                $ipv4 = $ipnet->ipv4;
                                $resalutTable[] = array('id'=>$server->id , 'label'=>$server->name , 'ipv4'=> $ipv4->ip , 'status'=>$server->status);
                            }
                        }
                        
                        
                    die(json_encode(array("resaults" => $resalutTable)));
                }
                
            }
        }
        
        /**
         * @name listHtServers Hitzner
         * @description get all Servers by API
         * @before init
         * @after setMenu,closeConnection
         */
        public function listHtServers() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                        # get the main values
                        $user = $data['user'];
                        $accountId = $data['accountId'];

                        $app = Application::getCurrent()->getSetting('init');
        
                        $account = ServerApis::first(true,array('id = ?',$accountId));

                        $ht = array(
                            "api_type" => $account['api_provider'],
                            "api_url" => $account['api_url'],
                            "api_key" => $account['api_key']
                            );

                        $api = Api::getAPIClass($ht);
                        $resalutTable = array();
                        $res = json_decode($api->getAllServers());
                        $servers = $res->servers;
                        
                        if(count($servers))
                        {
                            foreach ($servers as $server)
                            {
                                $net = $server->public_net;
                                $ipv4 = $net->ipv4;
                                $resalutTable[] = array('id'=>$server->id , 'label'=>$server->name , 'ipv4'=> $ipv4->ip , 'status'=>$server->status);
                            }
                        }
                        
                    die(json_encode(array("resaults" => $resalutTable)));
                }
                
            }
        }
        
        /**
         * @name addHtServers Hitzner
         * @description Store Servers on DB
         * @before init
         * @after setMenu,closeConnection
         */
        public function addHtServers() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $ips = json_decode($data['ips']);
                    $accountId = $data['accountId'];
                    
                    
                    $account = ServerApis::first(true,array('id = ?', trim($accountId)));
                    
                    
                    foreach ($ips as $ip)
                    {
                        $rand = Strings::generateRandomText(3,true,false,true,false);
                         # insert case
                        $server = new Server();
                        $server->setStatus_id(1);
                        $server->setProvider_id(1);
                        $server->setServer_type_id(2);
                        $server->setName("HT_".$account['account_name']."_".$rand);
                        $server->setHost_name("");
                        $server->setMain_ip($ip);
                        $server->setUsername("root");
                        $server->setPassword('');
                        $server->setServer_auth('1');
                        $server->setSsh_port(22);
                        $server->setExpiration_date(date("Y-m-d"));
                        $server->setCreated_by(intval(Arrays::getElement($data['user'],'id',1)));
                        $server->setCreated_at(date("Y-m-d"));
                        $server->setLast_updated_by(intval(Arrays::getElement($data['user'],'id',1)));
                        $server->setLast_updated_at(date("Y-m-d"));
                        $server->setServer_type(intval(1));

                        $result = $server->save(); 

                        if($result > -1)
                        {
                            $message = "Record stored succesfully !";
                            $messageFlag = 'success';
                        }
                    }
                    
                    # stores the message in the session 
                    Session::set('proccess_message_flag',$messageFlag);
                    Session::set('proccess_message',$message);
                        
                    die(json_encode(array("resaults" => 'Done')));
                }
                
            }
        }
        
        /**
         * @name removeInstances
         * @description get all Servers by API
         * @before init
         * @after setMenu,closeConnection
         */
        public function removeHtServers() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $instances = json_decode($data['instance']);
                    $ips = json_decode($data['ips']);
                    $accountId = $data['accountId'];
                    
                    $account = ServerApis::first(true,array('id = ?',$accountId));

                    $dg = array(
                        "api_type" => $account['api_provider'],
                        "api_url" => $account['api_url'],
                        "api_key" => $account['api_key']
                        );

                    $api = Api::getAPIClass($dg);
                    
                    
                    foreach ($instances as $instance)
                    {
                        $res = json_decode($api->removeDroplet($instance));
                    }
                    
                    // remove them from DB now ...
                    
                    foreach ($ips as $ip)
                    {
                       $serverAccount = Server::first(true,array('main_ip = ?',$ip),array('id','name','main_ip','username','password','ssh_port','server_type'));
                       
                       if($serverAccount != null && count($serverAccount))
                       {
                           $serverID = $serverAccount['id'];
                            $server = new Server(array("id" => $serverID));
                            $res = $server->delete();

                             # update domains 
                             Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.domains SET ip_id = 0 , domain_status = 'Available' WHERE ip_id IN (SELECT id FROM admin.ips WHERE server_id = $serverID)");
                             Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.ips WHERE server_id = $serverID");
                             Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.vmtas WHERE server_id = $serverID");
                       }
                        
                    }
                    
                        
                    die(json_encode(array("resaults" => 'Done')));
                }
                
            }
        }
        
        
        /* DG AREA */
        
        /**
         * @name db
         * @description manage dg api accounts
         * @before init
         * @after setMenu,closeConnection
         */
        public function dg() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_admin_api',true);
            $this->getMasterView()->set('menu_admin_api_add',true);
            
            // benaissa key : 16491ba4c96747cab04c7eb5f1e6eea986132fab01846af0fbd7ef91ce3b3d7a
            
            // Manage APIs 
            $accounts = ServerApis::all(true,array(),array('id','account_name'),'id','ASC'); 
            
            # set the list into the template data system 
            $this->getPageView()->set('accounts',$accounts);
            
        }
        
        /**
         * @name getServersdg
         * @description get Servers by API DigitalOceon
         * @before init
         * @after setMenu,closeConnection
         */
        public function getServersdg() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    
                        # get the main values
                        $user = $data['user'];
                        $accountId = $data['accountId'];
                        $region = $data['region'];
                        $os = $data['os'];
                        $type = $data['type'];
                        $authType = $data['authType'];
                        $rootPassword = $data['rootPassword'];
                        $count = $data['count'];
                        $names = "";
                                                
                        $app = Application::getCurrent()->getSetting('init');
    
                    
                        
                        $account = ServerApis::first(true,array('id = ?',$accountId));
                        
                        $digital = array(
                            "api_type" => $account['api_provider'],
                            "api_url" => $account['api_url'],
                            "api_key" => $account['api_key']
                            );
                        
                        $api = Api::getAPIClass($digital);
                                    
                        $resalutTable = array();
                        
                        
                        for ($index1 = 0; $index1 < $count; $index1++)
                        {
                            $rand = Strings::generateRandomText(3,true,false,true,false);
                            $names[] = "bluemail-".$rand;
                        }
                        
                        $res = json_decode($api->CreateServer($names , $type, $region, $os , $account['ssh_key']));
                        //\ma\mfw\output\PrintWriter::printValue($res);
                        $droplets = $res->droplets;
                        if(!count($droplets))
                        {
                            \ma\mfw\output\PrintWriter::printValue($res);
                        }
                        
                        foreach ($droplets as $droplet)
                        {
                           $resalutTable[] = array('id'=>$droplet->id , 'label'=>$droplet->name , 'ipv4'=> '0.0.0.0' , 'status'=>$droplet->status);
                        }
                        
                    die(json_encode(array("resaults" => $resalutTable)));
                }
                
            }
        }
        
        /**
         * @name removeInstances
         * @description get all Servers by API
         * @before init
         * @after setMenu,closeConnection
         */
        public function removeDroplets() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $instances = json_decode($data['instance']);
                    $ips = json_decode($data['ips']);
                    $accountId = $data['accountId'];
                    
                    $account = ServerApis::first(true,array('id = ?',$accountId));

                    $dg = array(
                        "api_type" => $account['api_provider'],
                        "api_url" => $account['api_url'],
                        "api_key" => $account['api_key']
                        );

                    $api = Api::getAPIClass($dg);
                    
                    
                    foreach ($instances as $instance)
                    {
                        $res = json_decode($api->removeDroplet($instance));
                    }
                    
                    // remove them from DB now ...
                    
                    foreach ($ips as $ip)
                    {
                       $serverAccount = Server::first(true,array('main_ip = ?',$ip),array('id','name','main_ip','username','password','ssh_port','server_type'));
                       
                       if($serverAccount != null && count($serverAccount))
                       {
                           $serverID = $serverAccount['id'];
                            $server = new Server(array("id" => $serverID));
                            $res = $server->delete();

                             # update domains 
                             Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.domains SET ip_id = 0 , domain_status = 'Available' WHERE ip_id IN (SELECT id FROM admin.ips WHERE server_id = $serverID)");
                             Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.ips WHERE server_id = $serverID");
                             Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.vmtas WHERE server_id = $serverID");
                       }
                        
                    }
                    
                        
                    die(json_encode(array("resaults" => 'Done')));
                }
                
            }
        }
        
        
        /**
         * @name listServers Digitaloceon
         * @description get all Servers by API
         * @before init
         * @after setMenu,closeConnection
         */
        public function listDgServers() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                        # get the main values
                        $user = $data['user'];
                        $accountId = $data['accountId'];

                        $app = Application::getCurrent()->getSetting('init');
        
                        $account = ServerApis::first(true,array('id = ?',$accountId));
                        
                        $dg = array(
                            "api_type" => $account['api_provider'],
                            "api_url" => $account['api_url'],
                            "api_key" => $account['api_key']
                            );

                        $api = Api::getAPIClass($dg);
                        $resalutTable = array();
                        $res = json_decode($api->getAllServers());
                        
                        $droplets = $res->droplets;
                        
                        if(count($droplets))
                        {
                            foreach ($droplets as $droplet)
                            {
                                 $ip = $droplet->networks;
                                 $v4 = $ip->v4;
                                 $ipv4 = $v4[0]->ip_address;
                                 $resalutTable[] = array('id'=>$droplet->id , 'label'=>$droplet->name , 'ipv4'=> $ipv4 , 'status'=>$droplet->status);
                            }
                        }
                        
                    die(json_encode(array("resaults" => $resalutTable)));
                }
                
            }
        }
        
        
        /**
         * @name addServers Digital
         * @description add servers to DB
         * @before init
         * @after setMenu,closeConnection
         */
        public function addDgServers() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $ips = json_decode($data['ips']);
                    $accountId = $data['accountId'];
                    
                    
                    $account = ServerApis::first(true,array('id = ?',$accountId));
                    
                    $app = Application::getCurrent()->getSetting('init');
                    $public_key = $app->public_key;
                    $private_key = $app->private_key;
                    
                    
                    foreach ($ips as $ip)
                    {
                        $rand = Strings::generateRandomText(3,true,false,true,false);
                         # insert case
                        $server = new Server();
                        $server->setStatus_id(1);
                        $server->setProvider_id(1);
                        $server->setServer_type_id(2);
                        $server->setName("DG_".$account['account_name']."_".$rand);
                        $server->setHost_name("");
                        $server->setMain_ip($ip);
                        $server->setUsername("root");
                        $server->setPassword('');
                        $server->setServer_auth('1');
                        $server->setSsh_port(22);
                        $server->setExpiration_date(date("Y-m-d"));
                        $server->setCreated_by(intval(Arrays::getElement($data['user'],'id',1)));
                        $server->setCreated_at(date("Y-m-d"));
                        $server->setLast_updated_by(intval(Arrays::getElement($data['user'],'id',1)));
                        $server->setLast_updated_at(date("Y-m-d"));
                        $server->setServer_type(intval(1));

                        $result = $server->save(); 

                        if($result > -1)
                        {
                            $message = "Record stored succesfully !";
                            $messageFlag = 'success';
                        }
                    }
                    
                    # stores the message in the session 
                    Session::set('proccess_message_flag',$messageFlag);
                    Session::set('proccess_message',$message);
                        
                    die(json_encode(array("resaults" => 'Done')));
                }
                
            }
        }
        
        
        /**
         * @name testIps
         * @description get all Servers by API
         * @before init
         * @after setMenu,closeConnection
         */
        public function testDgIps() 
        {
            # check if the request is not AJAX request then return to index 
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                # retreive
                $data = Request::getAllDataFromPOST();
                
                if(count($data))
                {
                    
                    # add user data 
                    $data['user'] = Session::get('bluemail_connected_user');
                    # add user data 
                    $data['api-link'] = URL::getCurrentApplicationURL() . RDS . 'api'; 

                    $ips = json_decode($data['ips']);
                    $emails =  $data['emails'];
                    
                    $app = Application::getCurrent()->getSetting('init');
                    $public_key = $app->public_key;
                    $private_key = $app->private_key;
                    
                    
                    foreach ($ips as $ip)
                    {
                        $sshAuthenticator = new \ma\mfw\ssh2\SSHKeyAuthentication('root', $public_key, $private_key);
                        $sshConnector = new SSH($ip,$sshAuthenticator,22);
                        
                        if($sshConnector->isConnected())
                        {
                            $sshConnector->cmd('yum -y update &');
                            $sshConnector->cmd('yum install -y mailx &');
                            $sshConnector->cmd('yum install -y sendmail &');
                            \ma\mfw\output\PrintWriter::printValue('echo "Blue mail tester body" | mail -v -r "Bluemail IP Tester : '.$ip.' <no-reply@email.etihadguest.com>" -s "Bluemail Tester for IP : '.$ip.'" ' .$emails);
                            $sshConnector->cmd("echo 'Blue mail tester body' | mail -v -r 'Bluemail IP Tester : ".$ip." <no-reply@email.etihadguest.com>' -s 'Bluemail Tester for IP : ".$ip."' ".$emails);
                        }
                        else
                        {
                            die(json_encode(array("resaults" => 'Can not connect to the server '.$ip)));
                        }
                        
                    }
                        
                    die(json_encode(array("resaults" => 'Done Test has been sent to ' . $emails[0])));
                }
                
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
            $this->getMasterView()->set('menu_admin_data',true);
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