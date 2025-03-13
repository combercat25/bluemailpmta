<?php namespace ma\applications\bluemail\controllers {
    if (!defined('IS_MFW')) die('<pre>It\'s forbidden to access these files directly , access should be only via index.php </pre>');
    /**
     * @framework Miami Framework
     * @version 1.1
     * @author Miami Team
     * @copyright Copyright (c) 2017 - 2018.
     * @license
     * @link
     */
    use ma\mfw\application\Controller as Controller;
    use ma\mfw\database\Database as Database;
    use ma\mfw\http\Request as Request;
    use ma\mfw\http\Session as Session;
    use ma\mfw\http\Response as Response;
    use ma\mfw\www\URL as URL;
    use ma\mfw\types\Arrays as Arrays;
    use ma\applications\bluemail\models\admin\BannedIps as BannedIps;
    use ma\mfw\exceptions\types\PageException as PageException;
    /**
     * @name Bots.controller
     * @description The Data controller
     * @package ma\applications\bluemail\controllers
     * @category Controller
     * @author Miami Team
     */
    class Bots extends Controller
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
            if(!in_array(Arrays::getElement($user,'application_role_id'),array(1 , 2)))
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
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'show.html');
        }
               
        /**
         * @name addip
         * @description Add banned Ips to DB
         * @before init
         * @after setMenu,closeConnection
         */
        public function addip()
        {
            
        }
        
        /**
         * @name save
         * @description save banned Ips to DB
         * @before init
         * @after setMenu,closeConnection
         */
        public function save()
        {
            # get the connected user
            $user = Session::get('bluemail_connected_user'); 

            $message = "Something went wrong !";
            $messageFlag = 'error';

            
        }
        
        /**
         * @name ipslists
         * @description show the list if the banned IPs
         * @before init
         * @after setMenu,closeConnection
         */
        public function ipslists()
        {
            $bannedIPs = BannedIps::all(true,array(),array('*'),'value','ASC');
            
            # get all the columns names 
            $columns = array('id','status','Banned_ip','created_by','created_date','last_updated_by','last_updated_at');

            $this->getPageView()->set('bannedIPs',$bannedIPs);

            
            # set the columns list into the template data system 
            $this->getPageView()->set('columns',$columns);
            
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
         * @description makes sure to close all open connections after execution finished
         * @once
         * @protected
         */
        public function closeConnection()
        {
            # disconnect from all databases
            Database::secureDisconnect();
        }  
    }
}
