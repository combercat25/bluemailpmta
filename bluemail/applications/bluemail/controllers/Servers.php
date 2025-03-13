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
    use ma\mfw\files\Paths as Paths; 
    use ma\mfw\os\System as System;
    use ma\mfw\types\Arrays as Arrays;
    use ma\mfw\www\Domains as Domains; 
    use ma\applications\bluemail\models\admin\Vmta as Vmta;
    use ma\applications\bluemail\models\admin\Server as Server;
    use ma\applications\bluemail\models\admin\Status as Status;
    use ma\applications\bluemail\models\admin\ServerType as ServerType;
    use ma\applications\bluemail\models\admin\Ip as Ip;
    use ma\applications\bluemail\models\admin\Domain as Domain;
    use ma\applications\bluemail\models\admin\ServerProvider as ServerProvider;
    use ma\mfw\ssh2\SSH as SSH;
    use ma\mfw\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
    use ma\mfw\globals\Server as GloblServers;
    use ma\mfw\encryption\Crypto as Crypto;
    use ma\mfw\types\Strings as Strings;
    use ma\applications\bluemail\helpers\PagesHelper as PagesHelper;
    use ma\mfw\exceptions\types\PageException as PageException;
    use ma\mfw\api\NameCheap as NameCheap;
    use ma\mfw\configuration\Configuration as Configuration;



    /**
     * @name            Servers.controller 
     * @description     The Servers controller
     * @package		ma\applications\bluemail\controllers
     * @category        Controller
     * @author		Miami Team			
     */
    class Servers extends Controller 
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
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'lists.html');
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
            $this->getMasterView()->set('menu_admin_servers',true);
            $this->getMasterView()->set('menu_admin_servers_list',true);

            # get the data from the database
            $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT s.id AS id,"
                                            . " s.name AS server_name,"
                                            . " p.name AS server_provider,"
                                            . " stp.name AS server_type,"
                                            . " st.name AS status,"
                                            . " s.host_name AS host_name,"
                                            . " s.expiration_date AS expiration_date,"
                                            . " s.main_ip AS main_ip,"
                                            . " (SELECT count(*) FROM admin.ips where server_id = s.id) AS ips_sum,"
                                            . " uc.username AS created_by,"
                                            . " st.created_at AS created_date"
                                            . " FROM admin.servers s"
                                            . " LEFT JOIN admin.server_providers p ON p.id = s.provider_id"
                                            . " LEFT JOIN admin.server_types stp ON stp.id = s.server_type_id"
                                            . " LEFT JOIN admin.users uc ON uc.id = s.created_by"
                                            . " LEFT JOIN admin.status st ON st.id = s.status_id"
                                            . " LEFT JOIN admin.users up ON up.id = s.last_updated_by"
                                            . " ORDER BY st.id", true);
                                    
            # get all the columns names 
            $columns = array('id','server_name','created_by','server_provider','server_type','status','host_name','main_ip','ips_sum','expiration_date','days_left');

            $today = date_create(date('Y-m-d'));
            
            foreach ($list as &$row) 
            {
                if(strlen($row['expiration_date']) > 0)
                {
                    $date = date_create($row['expiration_date']);
                    $diff = date_diff($today,$date);  
                    $row['days_left'] = $diff->days;
                }
            }

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
            $this->getMasterView()->set('menu_admin_servers',true);
            $this->getMasterView()->set('menu_admin_servers_add',true);

            # get providers list 
            $providers = ServerProvider::all(true,array(),array('id','name'),'id','ASC');

            # get providers list 
            $types = ServerType::all(true,array(),array('id','name'),'id','ASC');

            # get status list 
            $status = Status::all(true,array(),array('id','name'),'id','ASC');

            # set the data to the template
            $this->getPageView()->set('providers',$providers);
            $this->getPageView()->set('types',$types);
            $this->getPageView()->set('status',$status);
        }
        /**
* @name addMultiple
* @description the add action
* @before init
* @after setMenu,closeConnection
*/
public function addMultiple()
{
# set the menu item to active
$this->getMasterView()->set('menu_admin_servers',true);
$this->getMasterView()->set('menu_admin_servers_add',true);

# get providers list
$providers = ServerProvider::all(true,array(),array('id','name'),'id','ASC');

# get providers list
$types = ServerType::all(true,array(),array('id','name'),'id','ASC');

# get status list
$status = Status::all(true,array(),array('id','name'),'id','ASC');

# set the data to the template
$this->getPageView()->set('providers',$providers);
$this->getPageView()->set('types',$types);
$this->getPageView()->set('status',$status);

}
        /**
         * @name edit
         * @description the edit action
         * @before init
         * @after setMenu,closeConnection
         */
        public function edit() 
        {
            $arguments = func_get_args(); 
            $id = isset($arguments) && count($arguments) > 0 ? $arguments[0] : null;

            if(isset($id) && is_numeric($id))
            {
                # retrieve the server by id
                $server = Server::first(true,array('id = ?',$id));
                
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_servers',true);
                $this->getMasterView()->set('menu_admin_servers_add',true);

                # get providers list 
                $providers = ServerProvider::all(true,array(),array('id','name'),'id','ASC');

                # get providers list 
                $types = ServerType::all(true,array(),array('id','name'),'id','ASC');

                # get status list 
                $status = Status::all(true,array(),array('id','name'),'id','ASC');

                # get the data from the database
                $ipsList = Database::getCurrentDatabaseConnector()->executeQuery("SELECT ip.id AS id,"
                                                . " ip.value AS ip,"
                                                . " ip.rdns AS domain,"
                                                . " st.name AS status,"
                                                . " uc.username AS created_by,"
                                                . " st.created_at AS created_date,"
                                                . " up.username AS last_updated_by,"
                                                . " st.last_updated_at AS last_updated_at"
                                                . " FROM admin.ips ip"
                                                . " LEFT JOIN admin.users uc ON uc.id = ip.created_by"
                                                . " LEFT JOIN admin.status st ON st.id = ip.status_id"
                                                . " LEFT JOIN admin.users up ON up.id = ip.last_updated_by"
                                                . " WHERE ip.server_id = $id"
                                                . " ORDER BY ip.id", true);
                
                # get the server vmtas from db
                $vmtaList = Database::getCurrentDatabaseConnector()->executeQuery("SELECT vmtas.id AS id,"
                                                . " vmtas.name AS name,"
                                                . " vmtas.type AS type,"
                                                . " vmtas.ip_value AS ip,"
                                                . " vmtas.domain AS domain,"
                                                . " st.name AS status,"
                                                . " uc.username AS created_by,"
                                                . " st.created_at AS created_date,"
                                                . " up.username AS last_updated_by,"
                                                . " st.last_updated_at AS last_updated_at"
                                                . " FROM admin.vmtas vmtas"
                                                . " LEFT JOIN admin.users uc ON uc.id = vmtas.created_by"
                                                . " LEFT JOIN admin.status st ON st.id = vmtas.status_id"
                                                . " LEFT JOIN admin.users up ON up.id = vmtas.last_updated_by"
                                                . " WHERE vmtas.server_id = $id"
                                                . " ORDER BY vmtas.id", true);
                
                
                # get all the columns names 
                $ipsColumns = array('id','ip','domain','status','created_by','created_date','last_updated_by','last_updated_at');
                
                # get all the columns names for vmtas
                $vmtaColumns = array('id','name','type','ip_value','domain','status','created_by','created_date','last_updated_by','last_updated_at');

                # set the list into the template data system 
                $this->getPageView()->set('ipsList',$ipsList);

                # set the columns list into the template data system 
                $this->getPageView()->set('ipsColumns',$ipsColumns);
                
                # set the list into the template data system 
                $this->getPageView()->set('vmtaList',$vmtaList);

                # set the columns list into the template data system 
                $this->getPageView()->set('vmtaColumns',$vmtaColumns);
                
                # set the data to the template
                $this->getPageView()->set('server',$server);
                $this->getPageView()->set('providers',$providers);
                $this->getPageView()->set('types',$types);
                $this->getPageView()->set('status',$status);
                
                # check for message 
                PagesHelper::checkForMessageToPage($this);
            }
        }
        
        /**
         * @name vmta
         * @description edit vmta
         * @before init
         * @after setMenu,closeConnection
         */
        public function vmta() 
        {
            $arguments = func_get_args(); 
            $server_id = isset($arguments) && count($arguments) > 0 ? $arguments[0] : null;
            $vmta_id = isset($arguments) && count($arguments) > 0 ? $arguments[1] : null;

            if(isset($server_id) && is_numeric($server_id))
            {
                $message = "Something went wrong !";
                $messageFlag = 'error';
                
                $server = Server::first(true,array('id = ?',$server_id));
                if(count($server))
                {
                    $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                    $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                    if($sshConnector->isConnected())
                    {
                        $resault = $sshConnector->cmd('find /etc/pmta/vmtas/*');
                        \ma\mfw\output\PrintWriter::printValue($resault);
                        $sshConnector->disconnect();
                    }  
                }
                
            }
        }
        
        /**
         * @name pmta
         * @description the pmta config action
         * @before init
         * @after setMenu,closeConnection
         */
        public function pmta() 
        {
            $data = Request::getAllDataFromPOST();

            if(isset($data) && count($data))
            {
                $id = $data['server-id'];
                $config = $data['config'];
                
                $message = "Something went wrong !";
                $messageFlag = 'error';
                
                $server = Server::first(true,array('id = ?',$id));
                
                if(strlen($config) > 0 && count($server))
                {
                    $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                    $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                    if($sshConnector->isConnected())
                    {
                        $sshConnector->cmd('service pmta stop',true);
                        $sshConnector->cmd('mv /etc/pmta/config /etc/pmta/config_bak',true);
                        $sshConnector->scp('send',array('/etc/pmta/config'),$config);
                        $sshConnector->cmd('service pmta start',true);
                        $sshConnector->cmd('service pmta reload',true);
                        $message = "PMTA Config updated successfully !";
                        $messageFlag = 'success';
                
                        $sshConnector->disconnect();
                    }  
                }
                
                # stores the message in the session 
                Session::set('proccess_message_flag',$messageFlag);
                Session::set('proccess_message',$message);
                
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'pmta' . RDS . $id . '.html');
            }
            
            $arguments = func_get_args(); 
            $id = isset($arguments) && count($arguments) > 0 ? $arguments[0] : null;

            if(isset($id) && is_numeric($id))
            {
                $server = Server::first(true,array('id = ?',$id));
                $config = '';
                
                if($server != null  && count($server))
                {
                    $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                    $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                    if($sshConnector->isConnected())
                    {
                        $cmd = "cat /etc/pmta/config";
                        $config = $sshConnector->cmd($cmd,true);
                        $sshConnector->disconnect();
                    }  
                }
                
                # set the data to the template
                $this->getPageView()->set('server',$server);
                $this->getPageView()->set('config',$config);
            }
            else
            {
                $message = "Something went wrong !";
                $messageFlag = 'error';
                
                # stores the message in the session 
                Session::set('proccess_message_flag',$messageFlag);
                Session::set('proccess_message',$message);
                
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'lists.html'); 
            }
            
            # check for message 
            PagesHelper::checkForMessageToPage($this);
        }
        
        /**
         * @name install
         * @description installation action
         * @before init
         * @after setMenu,closeConnection
         */
        public function install() 
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
                    
                    # encrypt the data  
                    $parameters = Crypto::AESEncrypt($data);
                    
                    # log file path 
                    $logFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $data['server-id'] . '.log';
                    
                    # executing the script that handles servers installations
                    System::executeCommand('nohup php ' . Paths::getCurrentApplicationRealPath() . DS . 'scripts' . DS . 'servers' . DS . 'install.php ' . $parameters . ' > ' . $logFile . ' 2> ' . $logFile . ' &');
                    
                    die(json_encode(array("started" => true)));
                }
            }
            else
            {
                $arguments = func_get_args();   
                $id = isset($arguments) && count($arguments) > 0 ? $arguments[0] : null;

                if(isset($id) && is_numeric($id))
                {
                    $ips = array();
                    $ipIds = array();
                    $subdomains = 'disabled';

                    # retrieve the server by id
                    $server = Server::first(true,array('id = ?',$id),array('id','name','main_ip','username','password','ssh_port' ,'server_type' ));

                    if($server != null  && count($server))
                    {
                        $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                        $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                        if($sshConnector->isConnected())
                        {
                            $prefix = $server['username'] != 'root' ? "echo {$server['password']} | sudo -S " : '';
                            $release = $sshConnector->cmd("cat /etc/centos-release",true);

                            if(strpos($release,'CentOS Linux release 7') > -1)
                            {
                                $version = 7;
                                $cmd = "{$prefix}ip addr show | grep 'inet\|inet6' | grep -v '127.0.0.1' | cut -f2 | awk '{ print $2}' | cut -f 1 -d '/' | awk '{ print $1}'| grep -v ^::1 | cut -d/ -f 1 | sort -t: -n -k2";
                            }
                            else
                            {
                                if($server['server_type'] == 2)
                                {
                                    $version = 6;
                                    $cmd = "{$prefix}ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'";
                                }
                                else
                                {
                                $version = 6;
                                $cmd = "{$prefix}ifconfig  | grep 'inet\|inet6'|  awk '{ print $0}' | grep -v Link | grep -v '127.0.0.1' | cut -d: -f 2-10 | awk '{ print $1}'| grep -v ^::1 | cut -d/ -f 1 | sort -t: -n -k2";
                                }
                                
                            }

                            $result = explode(PHP_EOL,trim($sshConnector->cmd($cmd,true),PHP_EOL));
                            $sshConnector->disconnect();
                        }  
                    }

                    foreach ($result as $ip) 
                    {
                        $ipObject = Ip::first(true,array('value = ?',trim($ip)));

                        if(count($ipObject))
                        {
                            $ipIds[] = $ipObject['id'];
                            $ips[] = array('id' => $ipObject['id'],'ip' => $ipObject['value'],'domain' => $ipObject['rdns']);

                            # check if there is any subdomains
                            if(count(explode('.',$ipObject['rdns'])) > 2)
                            {
                                $subdomains = 'enabled';
                            }
                        }
                        else
                        {
                            $ips[] = array('id' => 0,'ip' => $ip,'domain' => '');
                        } 
                    }

                    $ipsCondition = (count($ipIds) > 0) ? 'OR ip_id IN (' . implode(',', $ipIds) . ')' : '';
                    $domains = Domain::all(true,array('domain_status = ? ' . $ipsCondition,'Available'),array('*'),'value','ASC');

                    # set the data to the template
                    $this->getPageView()->set('server',$server);
                    $this->getPageView()->set('ips',$ips);
                    $this->getPageView()->set('subdomains',$subdomains);
                    $this->getPageView()->set('version',$version);
                    $this->getPageView()->set('domains',$domains);
                }
            } 
        }

        /**
         * @name uninstall
         * @description the uninstall action
         * @before init
         * @after setMenu,closeConnection
         */
        public function uninstall() 
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
                    $serverId = intval($data['server-id']);
                    
                    if($serverId > 0)
                    {    
                        # executing the script that handles servers installations
                        System::executeCommand('php ' . Paths::getCurrentApplicationRealPath() . DS . 'scripts' . DS . 'servers' . DS . 'uninstall.php ' . $serverId);   

                        die(json_encode(array("type" => "success" , "message" => "Server has been uninstalled successfully !")));
                    }
                }
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
                
                $logFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $data['server-id'] . '.log';
                $statusFile = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $data['server-id'] . '.process';

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
            $id = Request::getParameterFromPOST('server-id');
            $serverName = Request::getParameterFromPOST('server-name');
            $serverStatus = Request::getParameterFromPOST('status-id');
            $serverProvider = Request::getParameterFromPOST('server-provider-id');
            $sType = Request::getParameterFromPOST('server-type');
            $serverType = 2;
            $serverHost = Request::getParameterFromPOST('server-host');
            $serverMainIp = Request::getParameterFromPOST('server-ip');
            $serverUsername = Request::getParameterFromPOST('server-username');
            $serverPassword = Request::getParameterFromPOST('server-password');
            $serverSSHPort = Request::getParameterFromPOST('server-ssh-port');
            $expirationDate = Request::getParameterFromPOST('expiration-date');

            if(isset($serverName))
            {
                $message = "Something went wrong !";
                $messageFlag = 'error';
                
                # check if the ip value is a valid ip 
                if(!filter_var($serverMainIp,FILTER_VALIDATE_IP))
                {
                    $message = "Error While Saving Your Record : Please Insert a Valid Main IP !";
                    $messageFlag = 'error';
                }
                elseif(!is_numeric($serverSSHPort) || strlen($serverSSHPort) > 4)
                {
                    $message = "Error While Saving Your Record : Please Insert a Valid SSH Port ! (It should be a numeric value between 2 and 4 digits)";
                    $messageFlag = 'error';
                }
                else
                {
                    if($id != NULL && is_numeric($id))
                    {
                        # update case
                        $server = new Server(array("id" => $id));
                        $server->setStatus_id(intval($serverStatus));
                        $server->setProvider_id($serverProvider);
                        $server->setServer_type_id($serverType);
                        $server->setName($serverName);
                        $server->setHost_name($serverHost); 
                        $server->setMain_ip($serverMainIp);
                        $server->setUsername($serverUsername);
                        $server->setPassword($serverPassword);
                        $server->setSsh_port($serverSSHPort);
                        $server->setExpiration_date($expirationDate);
                        $server->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                        $server->setLast_updated_at(date("Y-m-d"));
                        $server->setServer_type(intval($sType));

                        $result = $server->save(); 

                        if($result > -1)
                        {
                            $message = "Record updated succesfully !";
                            $messageFlag = 'success';
                        }
                    }
                    else
                    {
                        # insert case
                        $server = new Server();
                        $server->setStatus_id(intval($serverStatus));
                        $server->setProvider_id($serverProvider);
                        $server->setServer_type_id($serverType);
                        $server->setName($serverName);
                        $server->setHost_name($serverHost);
                        $server->setMain_ip($serverMainIp);
                        $server->setUsername($serverUsername);
                        $server->setPassword($serverPassword);
                        $server->setSsh_port($serverSSHPort);
                        $server->setExpiration_date($expirationDate);
                        $server->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                        $server->setCreated_at(date("Y-m-d"));
                        $server->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                        $server->setLast_updated_at(date("Y-m-d"));
                        $server->setServer_type(intval($sType));

                        $result = $server->save(); 

                        if($result > -1)
                        {
                            $message = "Record stored succesfully !";
                            $messageFlag = 'success';
                        }
                   }
                }

                # stores the message in the session 
                Session::set('proccess_message_flag',$messageFlag);
                Session::set('proccess_message',$message);
            }
            
            # redirect to show list 
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'lists.html'); 
        }
