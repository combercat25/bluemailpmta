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
    use ma\mfw\http\Session as Session;
    use ma\mfw\www\URL as URL;   
    use ma\applications\bluemail\models\admin\Server as Server;
    use ma\applications\bluemail\helpers\PagesHelper as PagesHelper;
    /**
     * @name            Ips.controller 
     * @description     The Ips controller
     * @package		ma\applications\bluemail\controllers
     * @category        Controller
     * @author		Miami Team			
     */
    class Ips extends Controller 
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
        }

        /**
         * @name index
         * @description the index action
         * @before init
         * @after setMenu,closeConnection
         */
        public function index() 
        {
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'ips' . RDS . 'show.html');
        }
        
        /**
         * @name show
         * @description the show action
         * @before init
         * @after setMenu,closeConnection
         */
        public function show() 
        {
            # get the connected user
            $user = Session::get('bluemail_connected_user'); 
            
            if(!in_array($user['application_role_id'],[1]))
            {
                $servers = Database::getCurrentDatabaseConnector()->executeQuery("SELECT id FROM admin.servers WHERE status_id = 1 AND server_type_id > 1 AND '{$user['id']}' = ANY(string_to_array(authorized_users,','))",true);
            }
            else
            {
                $servers = Server::all(true,array('server_type_id = ? AND status_id = ? ',array(2,1)),array('id'));
            }
            
            $ids = [];
            
            foreach ($servers as $server)
            {
                $ids[] = intval($server['id']);
            }
            
            $list = Database::getCurrentDatabaseConnector()->query()->from('admin.vmtas v',['s.id' => 'server_id','s.name' => 'server_name','v.ip_value' => 'ip_value','v.domain' => 'rdns'])
                    ->join('admin.servers s','s.id = v.server_id')
                    ->where('v.server_id IN (' . join(',',$ids) . ')',[])
                    ->order('s.id','DESC')
                    ->all();

            # get all the columns names 
            $columns = array('server_id','server_name','ip_value','rdns');

            # set the list into the template data system 
            $this->getPageView()->set('list',$list);
            
            # set the columns list into the template data system 
            $this->getPageView()->set('columns',$columns);

            # check for message 
            PagesHelper::checkForMessageToPage($this);
        }

        /**
         * @name setMenu
         * @description set the current menu to the template
         * @protected
         */
        public function setMenu() 
        {
            # set the menu item to active 
            $this->getMasterView()->set('menu_production_ips_show',true);
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