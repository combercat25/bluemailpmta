<?php
/**
 * @framework       Miami Framework
 * @version         1.1
 * @author          Miami Team
 * @copyright       Copyright (c) 2017 - 2018.	
 * @license		
 * @link	
 */ 

# help methods 

/**
 * @name sendPostRequest
 * @description send post request
 * @access public
 * @param string $url
 * @param boolean $data
 * @return mixed
 */
function sendPostRequest($url,$data) 
{
    $response = null;

    # preparing the post data
    $post = array();

    $post = array_merge($post,$data);
    $postFields = http_build_query($post);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$postFields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

/**
 * @name getIp
 * @description get client ip
 * @access public
 * @return string
 */
function getIp()
{
    $ip = "";

    if (!empty($_SERVER['HTTP_CLIENT_IP']))
    {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } 
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } 
    else 
    {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    if(filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV6))
    {
        $ipv4 = hexdec(substr($ip, 0, 2)). "." . hexdec(substr($ip, 2, 2)). "." . hexdec(substr($ip, 5, 2)). "." . hexdec(substr($ip, 7, 2));
        $ip = $ipv4;
    }

    if(!filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4))
    {
        $match = array();

        if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/',$ip, $match)) 
        {
            $ip = count($match) > 0 && filter_var($match[0],FILTER_VALIDATE_IP) ? $match[0] : "";
        }
    }

    return $ip;
}