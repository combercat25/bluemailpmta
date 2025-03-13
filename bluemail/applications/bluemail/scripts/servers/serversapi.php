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
use ma\mfw\files\Paths as Paths;
use ma\mfw\api\Api as Api;
use ma\mfw\os\System as System;
use ma\applications\bluemail\models\admin\ServerApis as ServerApis;
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
    $accountId = $parameters['accountId'];
    $count = $parameters['count'];
    
    $app = Application::getCurrent()->getSetting('init');
    
    $regions = array('ca-central','us-central','us-west','us-southeast','us-east','eu-west','ap-south','eu-central','ap-northeast');
    $image = "linode/centos7";
    $type = "g6-standard-4";
    $rootPass = "010203@El";
    $public_key = trim(file_get_contents($app->public_key));


    # empty the log file 
    System::executeCommand("> " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $accountId . '.log');
    System::executeCommand("echo 'in progress' > " . ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY  . DS . 'logs' . DS . 'installations' . DS . 'server_' . $accountId . '.process');
    
    $account = ServerApis::first(true,array('id = ?',$accountId));
    
    showProgressMessage('Getting '.$count.' servers from : ' . $account['account_name'] . ' ...');
    showProgressMessage('Getting servers accoount details ...');
    $linode = array(
        "api_type" => $account['api_provider'],
        "api_url" => $account['api_url'],
        "api_key" => $account['api_key']
        );
    
    $api = Api::getAPIClass($linode);
    
    for ($index = 1; $index <= $count; $index++)
    {
        showProgressMessage('Creating server -> ' . $index);
        $res = $api->CreateServer($type, $regions[6], $image, $rootPass, "cento-".$index , $public_key); 
        showProgressMessage('Next');
    }
    
    
    showProgressMessage('Server installation for ' . $api . ' completed !');

    
    # print progress message
    showProgressMessage('Connecting to server via SSH ...');
   

    # print progress message
    showProgressMessage('Closing connection ...');

    # print progress message
    showProgressMessage('Server installation for ' . $count . ' completed !');

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