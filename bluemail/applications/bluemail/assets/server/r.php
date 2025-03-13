<?php
/**
 * @framework       Miami Framework
 * @version         1.1
 * @author          Miami Team
 * @copyright       Copyright (c) 2017 - 2018.	
 * @license		
 * @link	
 */ 
require_once __DIR__ . '/help.php';

# check if there are some info passed 
if(count($_GET))
{
    $url = '$P{API_URL}';
    $type = key_exists('t',$_GET) ? trim(strip_tags(stripcslashes($_GET['t']))) : '';
    $dropId = key_exists('d',$_GET) ? intval($_GET['d']) : 0;
    $creativeId = key_exists('cr',$_GET) ? intval($_GET['cr']) : 0;
    $clientId = key_exists('c',$_GET) ? intval($_GET['c']) : 0;
    $listId = key_exists('l',$_GET) ? intval($_GET['l']) : 0;
    
    # send tracking information to bluemail
    if($dropId > 0 && $listId > 0 && $clientId > 0)
    {
        $ip = getIp();
        $agent = base64_encode($_SERVER['HTTP_USER_AGENT']);
        $language = base64_encode($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $scommand = 'nohup php -r \'require_once "' . __DIR__ . '/help.php"; $result = json_decode(sendPostRequest("' . $url . '", [ "api_key" => "x0ja8s4a3duqk9e2w6vga91hrvi7t14wrdxpv754aql055tr2ee2d59b6hop", "func_name" => "procceed-tracking", "data" => [ "type" => "' . $type . '" , "drop-id" => "' . $dropId . '" , "client-id" => "' . $clientId . '" , "list-id" => "' . $listId . '" , "agent" => "' . $agent . '" , "ip" => "' . $ip . '" , "lang" => "' . $language . '" ]]),true);  print_r($result["results"]["message"]);\' >> /tmp/tracks.log 2>> /tmp/tracks.log &';
        exec($scommand); 
    }

    # check if the type is one of the supported types 
    if(in_array($type,['c','l','u']))
    {
        $data = [
            'type' => $type,
            'drop-id' => $dropId,
            'creative-id' => $creativeId,
            'client-id' => $clientId,
            'list-id' => $listId
        ];
        
        $result = json_decode(sendPostRequest($url, array(
            'api_key' => 'x0ja8s4a3duqk9e2w6vga91hrvi7t14wrdxpv754aql055tr2ee2d59b6hop',
            'func_name' => 'generate-link',
            'data' => $data
        )),true);
        
        if(count($result) && $result['statusCode'] == '200' && filter_var($result['results']['link'],FILTER_VALIDATE_URL))
        {
            echo '<pre>Correct Link !</pre>';
            header("Location: " . $result['results']['link']);
            exit();
        }
        else
        {
            echo '<pre>Invalid Link !</pre>';
        }
    }
    elseif(in_array($type,['o']))
    {
        echo '<pre>Operation Finished !</pre>';
    }
    else
    {
        echo '<pre>Unsupported Action Type !</pre>';
    }
}
else
{
    echo '<pre>No Direct Access Allowed !</pre>';
}