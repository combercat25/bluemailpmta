<?php
/**
 * @framework       Miami Framework
 * @version         1.1
 * @author          Miami Team
 * @copyright       Copyright (c) 2017 - 2018.	
 * @license		
 * @link	
 */ 
use ma\mfw\database\Database as Database;
use ma\mfw\application\Application as Application;
use ma\applications\bluemail\models\admin\Ip as Ip;
use ma\mfw\files\Paths as Paths;
use ma\mfw\ssh2\SSH as SSH;
use ma\mfw\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
use ma\mfw\types\Strings as Strings;
use ma\mfw\os\System as System;
use ma\applications\bluemail\models\admin\Server as Server;
use ma\mfw\encryption\Crypto as Crypto;
/**
 * @name            install.php 
 * @description     a native script that installs a production server
 * @package         .
 * @category        Native Script
 * @author          Miami Team			
 */

# to ensure scripts are not called from outside of the framework 
define('IS_MFW',true);  

# get the application name
$appPrefix = trim(basename(dirname(dirname(__DIR__))));

# require the main configuration of the framework 
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/configs/init.conf.php';

# require request init configurations ( application init and database , cache ... )
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/configs/request.init.conf.php';

# require the helper
require_once Paths::getCurrentApplicationRealPath() . DS . 'scripts' . DS . 'servers' . DS . 'helper.php';

# check if the parameters has been sent 
if(count($argv) == 2)
{
    # connect to the default database 
    Database::secureConnect();
    
    # extract all the parameters 
    $parameters = Crypto::AESDecrypt($argv[1]);
    
    # get the main values
    $user = $parameters['user'];
    $serverId = $parameters['server-id'];
    $selected_ips = $parameters['ips'];
    $username = $parameters['username'];
    $password = $parameters['password'];
    $port = $parameters['port'];
    

    # empty the log file 
    System::executeCommand("> " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $serverId . '.log');
    System::executeCommand("echo 'in progress' > " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $serverId . '.process');
    
    //$prefixes = array('shop','blog','ads','club','sales','portal','store','offers','news','app','offers','report','games','email','webmail','jobs','app','interact','goodies','leads');
    
    for ($index1 = 0; $index1 < 255; $index1++)
    {
        $prefixes[] = Strings::generateRandomText(rand(3,5),true,false,false,false);
    }
    
    
    # retrieve the servers and ips
    $server = Server::first(true,array('id = ?',$serverId));
    $ips = Ip::all(true,array('server_id = ?',$serverId));
    

        showProgressMessage('Configuring  server proxy : ' . $server['name'] . ' ...');
        # print progress message
        showProgressMessage('Connecting to server via SSH ...');

        if($server['server_auth'] != '' && $server['server_auth'] == 1)
        {
            $app = Application::getCurrent()->getSetting('init');
            $public_key = $app->public_key;
            $private_key = $app->private_key;
            $sshAuthenticator = new \ma\mfw\ssh2\SSHKeyAuthentication($server['username'], $public_key, $private_key);
            $ssh = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);
        }
        else
        {
            $sshAuthenticator = new SSHPasswordAuthentication($server['username'],$server['password']);
            $ssh = new SSH($server['main_ip'],$sshAuthenticator,$server['ssh_port']);
        }


        if($ssh->isConnected())
        {
            $prefix = $server['username'] != 'root' ? "echo {$server['password']} | sudo -S " : '';
            # initializing assets directory
             $assetsDirectory = Paths::getCurrentApplicationRealPath() . DS . DEFAULT_ASSETS_DIRECTORY;
            
            showProgressMessage('Server connected  !');
            
            showProgressMessage('Removing old squid   !');
            
            $ssh->cmd("sudo yum -y remove squid" , true);
            
            showProgressMessage('Start installing squid ...');
            
            $$squidIPsSuid = "";
            
            foreach ($selected_ips as $key => $value)
            {
                showProgressMessage('Configuring the IP ip ->  ' . $value);
                $IPsSuid .= "acl myip_".$value." myip ".$value." ".PHP_EOL." tcp_outgoing_address ".$value." myip_".$value.PHP_EOL;
            }
             
            
           
            
            $squid = str_replace(array('$P{IPS}' , '$P{PORT}'),array($IPsSuid , $port),file_get_contents($assetsDirectory . DS . DEFAULT_TEMPLATES_DIRECTORY . DS . 'installation' . DS . 'squid.tpl')) . PHP_EOL;

            
            //showProgressMessage($IPsSuid);
    
            $ssh->cmd("sudo yum -y install squid" , true);
             $ssh->cmd("mv /etc/squid/squid.conf /etc/squid/squid.conf.bkp");
            $ssh->cmd("rm -rf /etc/squid/squid.conf");
            $ssh->scp('send',array('/etc/squid/squid.conf'),$squid);
            $ssh->cmd("yum -y install httpd-tools",true) . PHP_EOL;
            $ssh->cmd("touch /etc/squid/passwd && chown squid /etc/squid/passwd",true) . PHP_EOL;
            $ssh->cmd("htpasswd -b -c /etc/squid/passwd ".$username." ".$password." ",true) . PHP_EOL;
            $ssh->cmd("sudo service squid restart",true) ;
                      
            showProgressMessage(' squid installed ...');
            
        }
        else
        {
            showProgressMessage('Error while  connecting to server  !');
        }
    
    
        

    

    # print progress message
    showProgressMessage('Closing connection ...');

    # print progress message
    showProgressMessage('Server installation for ' . $server['name'] . ' completed !');

    # set proccess to 1 means completed
    System::executeCommand("echo 'completed' > " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $serverId . '.process');

    # disconnect from all databases 
    Database::secureDisconnect();
}
else 
{
    # print progress message
    showProgressMessage('Please check the parameters that has been sent to this script !');
    
    # set proccess to 1 means completed
    System::executeCommand("echo 'completed' > " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $serverId . '.process');
}