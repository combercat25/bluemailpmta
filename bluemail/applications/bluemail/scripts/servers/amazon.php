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
use ma\mfw\types\Arrays as Arrays;
use ma\applications\bluemail\models\admin\Ipa as Ipa;
use ma\applications\bluemail\models\admin\Ip as Ip;
use ma\mfw\files\Paths as Paths;
use ma\mfw\ssh2\SSH as SSH;
use ma\mfw\ssh2\SSHPasswordAuthentication as SSHPasswordAuthentication;
use ma\mfw\types\Strings as Strings;
use ma\mfw\os\System as System;
use ma\applications\bluemail\models\admin\Server as Server;
use ma\mfw\encryption\Crypto as Crypto;
use ma\mfw\api\NameCheap as NameCheap; 
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
    $interface = $parameters['interface'];

    # empty the log file 
    System::executeCommand("> " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $serverId . '.log');
    System::executeCommand("echo 'in progress' > " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $serverId . '.process');
    
    //$prefixes = array('shop','blog','ads','club','sales','portal','store','offers','news','app','offers','report','games','email','webmail','jobs','app','interact','goodies','leads');
    
    for ($index1 = 0; $index1 < 255; $index1++)
    {
        $prefixes[] = Strings::generateRandomText(rand(3,5),true,false,false,false);
    }
    
    $nameCheap = new NameCheap();
    
    # retrieve the servers and ips
    $server = Server::first(true,array('id = ?',$serverId));
    $ips = Ip::all(true,array('server_id = ?',$serverId));
    $ipAmazon = Ipa::all(true,array('server_id = ?',$serverId));
    

    showProgressMessage('Installing server : ' . $server['name'] . ' ...');
    
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
    
    //$ssh = new SSH($server['main_ip'],new SSHPasswordAuthentication($server['username'],$server['password']),$server['ssh_port']);

    if($ssh->isConnected())
    {
        $prefix = $server['username'] != 'root' ? "echo {$server['password']} | sudo -S " : '';
        
       
        # print progress message 
        showProgressMessage('Server connected !');
        
         # print progress message 
        showProgressMessage('Cleaning Old IPs  !');
        
        foreach ($selected_ips as $key => $value)
        {
            showProgressMessage('Cleaning IP -> ' . $value); 
            $ipOBJ = Ipa::first(true,array('value = ?', trim($value)));
            $allocID = $ipOBJ['allocation_id'];
            $assocID = $ipOBJ['association_id'];
            showProgressMessage("IP with alloc -> " . $allocID . " and assoc -> " .$assocID . " should release ");
            //ma\mfw\output\PrintWriter::printValue("IP with alloc -> " . $allocID . " and assoc -> " .$assocID . " should release " , false);
            $removed =  $ssh->cmd("aws ec2 disassociate-address --association-id $assocID;aws ec2 release-address --allocation-id $allocID" , true);
        }
//                
//        if(count($ipAmazon))
//        {
//            // Remove Old ones ...
//            foreach ($ipAmazon as $ip)
//            {
//                $allocID = $ip['allocation_id'];
//                $assocID = $ip['association_id'];
//                //ma\mfw\output\PrintWriter::printValue("IP with alloc -> " . $allocID . " and assoc -> " .$assocID . " should release " , false);
//                $removed =  $ssh->cmd("aws ec2 disassociate-address --association-id $assocID;aws ec2 release-address --allocation-id $allocID" , true);
//               // showProgressMessage($removed);
//            }
//        }
//        
         # print progress message 
        showProgressMessage(' Old IPs RELEASED !');
        
        foreach ($selected_ips as $key => $value)
        {
            
            showProgressMessage('Changing The ip ->  ' . $value); 
            
            showProgressMessage('Getting new Elastic IP ... '); 
            $resault = $ssh->cmd('aws ec2 allocate-address --domain "vpc"' , true);
            //showProgressMessage($resault);
            
            if($resault != "") 
            {
                
                // Check if the IPs already installed and try to remove them first
                
                $res=json_decode($resault);
                $elasticIP=$res->PublicIp;
                $allocationID=trim($res->AllocationId);

                showProgressMessage('New ElasticIP  ---  ' .$elasticIP); 
                //showProgressMessage($elasticIP);
                //showProgressMessage('AllocationID  ---  ' .$allocationID);

                showProgressMessage('changing IP  ' .$value  .' ...');
                $ip = trim($value);
                //showProgressMessage( "aws ec2 associate-address --allocation-id '$allocationID' --network-interface-id '$interface' --no-allow-reassociation --private-ip-address '$ip'" );
                $changeEd =  $ssh->cmd("aws ec2 associate-address --allocation-id '$allocationID' --network-interface-id '$interface' --no-allow-reassociation --private-ip-address '$ip'" , true);
                //showProgressMessage($changeEd);
                
                $res=json_decode($changeEd);

                $associationId=trim($res->AssociationId);
                
                $ipOBJ = Ipa::first(true,array('value = ?',$ip));
                showProgressMessage($ipOBJ);
                
                if(!count($ipOBJ))
                {
                    $ipOBJ = new Ipa();
                    $ipOBJ->setStatus_id(1);
                    $ipOBJ->setServer_id(intval($serverId));
                    $ipOBJ->setValue($ip);
                    $ipOBJ->setRdns('');

                    $ipOBJ->setAssociation_id($associationId);
                    $ipOBJ->setAllocation_id($allocationID);

                    $ipOBJ->setCreated_by(intval(Arrays::getElement($user,'id')));
                    $ipOBJ->setCreated_at(date("Y-m-d"));
                    $ipOBJ->setLast_updated_by(intval(Arrays::getElement($user,'id')));
                    $ipOBJ->setLast_updated_at(date("Y-m-d")); 
                    $result = $ipOBJ->save(true);
                }
                else
                {
                    $ipOBJ = new Ipa(array('id' => $ipOBJ['id'])); 
                    $ipOBJ->setAssociation_id($associationId);
                    $ipOBJ->setAllocation_id($allocationID);
                    
                    $ipOBJ->setLast_updated_by(intval(Arrays::getElement($user,'id')));
                    $ipOBJ->setLast_updated_at(date("Y-m-d")); 
                    $result = $ipOBJ->save(true);
                }
                

                //$ipId = ($ip['id'] > 0) ? $ip['id'] : $result;

                showProgressMessage('THE IP ->    ' .$value .' HAS BEEN CHANGED TO  ---> ' . $elasticIP);
            }
            else
            {
                showProgressMessage('Can not change the IP ->  ' . $value . ' Error on the server');
            }

        }
        
        # initializing assets directory
        $assetsDirectory = Paths::getCurrentApplicationRealPath() . DS . DEFAULT_ASSETS_DIRECTORY;
        
        # disconnect from the server 
        $ssh->disconnect();
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