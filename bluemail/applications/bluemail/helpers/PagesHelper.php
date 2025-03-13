<?php namespace ma\applications\bluemail\helpers
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
    use ma\mfw\types\Arrays as Arrays;
    use ma\mfw\http\Session as Session;
    /**
     * @name            PagesHelper.class 
     * @description     The PagesHelper class
     * @package		ma\applications\bluemail\helpers
     * @category        Helper
     * @author		Miami Team			
     */
    class PagesHelper
    {
        /**
         * @name buildStatsTableForExcel
         * @description build stats table for excel
         * @access static
         * @param mixed $controller
         * @return
         */
        public static function checkForMessageToPage($controller)
        {
            if($controller)
            {
                # check if there is a message from a previous action
                $message = Session::getThenDel('proccess_message');

                if(isset($message))
                {
                    $messageFlag = Session::getThenDel('proccess_message_flag');
                    $button = $messageFlag == 'error' ? 'btn-danger' : 'btn-primary';

                    $html = '<script>Bluemail.alertBox({title:"' . $message . '",type:"' . $messageFlag . '",allowOutsideClick:"true",confirmButtonClass:"' . $button . '"});</script>';

                    # set the message into the template data system 
                    $controller->getMasterView()->set('prev_action_message',$html);
                }
            }
        }
        
        /**
 * @name generateDKIM
 * @description generate DKIM
 * @param SSH $ssh
 * @param string $domain 
 * @return $dkim
 */
public static function generateDKIM($ssh,$domain,$prefix)
{
    $dkim = array();
    
    if($domain != '')
    {
        $ssh->cmd("{$prefix}mkdir /etc/opendkim/keys/$domain;");
        $ssh->cmd("{$prefix}/usr/sbin/opendkim-genkey -D /etc/opendkim/keys/$domain/ -d $domain -s mail;");
        $ssh->cmd("{$prefix}chown -R opendkim:opendkim /etc/opendkim/keys/$domain;");
        $ssh->cmd("{$prefix}chmod 640 /etc/opendkim/keys/$domain/mail.private;");
        $ssh->cmd("{$prefix}chmod 644 /etc/opendkim/keys/$domain/mail.txt;");
        $ssh->cmd("{$prefix}'mail._dkim.$domain $domain:mail:/etc/opendkim/keys/$domain/mail.private' >> /etc/opendkim/KeyTable");
        $ssh->cmd("{$prefix}'*.$domain' >> /etc/opendkim/TrustedHosts");
        $ssh->cmd("{$prefix}'*@$domain mail._domainkey.$domain' >> /etc/opendkim/SigningTable");
        
        $content = $ssh->cmd("{$prefix}cat /etc/opendkim/keys/$domain/mail.txt",true);
        
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
    
    return $dkim;
}
        
        
    }
}