/**
* @name saveMultiple
* @description the saveMultiple action
* @before init
* @after setMenu,closeConnection
*/
public function saveMultiple()
{
# get the connected user
$user = Session::get('bluemail_connected_user');

# retrieves the data from post
$id = Request::getParameterFromPOST('server-id');
$serverName = Request::getParameterFromPOST('server-name');
$serverStatus = Request::getParameterFromPOST('status-id');
$serverProvider = Request::getParameterFromPOST('server-provider-id');
$sType = Request::getParameterFromPOST('server-type');
$serverType = 2;
$serverHost = Request::getParameterFromPOST('server-host');
$srvIps = Request::getParameterFromPOST('servers-ip');
$serverUsername = Request::getParameterFromPOST('server-username');
$serverPassword = Request::getParameterFromPOST('server-password');
$serverSSHPort = Request::getParameterFromPOST('server-ssh-port');
$expirationDate = Request::getParameterFromPOST('expiration-date');

if(isset($serverName))
{
$message = "Something went wrong !";
$messageFlag = 'error';
$lines = explode("\n", $srvIps);

for ($i=0; $i < count($lines); $i++)
{

# insert case
$server = new Server();
$server->setStatus_id(intval($serverStatus));
$server->setProvider_id($serverProvider);
$server->setServer_type_id($serverType);
$server->setName($serverName."-".$i);
$server->setHost_name($serverHost);
$server->setMain_ip($lines[$i]);
$server->setUsername($serverUsername);
$server->setPassword($serverPassword);
$server->setSsh_port($serverSSHPort);
$server->setExpiration_date($expirationDate);
$server->setCreated_by(intval(Arrays::getElement($user,'id',1)));
$server->setCreated_at(date("Y-m-d"));
$server->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
$server->setLast_updated_at(date("Y-m-d"));
$server->setServer_type(intval($sType));

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
}

# redirect to show list
Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'lists.html');
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

            if(isset($id) && is_numeric($id))
            {
                $message = "Something went wrong !";
                $messageFlag = 'error';

                if(isset($id) && is_numeric($id))
                {
                    # delete the server
                    $server = new Server(array("id" => $id));
                    $server->delete();

                    # update domains 
                    Database::getCurrentDatabaseConnector()->executeQuery("UPDATE admin.domains SET ip_id = 0 , domain_status = 'Available' WHERE ip_id IN (SELECT id FROM admin.ips WHERE server_id = $id)");
                    Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.ips WHERE server_id = $id");
                    
                    $message = "Record deleted successfully !";
                    $messageFlag = 'success';
                }

                # stores the message in the session 
                Session::set('proccess_message_flag',$messageFlag);
                Session::set('proccess_message',$message);

                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'lists.html');
            }
        }
        
        /**
         * @name types
         * @description the types action
         * @before init
         * @after setMenu,closeConnection
         */
        public function types() 
        {
            $arguments = func_get_args(); 
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'lists';

            if(isset($page))
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_servers_types',true);
                
                switch ($page) 
                {
                    case 'lists' :
                    {
                        # set the template for the page view 
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'servers' . DS . 'types' . DS . 'lists' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active 
                        $this->getMasterView()->set('menu_admin_servers_types_list',true);

                        # get the data from the database
                        $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT st.id AS id,"
                                                        . " st.name AS type,"
                                                        . " s.name AS status,"
                                                        . " uc.username AS created_by,"
                                                        . " st.created_at AS created_date,"
                                                        . " up.username AS last_updated_by,"
                                                        . " st.last_updated_at AS last_updated_at"
                                                        . " FROM admin.server_types st"
                                                        . " LEFT JOIN admin.users uc ON uc.id = st.created_by"
                                                        . " LEFT JOIN admin.status s ON st.status_id = s.id"
                                                        . " LEFT JOIN admin.users up ON up.id = st.last_updated_by"
                                                        . " ORDER BY st.id", true);

                        # get all the columns names 
                        $columns = array('id','type','status','created_by','created_date','last_updated_by','last_updated_at');

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
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'servers' . DS . 'types' . DS . 'add' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active 
                        $this->getMasterView()->set('menu_admin_servers_types_add',true);
                        
                        # get status list 
                        $status = Status::all(true,array(),array('id','name'),'id','ASC');
                        
                        # set the list into the template data system 
                        $this->getPageView()->set('status',$status);
                        break;
                    }
                    case 'edit' :
                    {
                        $id = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;

                        if(isset($id) && is_numeric($id))
                        {
                            # retrieve the server by id
                            $type = ServerType::first(true,array('id = ?',$id));

                            # get status list 
                            $status = Status::all(true,array(),array('id','name'),'id','ASC');

                            # set the template for the page view 
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'servers' . DS . 'types' . DS . 'edit' . '.' . $this->getDefaultExtension());

                            # set the menu item to active 
                            $this->getMasterView()->set('menu_admin_servers_types_add',true);

                            # set the data to the template
                            $this->getPageView()->set('type',$type);

                            # set the list into the template data system 
                            $this->getPageView()->set('status',$status);
                        }
                        break;
                    }
                    case 'save' :
                    {
                        # get the connected user
                        $user = Session::get('bluemail_connected_user'); 

                        # retrieves the data from post
                        $id = Request::getParameterFromPOST('server-type-id');
                        $serverType = Request::getParameterFromPOST('server-type');
                        $serverTypeStatus = Request::getParameterFromPOST('status-id');

                        if(isset($serverType))
                        {
                            $message = "Something went wrong !";
                            $messageFlag = 'error';

                            if($id != NULL && is_numeric($id))
                            {
                                # update case
                                $type = new ServerType(array("id" => $id));
                                $type->setStatus_id(intval($serverTypeStatus));
                                $type->setName($serverType);
                                $type->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                $type->setLast_updated_at(date("Y-m-d"));

                                $result = $type->save(); 

                                if($result > -1)
                                {
                                    $message = "Record updated succesfully !";
                                    $messageFlag = 'success';
                                }
                            }
                            else
                            {
                                # insert case
                                $type = new ServerType();
                                $type->setStatus_id(intval($serverTypeStatus));
                                $type->setName($serverType);
                                $type->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                $type->setCreated_at(date("Y-m-d"));
                                $type->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                $type->setLast_updated_at(date("Y-m-d"));

                                $result = $type->save(); 

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
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'types' . RDS . 'lists.html');
                    }
                    case 'delete' :
                    {
                        $id = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;

                        $message = "Something went wrong !";
                        $messageFlag = 'error';

                        if(isset($id) && is_numeric($id))
                        {
                            # delete the server
                            $type = new ServerType(array("id" => $id));
                            $type->delete();
                            $message = "Record deleted successfully !";
                            $messageFlag = 'success';
                        }

                        # stores the message in the session 
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);

                        # redirect to show list 
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'types' . RDS . 'lists.html');
                        
                        break;
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'types' . RDS . 'lists.html');
                    }    
                }
            }
        }
        
        /**
         * @name getServerDomain
         * @description Get all the domains releaded to a server ....
         * @after setMenu,closeConnection
         */
        public function getServerDomains() 
        {
			# connect to the default database 
            Database::secureConnect();
            
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
                    
                    if($serverId == 0)
                    {
                        die(json_encode(array('type' => 'error','message' => 'Please Select a Server !')));
                    }
                    
                    # get the data from the database
                    $domainsList = Database::getCurrentDatabaseConnector()->executeQuery("SELECT ip.rdns AS domain FROM admin.ips ip"
                                                                                    . " WHERE ip.server_id = $serverId"
                                                                                    . " ORDER BY ip.id", true);
//                    for ($index = 0; $index < count($domainsList); $index++)
//                    {
//                        if(explode('.', $domainsList[$index]['domain']) > 1)
//                        {
//                            $domain = $domainsList[$index]['domain'];
//                            unset($domainsList);
//                            $domainsList[] = $domain;
//                            break;
//                        }
//                    }

                    # print the result 
                    die(json_encode(array("type" => "success" , "message" => "Domains Here !" , "resault" => $domainsList )));
                    
                }
                
            }
            else
            {
                # redirect to show list 
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'mail.html');
            }
        }
        
        /**
         * @name large
         * @description Install the large servers ... 
         * @before init
         * @after setMenu,closeConnection
         */
        public function large() 
        {
            //if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            if(count(Request::getAllDataFromPOST()))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                # retreive
                $data = Request::getAllDataFromPOST();
                $nameCheap = new NameCheap();
                
                if(count($data))
                {
                    $serverId = $data['server-id'];
                    $ips = explode(PHP_EOL, $data['ips']);
                    $domains = explode(PHP_EOL, $data['domains']);
                    $subDomains = explode(PHP_EOL, $data['subdomains']);
                    $mapStep = count($subDomains);
                    $resault = count($domains);
                    $user = Session::get('bluemail_connected_user');
                    
                    if($serverId != null && $serverId != '')
                    {
                        $server = Server::first(true,array('id = ?',$serverId),array('id','name','main_ip','username','password','ssh_port','server_type'));
                     
                        //if($server != null  && count($server) && $server['server_type'] == 2)
                        if($server != null  && count($server))
                        {
                            if(count($ips) && count($domains) && count($subDomains))
                            {
                                $mapGlobal = Array();
                                $start = 0;
                                $end = $mapStep;

                                for ($i = 0; $i < count($domains); $i++)
                                {
                                      $map['domain'] = $domains[$i];
                                      $map['subs'] = $subDomains;

                                      $mapIps = array();

                                      for ($j = $start; $j <= $end ; $j++) {

                                          if($ips[$j] != null && $ips[$j] != '')
                                          {
                                              $mapIps[] = $ips[$j];
                                          }

                                      }

                                      $map['ips'] = $mapIps;

                                      array_push($mapGlobal, $map);
                                      $start = $start + $mapStep;
                                      $end = $end + $mapStep;
                                }
                                

                                $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                                $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);
                                $prefix = $server['username'] != 'root' ? "echo {$server['password']} | sudo -S " : '';

                                if($sshConnector->isConnected())
                                {
                                    $domainPass = true;
                                    $domainError = '';
                                    $IpsArray = array();

                                    for ($i = 0; $i < count($mapGlobal); $i++)
                                    {
                                        $map = $mapGlobal[$i];
                                        $mainDomain = $map['domain'];
                                        $subDomains = $map['subs'];
                                        $IPs = $map['ips'];
                                        $mainIP = $IPs[0];
                                        $domain = '';
                                        $ip = '';
                                        # Get Domain DKIM
                                        $dkim = array();
    
                                        if($mainDomain != '')
                                        {
                                            $sshConnector->cmd("{$prefix}mkdir /etc/opendkim/keys/$mainDomain;");
                                            $sshConnector->cmd("{$prefix}/usr/sbin/opendkim-genkey -D /etc/opendkim/keys/$mainDomain/ -d $mainDomain -s mail;");
                                            $sshConnector->cmd("{$prefix}chown -R opendkim:opendkim /etc/opendkim/keys/$mainDomain;");
                                            $sshConnector->cmd("{$prefix}chmod 640 /etc/opendkim/keys/$mainDomain/mail.private;");
                                            $sshConnector->cmd("{$prefix}chmod 644 /etc/opendkim/keys/$mainDomain/mail.txt;");
                                            $sshConnector->cmd("{$prefix}'mail._dkim.$mainDomain $mainDomain:mail:/etc/opendkim/keys/$mainDomain/mail.private' >> /etc/opendkim/KeyTable");
                                            $sshConnector->cmd("{$prefix}'*.$mainDomain' >> /etc/opendkim/TrustedHosts");
                                            $sshConnector->cmd("{$prefix}'*@$mainDomain mail._domainkey.$mainDomain' >> /etc/opendkim/SigningTable");

                                            $content = $sshConnector->cmd("{$prefix}cat /etc/opendkim/keys/$mainDomain/mail.txt",true);

                                            if(strlen($content) > 0)
                                            {
                                                $matches = array();

                                                if (preg_match('#\((([^()]+|(?R))*)\)#',$content,$matches)) 
                                                {
                                                    $dkim[0] = 'mail._domainkey';
                                                    $dkim[1] = str_replace(array("\n",'"'),array(" ",""),$matches[1]);
                                                }
                                            }
                                        }

                                        $dnsRecords = array();
                                        $indexsub = 10;
                                        $spf = "v=spf1 ";
                                        $spf .= "-all";
                                        
                                        // Save the main Somain ... 

                                        for ($j = 0; $j < count($IPs); $j++) {

                                            if($j == 0) //saving the main Domain .
                                            {
                                                $domain = $mainDomain ;
                                                $ip = trim($mainIP);
                                                $rdns = trim($domain);
                                                //
                                                $dnsRecords["EmailType"] = "MX";
                                            
                                                $dnsRecords["HostName1"] = "@";
                                                $dnsRecords["RecordType1"] = "A";
                                                $dnsRecords["Address1"] = $ip;
                                                $dnsRecords["TTL1"] = "60";

                                                $dnsRecords["HostName2"] = "www";
                                                $dnsRecords["RecordType2"] = "A";
                                                $dnsRecords["Address2"] = $ip;
                                                $dnsRecords["TTL2"] = "60";

                                                $dnsRecords["HostName3"] = "mail";
                                                $dnsRecords["RecordType3"] = "A";
                                                $dnsRecords["Address3"] = $ip;
                                                $dnsRecords["TTL3"] = "60";

                                                $dnsRecords["HostName4"] = "ftp";
                                                $dnsRecords["RecordType4"] = "A";
                                                $dnsRecords["Address4"] = $ip;
                                                $dnsRecords["TTL4"] = "60";

                                                $dnsRecords["HostName5"] = "ns1";
                                                $dnsRecords["RecordType5"] = "A";
                                                $dnsRecords["Address5"] = $ip;
                                                $dnsRecords["TTL5"] = "60";

                                                $dnsRecords["HostName6"] = $domain;
                                                $dnsRecords["RecordType6"] = "NS";
                                                $dnsRecords["Address6"] = "ns1.{$domain}";
                                                $dnsRecords["TTL6"] = "60";
                                                
                                                $dnsRecords["HostName7"] = "@";
                                                $dnsRecords["RecordType7"] = "MX";
                                                $dnsRecords["Address7"] = "mail.{$rdns}";
                                                $dnsRecords["MXPre7"] = "10";
                                                $dnsRecords["TTL7"] = "60";
                                                
                                                $dnsRecords["HostName8"] = $dkim[0];
                                                $dnsRecords["RecordType8"] = "TXT";
                                                $dnsRecords["Address8"] = $dkim[1];
                                                $dnsRecords["TTL8"] = "60";
                                                

                                                $dnsRecords["HostName9"] = "@";
                                                $dnsRecords["RecordType9"] = "TXT";
                                                $dnsRecords["Address9"] = "v=spf1 ip4:{$ip} -all";
                                                $dnsRecords["TTL9"] = "60";

                                            }
                                            else // Saving the sabs ...
                                            {
                                                $domain = $subDomains[$j - 1];
                                                $ip = trim($IPs[$j]);
                                                $rdns = trim($domain). '.' . trim($mainDomain);
                                                //
                                                # A record
                                                $dnsRecords["HostName$indexsub"] = $domain;
                                                $dnsRecords["RecordType$indexsub"] = "A";
                                                $dnsRecords["Address$indexsub"] = $ip;
                                                $dnsRecords["TTL$indexsub"] = "60";
                                                $indexsub++;

                                                # SPF record
                                                $dnsRecords["HostName$indexsub"] = $domain;
                                                $dnsRecords["RecordType$indexsub"] = "TXT";
                                                $dnsRecords["Address$indexsub"] = "v=spf1 ip4:{$ip} -all";
                                                $dnsRecords["TTL$indexsub"] = "60";
                                                $indexsub++; 
                                                    
                                            }
                                            
                                        }
                                        //\ma\mfw\output\PrintWriter::printValue($dnsRecords);
                                        $resDomain = $nameCheap->setDomainRecords(trim($mainDomain),$dnsRecords);
                                        
                                        if($resDomain != null && $resDomain == 1)
                                        {
                                            // Save The IPs on DB ...
                                            
                                            for ($i = 0; $i < count($mapGlobal); $i++)
                                            {
                                                $map = $mapGlobal[$i];
                                                $mainDomain = $map['domain'];
                                                $subDomains = $map['subs'];
                                                $IPs = $map['ips'];
                                                $mainIP = $IPs[0];
                                                $ip = '';
                                                
                                                // Saving Main IP ...
                                                
                                                $ipObject = new Ip();
                                                $ipObject->setStatus_id(1);
                                                $ipObject->setServer_id(intval($serverId));
                                                $ipObject->setValue($mainIP);
                                                $ipObject->setRdns($mainDomain);
                                                $ipObject->setCreated_by(intval(Arrays::getElement($user,'id')));
                                                $ipObject->setCreated_at(date("Y-m-d"));
                                                $ipObject->setLast_updated_by(intval(Arrays::getElement($user,'id')));
                                                $ipObject->setLast_updated_at(date("Y-m-d")); 
                                                $result = $ipObject->save(true);
                                                
                                                if($result != null && $result != '')
                                                {
                                                    // Saving Values for VMTAs ...
                                                    $IpsArray[] = array("id" => trim($result) , "rdns" => trim($mainDomain) , "value" => trim($mainIP));
                                                }
                                                else
                                                {
                                                    \ma\mfw\output\PrintWriter::printValue('Error on saving IPs on DB ...');
                                                }
                                                
                                                for ($j = 1; $j < count($IPs); $j++)
                                                {
                                                    $sub = $subDomains[$j - 1];
                                                    $ip = $IPs[$j];
                                                    $rdns = trim($sub). '.' . trim($mainDomain);
                                                    
                                                    $ipObject = new Ip();
                                                    $ipObject->setStatus_id(1);
                                                    $ipObject->setServer_id(intval($serverId));
                                                    $ipObject->setValue($ip);
                                                    $ipObject->setRdns($rdns);
                                                    $ipObject->setCreated_by(intval(Arrays::getElement($user,'id')));
                                                    $ipObject->setCreated_at(date("Y-m-d"));
                                                    $ipObject->setLast_updated_by(intval(Arrays::getElement($user,'id')));
                                                    $ipObject->setLast_updated_at(date("Y-m-d")); 
                                                    $result = $ipObject->save(true);
                                                    
                                                    if($result != null && $result != '')
                                                    {
                                                        // Saving Values for VMTAs ...
                                                        $IpsArray[] = array("id" => trim($result) , "rdns" => trim($rdns) , "value" => trim($ip));
                                                    }
                                                    else
                                                    {
                                                        \ma\mfw\output\PrintWriter::printValue('Error on saving IPs on DB ...');
                                                    }
                                                
                                                }
                                            }
                                                
                                            $assetsDirectory = Paths::getCurrentApplicationRealPath() . DS . DEFAULT_ASSETS_DIRECTORY;

                                                if(count($IpsArray))
                                                {
                                                    foreach ($IpsArray as $row)
                                                    {
                                                        //print_r("Creating VMTA for -> ".$row['value']);echo "</br>"; 
                                                        $vmta = $row['value'];
                                                        $resault = $resault . 'Creating VMTA for -> '.$vmta.' ... <br>';
                                                        
                                                        if($row['rdns'] != '')
                                                        {
                                                            $domain = count(explode('.',$row['rdns'])) > 2 ? Domains::getDomainFromURL($row['rdns']) : $row['rdns'];
                                                            
                                                            # create a vmta 
                                                            $vmtaObject = new Vmta();
                                                            $vmtaObject->setStatus_id(1);
                                                            $vmtaObject->setServer_id($serverId);
                                                            $vmtaObject->setIp_id($row['id']);
                                                            $vmtaObject->setName('vmta_large_' . $row['id']);
                                                            $vmtaObject->setType('Default');
                                                            $vmtaObject->setIp_value($row['value']);
                                                            $vmtaObject->setDomain($row['rdns']);
                                                            $vmtaObject->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                                            $vmtaObject->setCreated_at(date("Y-m-d"));
                                                            $vmtaObject->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                                            $vmtaObject->setLast_updated_at(date("Y-m-d"));
                                                            $vmtaObject->save();
                                                            
                                                            $vmta = str_replace(array('$P{VMTA}','$P{IP}','$P{DOMAIN}'),array('vmta_large_' . $row['id'],$row['value'],$row['rdns']),file_get_contents($assetsDirectory . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'installation' . DS . 'vmta.tpl')) . PHP_EOL;
                                                            
                                                            $check = preg_replace( "/\r|\n/","", trim($sshConnector->cmd($prefix . '[ -f /etc/opendkim/keys/'.$domain.'/mail.private ] && echo "Found" || echo "Not found"',true)));

                                                            if($check == 'Found')
                                                            {
                                                                $dkim = "domain-key mail,$domain,/etc/pmta/keys/$domain/mail.private";
                                                                $vmta = str_replace('$P{DKIM}',$dkim, $vmta);

                                                                if(count(explode('.',$domain)) == 2)
                                                                {
                                                                    $sshConnector->cmd("{$prefix}mkdir -p /etc/pmta/keys/$domain/;");
                                                                    $sshConnector->cmd("{$prefix}cp /etc/opendkim/keys/$domain/mail.private /etc/pmta/keys/$domain/mail.private;");
                                                                }
                                                            }
                                                            else
                                                            {
                                                                $vmta = str_replace('$P{DKIM}','', $vmta);
                                                            }

                                                            
                                                            $sshConnector->scp('send',array('/etc/pmta/vmtas/' . 'vmta_large_' . $row['id']  . '.conf'),$vmta);
                                                        }
                                                    }
                                                    unset($IpsArray);
                                                    $IpsArray = Array(); 

                                                    $sshConnector->cmd("chown -R pmta:pmta /etc/pmta/;",true);
                                                    $sshConnector->cmd("service pmta reload",true);

                                                    //\ma\mfw\output\PrintWriter::printValue('Installed ...');
                                                }
                                                else
                                                {
                                                    \ma\mfw\output\PrintWriter::printValue('No VMTAs Found  ... <br>');
                                                }
                                        
                                            }
                                            else
                                            {
                                                \ma\mfw\output\PrintWriter::printValue('Errore while ipdaing the domain on namecheap  ... <br>');
                                            }
                                    }
                                    if($domainPass)
                                    {
                                        $resault = $resault . 'All Domains Has been configured succesfuly   ... <br>'; 
                                    }
                                    else
                                    {
                                        $resault = $resault . 'The domain '. $domainError .' Not installed please try again  ... <br>'; 
                                    }

                                }
                                else
                                {
                                    $resault = 'Error while connect to the server please check ...';
                                }
                            }
                           
                        }
                        else
                        {
                            $resault = 'The server That you trying to install not exist or is not a large server ...';
                        }
                    }
                    
                    Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers.html');
                }
                
            }
            else
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_servers_types',true);
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_servers_large',true);
                # get all servers from the database

                $serverProviders = ServerProvider::all(true,array('status_id = ? ',1),array('id','name'));
                $servers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id','name','provider_id'));

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

                $this->getPageView()->set('servers',$servers);
                $this->getPageView()->set('serverProviders',$serverProviders);
            }
            
        }
        
        /**
         * @name ips
         * @description the ips action
         * @before init
         * @after setMenu,closeConnection
         */
        public function ips() 
        {
            $arguments = func_get_args(); 
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'lists';

            if(isset($page))
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_servers_types',true);
                
                switch ($page) 
                {
                    case 'add' :
                    {
                            # set the template for the page view 
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'servers' . DS . 'ips' . DS . 'add' . '.' . $this->getDefaultExtension());

                            # set the menu item to active 
                            $this->getMasterView()->set('menu_admin_servers_add',true);

                            $serverProviders = ServerProvider::all(true,array('status_id = ? ',1),array('id','name'));
                            $servers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id','name','provider_id'));

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



                            $this->getPageView()->set('servers',$servers);
                            $this->getPageView()->set('serverProviders',$serverProviders);
                        
                        
                        break;
                    }
                    case 'save' :
                    {
                        # retrieves the data from post
                        $ips = explode(PHP_EOL,Request::getParameterFromPOST('ips'));
                        $ipsProcessType = Request::getParameterFromPOST('ips-process-type');
                        
                        # initializing assets directory
                        $assetsDirectory = Paths::getCurrentApplicationRealPath() . DS . DEFAULT_ASSETS_DIRECTORY;
                        
                        # messages 
                        $flag = 'success';
                        $message = 'Operation Finished Successfully !';
                        
                        
                        if(count($ips))
                        {
                            $serverId = Request::getParameterFromPOST('server-id');
                            $server = Server::first(true,array('id = ?',$serverId),array('id','name','main_ip','username','password','ssh_port'));
                            
                            if($server != null  && count($server))
                            {
                                $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
                                $sshConnector = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);

                                if($sshConnector->isConnected())
                                {
                                    \ma\mfw\output\PrintWriter::printValue($server,false);
                                    $ipsConfigured = explode(PHP_EOL,trim($sshConnector->cmd("ifconfig  | grep 'inet addr:'| grep -v '127.0.0.1' | cut -d: -f2 | awk '{ print $1}'",true),PHP_EOL));
                                    $devices = explode(PHP_EOL,trim($sshConnector->cmd("ifconfig | egrep '^eth' | cut -f 1-2 -d ':' | cut -f 1 -d ' '",true),PHP_EOL));
                                    $deviceType = "";
                                    $deviceLastNum = 0;

                                    if(count($devices))
                                    {
                                        foreach ($devices as $device) 
                                        {
                                            $device = trim($device);
                                            
                                            if(Strings::startsWith($device,"eth"))
                                            { 
                                                # get ips template
                                                $ipTemplate = file_get_contents($assetsDirectory . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'installation' . DS . 'ip.tpl');
                                                
                                                if(Strings::indexOf($device,":") > -1) 
                                                {
                                                    $deviceType = Arrays::getElement(explode(":",$device),0) . ":";
                                                    $tmpVal = intval(Arrays::getElement(explode(":",$device),1));
                                                    $deviceLastNum = $tmpVal > $deviceLastNum ? $tmpVal : $deviceLastNum;
                                                }
                                                else 
                                                {
                                                    $deviceType = "eth0:";
                                                    $deviceLastNum = -1;
                                                }
                                                

                                            }
                                            else if(Strings::startsWith($device,"venet"))
                                            {
                                                # get ips template
                                                $ipTemplate = file_get_contents($assetsDirectory . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'installation' . DS . 'ip.tpl');

                                                if(Strings::indexOf($device,":") > -1)
                                                {
                                                    $deviceType = Arrays::getElement(explode(":",$device),0) . ":";
                                                    $tmpVal = intval(Arrays::getElement(explode(":",$device),1));
                                                    $deviceLastNum = $tmpVal > $deviceLastNum ? $tmpVal : $deviceLastNum;
                                                }
                                                else
                                                {
                                                    $deviceType = "venet:";
                                                    $deviceLastNum = str_replace("venet","", $device);
                                                }
                                            }
                                            else if(Strings::startsWith($device,"em"))
                                            {
                                                # get ips template
                                                $ipTemplate = file_get_contents($assetsDirectory . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'installation' . DS . 'ip.tpl');

                                                if(Strings::indexOf($device,":") > -1)
                                                {
                                                    $deviceType = Arrays::getElement(explode(":",$device),0) . ":";
                                                    $tmpVal = intval(Arrays::getElement(explode(":",$device),1));
                                                    $deviceLastNum = $tmpVal > $deviceLastNum ? $tmpVal : $deviceLastNum;
                                                }
                                                else
                                                {
                                                    $deviceType = "em1:";
                                                    $deviceLastNum = -1;
                                                }
                                            }
                                        }
                                    } 
                                    
                                    # individual case
                                    if($ipsProcessType == 'indiv')
                                    {
                                        foreach ($ips as $line) 
                                        {
                                            if (count(explode(";",$line)) == 3) 
                                            {
                                                $ip = trim(trim(Arrays::getElement(explode(";",$line),0),PHP_EOL));
                                                $gateway = trim(trim(Arrays::getElement(explode(";",$line),1),PHP_EOL));
                                                $netmask = trim(trim(Arrays::getElement(explode(";",$line),2),PHP_EOL));

                                                if(filter_var($ip,FILTER_VALIDATE_IP) && filter_var($gateway,FILTER_VALIDATE_IP) && filter_var($netmask,FILTER_VALIDATE_IP))
                                                {
                                                    if(!in_array($ip,$ipsConfigured))
                                                    {
                                                        $currentDevice = $deviceType . ($deviceLastNum + 1);
                                                        $staticIp = str_replace(array('$P{DEVICE}','$P{IP}','$P{GATEWAY}','$P{MASK}'), array($currentDevice,$ip,$gateway,$netmask), $ipTemplate);      
                                                        # create static ip file 
                                                        $sshConnector->scp('send',array('/etc/sysconfig/network-scripts/ifcfg-' . $currentDevice),$staticIp);

                                                        # increase device num
                                                        $deviceLastNum++;
                                                    } 
                                                }
                                            }
                                        }

                                        # restarting the network 
                                        $sshConnector->cmd("service network restart",true);
                                    }
                                    elseif($ipsProcessType == 'range')
                                    {
                                        foreach ($ips as $line) 
                                        {
                                            
                                            if (count(explode(";",$line)) == 4) 
                                            {
                                                
                                                $firstIp = trim(trim(Arrays::getElement(explode(";",$line),0),PHP_EOL));
                                                $secondIp = trim(trim(Arrays::getElement(explode(";",$line),1),PHP_EOL));
                                                $gateway = trim(trim(Arrays::getElement(explode(";",$line),2),PHP_EOL));
                                                $netmask = trim(trim(Arrays::getElement(explode(";",$line),3),PHP_EOL));
                             
                                                if(filter_var($firstIp,FILTER_VALIDATE_IP) && filter_var($secondIp,FILTER_VALIDATE_IP) && filter_var($gateway,FILTER_VALIDATE_IP) && filter_var($netmask,FILTER_VALIDATE_IP))
                                                {  
                                                    $firstIplastNum = intval(Arrays::getElement(explode(".",$firstIp),3));
                                                    $secondIplastNum = intval(Arrays::getElement(explode(".",$secondIp),3));

                                                    for ($index = $firstIplastNum; $index <= $secondIplastNum; $index++) 
                                                    {
                                                        $ip = intval(Arrays::getElement(explode(".",$firstIp),0)) . "." . 
                                                              intval(Arrays::getElement(explode(".",$firstIp),1)) . "." .
                                                              intval(Arrays::getElement(explode(".",$firstIp),2)) . "." .
                                                              $index;

                                                        if(!in_array($ip,$ipsConfigured))
                                                        {
                                                            $currentDevice = $deviceType . ($deviceLastNum + 1);
                                                            $staticIp = str_replace(array('$P{DEVICE}','$P{IP}','$P{GATEWAY}','$P{MASK}'), array($currentDevice,$ip,$gateway,$netmask), $ipTemplate);      

                                                            # create static ip file 
                                                            $sshConnector->scp('send',array('/etc/sysconfig/network-scripts/ifcfg-' . $currentDevice),$staticIp);
                                                            
                                                            # increase device num
                                                            $deviceLastNum++;
                                                        } 
                                                    }
                                                }
                                            }
                                        }

                                        # restarting the network 
                                        $sshConnector->cmd("service network restart",true);
                                    }

                                    $sshConnector->disconnect();
                                }  
                            }
                        }
                        
                        Session::set('proccess_message_flag',$flag);
                        Session::set('proccess_message',$message);
                        # redirect to show list 
                       // Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'edit' . RDS . $serverId . '.html');
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers.html');
                    }    
                }
            }
        }

        /**
         * @name domains
         * @description the domains action
         * @before init
         * @after setMenu,closeConnection
         */
        public function domains() 
        {
            $arguments = func_get_args(); 
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'lists';

            if(isset($page))
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_servers_types',true);
                
                switch ($page) 
                {
                    case 'add' :
                    {
                        $id = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;

                        if(isset($id) && is_numeric($id))
                        {
                            # set the template for the page view 
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'servers' . DS . 'domains' . DS . 'add' . '.' . $this->getDefaultExtension());

                            # set the menu item to active 
                            $this->getMasterView()->set('menu_admin_servers_add',true);
           
                            # retrieve the server by id
                            $server = Server::first(true,array('id = ?',$id));
                            
                            # retrieve the ips
                            $ips = Ip::all(true,array('server_id = ?',$id),array('id','value'),'id','ASC');
                            
                            # get status list 
                            $status = Status::all(true,array(),array('id','name'),'id','ASC');

                            # set the list into the template data system 
                            $this->getPageView()->set('ips',$ips);
                            $this->getPageView()->set('status',$status);
                            $this->getPageView()->set('server',$server);
                        }
                        
                        break;
                    }
                    case 'edit' :
                    {
                        $id = isset($arguments) && count($arguments) > 2 ? $arguments[2] : null;
                        $serverId = isset($arguments) && count($arguments) > 2 ? $arguments[1] : null;

                        if(isset($id) && is_numeric($id))
                        {
                            # set the template for the page view 
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'servers' . DS . 'domains' . DS . 'edit' . '.' . $this->getDefaultExtension());

                            # set the menu item to active 
                            $this->getMasterView()->set('menu_admin_servers_add',true);

                            # retrieve the domain by id
                            $domain = Domain::first(true,array('id = ?',$id));
                
                            # retrieve the server by id
                            $server = Server::first(true,array('id = ?',$serverId));
                            
                            # retrieve the ips
                            $ips = Ip::all(true,array('server_id = ?',$serverId),array('id','value'),'id','ASC');
                            
                            # get status list 
                            $status = Status::all(true,array(),array('id','name'),'id','ASC');

                            # set the list into the template data system 
                            $this->getPageView()->set('domain',$domain);
                            $this->getPageView()->set('status',$status);
                            $this->getPageView()->set('ips',$ips);
                            $this->getPageView()->set('server',$server);
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
                            # delete the domain
                            $domain = new Domain(array("id" => $id));
                            $domain->delete();
                            $message = "Domain deleted!";
                            $messageFlag = 'success';
                        }

                        # stores the message in the session 
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);

                        # redirect to show list 
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'lists.html');
                        
                        break;
                    }
                    case 'save' :
                    {
                        # get the connected user
                        $user = Session::get('bluemail_connected_user'); 
                        
                        # retrieves the data from post
                        $id = Request::getParameterFromPOST('domain-id');
                        $ipId = Request::getParameterFromPOST('ip-id');
                        $domainName = Request::getParameterFromPOST('domain-name');
                        $status = Request::getParameterFromPOST('status-id');
                        $serverId = Request::getParameterFromPOST('server-id');

                        if(isset($domainName))
                        {
                            $message = "Something went wrong !";
                            $messageFlag = 'error';

                            # check if the domain value is a valid domain 
                            if(!Domains::isValidDomain($domainName))
                            {
                                $message = "Error While Saving Your Record : Please Insert a Valid Domain !";
                                $messageFlag = 'error';
                            }
                            else
                            {
                                if($id != NULL && is_numeric($id))
                                {
                                    $existed = false;
                                    
                                    # update case
                                    $domain = new Domain(array("id" => $id));
                                    
                                    # checking if the domain is already existed
                                    if(trim($domainName) != trim($domain->getValue()))
                                    {
                                        $checkDomain = Domain::first(true,array("value = ? ",$domainName),array('id'));
                                        
                                        if(isset($checkDomain) && count($checkDomain) && isset($checkDomain['id']))
                                        {
                                            $message = "Error While Saving Your Record : This Domain is Already in The Database !";
                                            $messageFlag = 'error';
                                            $existed = true;
                                        }
                                    }
                                    else
                                    {
                                        $domain->setStatus_id(intval($status));
                                        $domain->setIp_id($ipId);
                                        $domain->setValue($domainName);
                                        $domain->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                        $domain->setLast_updated_at(date("Y-m-d"));

                                        $result = $domain->save(); 

                                        if($result > -1)
                                        {
                                            $message = "Record updated succesfully !";
                                            $messageFlag = 'success';
                                        }
                                    } 
                                }
                                else
                                {
                                    # checking if the domain is already existed
                                    $domain = Domain::first(true,array("value = ? ",$domainName),array('id'));
                                    
                                    if(isset($domain) && count($domain) && isset($domain['id']))
                                    {
                                        $message = "Error While Saving Your Record : This Domain is Already in The Database !";
                                        $messageFlag = 'error';
                                    }
                                    else
                                    {
                                        # insert case
                                        $domain = new Domain();
                                        $domain->setStatus_id(intval($status));
                                        $domain->setIp_id($ipId);
                                        $domain->setValue($domainName);
                                        $domain->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                        $domain->setCreated_at(date("Y-m-d"));
                                        $domain->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                        $domain->setLast_updated_at(date("Y-m-d"));

                                        $result = $domain->save(); 

                                        if($result > -1)
                                        {
                                            $message = "Record stored succesfully !";
                                            $messageFlag = 'success';
                                        }
                                    }
                               }
                            }

                            # stores the message in the session 
                            Session::set('proccess_message_flag',$messageFlag);
                            Session::set('proccess_message',$message);
                        }

                        # redirect to show list 
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'edit' . RDS . $serverId . '.html');
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers.html');
                    }    
                }
            }
        }
        
        /**
         * @name providers
         * @description the providers action
         * @before init
         * @after setMenu,closeConnection
         */
        public function providers() 
        {
            $arguments = func_get_args(); 
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'lists';

            if(isset($page))
            {
                # set the menu item to active 
                $this->getMasterView()->set('menu_admin_server_providers',true);
                
                switch ($page) 
                {
                    case 'lists' :
                    {
                        # set the template for the page view 
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'servers' . DS . 'providers' . DS . 'lists' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active 
                        $this->getMasterView()->set('menu_admin_server_providers_list',true);

                        # get the data from the database
                        $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT p.id AS id,"
                                                        . " p.name AS provider_name,"
                                                        . " s.name AS status,"
                                                        . " p.website AS provider_website,"
                                                        . " p.username AS provider_username,"
                                                        . " p.password AS provider_password," 
                                                        . " uc.username AS created_by,"
                                                        . " p.created_at AS created_date,"
                                                        . " up.username AS last_updated_by,"
                                                        . " p.last_updated_at AS last_updated_at"
                                                        . " FROM admin.server_providers p"
                                                        . " LEFT JOIN admin.users uc ON uc.id = p.created_by"
                                                        . " LEFT JOIN admin.status s ON s.id = p.status_id"
                                                        . " LEFT JOIN admin.users up ON up.id = p.last_updated_by"
                                                        . " ORDER BY p.id", true);

                        # get all the columns names 
                        $columns = array('id','provider_name','status','provider_website','provider_username','provider_password','created_by','created_date','last_updated_by','last_updated_at');

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
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'servers' . DS . 'providers' . DS . 'add' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active 
                        $this->getMasterView()->set('menu_admin_server_providers_add',true);
                        
                        # get status list 
                        $status = Status::all(true,array(),array('id','name'),'id','ASC');
                        
                        # set the list into the template data system 
                        $this->getPageView()->set('status',$status);
                        break;
                    }
                    case 'edit' :
                    {
                        $id = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;

                        if(isset($id) && is_numeric($id))
                        {
                            # retrieve the server by id
                            $provider = ServerProvider::first(true,array('id = ?',$id));
                            
                            # get status list 
                            $status = Status::all(true,array(),array('id','name'),'id','ASC');

                            # set the template for the page view 
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'servers' . DS . 'providers' . DS . 'edit' . '.' . $this->getDefaultExtension());
                            
                            # set the menu item to active 
                            $this->getMasterView()->set('menu_admin_server_providers_add',true);

                            # set the data to the template
                            $this->getPageView()->set('provider',$provider);

                            # set the list into the template data system 
                            $this->getPageView()->set('status',$status);
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
                            $server = new ServerProvider(array("id" => $id));
                            $server->delete();
                            $message = "Provider deleted!";
                            $messageFlag = 'success';
                        }

                        # stores the message in the session 
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);

                        # redirect to show list 
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'providers' . RDS . 'lists.html');
                        
                        break;
                    }
                    case 'save' :
                    {
                        # get the connected user
                        $user = Session::get('bluemail_connected_user'); 

                        # retrieves the data from post
                        $id = Request::getParameterFromPOST('provider-id');
                        $providerStatus = Request::getParameterFromPOST('status-id');
                        $providerName = Request::getParameterFromPOST('provider-name');
                        $providerWebsite = Request::getParameterFromPOST('provider-website');
                        $providerUsername = Request::getParameterFromPOST('provider-username');
                        $providerPassword = Request::getParameterFromPOST('provider-password');
                        
                        if(isset($providerName))
                        {
                            $message = "Something went wrong !";
                            $messageFlag = 'error';

                            if($id != NULL && is_numeric($id))
                            {
                                # update case
                                $provider = new ServerProvider(array("id" => $id));
                                $provider->setStatus_id(intval($providerStatus));
                                $provider->setName($providerName);
                                $provider->setWebsite($providerWebsite);
                                $provider->setUsername($providerUsername);
                                $provider->setPassword($providerPassword);
                                $provider->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                $provider->setLast_updated_at(date("Y-m-d"));

                                $result = $provider->save(); 

                                if($result > -1)
                                {
                                    $message = "Record updated succesfully !";
                                    $messageFlag = 'success';
                                }
                            }
                            else
                            {
                                # insert case
                                $provider = new ServerProvider();
                                $provider->setStatus_id(intval($providerStatus));
                                $provider->setName($providerName);
                                $provider->setWebsite($providerWebsite);
                                $provider->setUsername($providerUsername);
                                $provider->setPassword($providerPassword);
                                $provider->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                $provider->setCreated_at(date("Y-m-d"));
                                $provider->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                $provider->setLast_updated_at(date("Y-m-d"));

                                $result = $provider->save(); 

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
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'providers' . RDS . 'lists.html');
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'servers' . RDS . 'providers' . RDS . 'lists.html');
                    }    
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
            $this->getMasterView()->set('menu_admin_servers_management',true);
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