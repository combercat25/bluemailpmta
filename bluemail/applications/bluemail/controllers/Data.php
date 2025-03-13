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
    use ma\mfw\application\Application as Application;
    use ma\mfw\http\Request as Request;
    use ma\mfw\http\Session as Session;
    use ma\mfw\http\Response as Response;
    use ma\mfw\www\URL as URL;
    use ma\mfw\types\Arrays as Arrays;
    use ma\applications\bluemail\models\admin\Status as Status;
    use ma\applications\bluemail\models\data\DataType as DataType;
    use ma\applications\bluemail\models\data\DataList as DataList;
    use ma\applications\bluemail\models\data\Seeds as Seeds;
    use ma\applications\bluemail\models\data\Fresh as Fresh;
    use ma\applications\bluemail\models\data\Clean as Clean;
    use ma\applications\bluemail\models\data\Opener as Opener;
    use ma\applications\bluemail\models\data\Clicker as Clicker;
    use ma\applications\bluemail\models\data\Lead as Lead;
    use ma\applications\bluemail\models\admin\Isp as Isp;
    use ma\applications\bluemail\models\admin\Sponsor as Sponsor;
    use ma\applications\bluemail\models\admin\Offer as Offer;
    use ma\applications\bluemail\models\admin\BounceCleanProccess as BounceCleanProccess;
    use ma\applications\bluemail\models\admin\SuppressionProccess as SuppressionProccess;
    use ma\applications\bluemail\models\admin\blacklist as blacklist;
    use \ma\applications\bluemail\models\admin\Server as Server;
    use ma\applications\bluemail\helpers\PagesHelper as PagesHelper;
    use ma\mfw\api\Api as Api;
    use ma\mfw\globals\Server as GloblServers;
    use ma\mfw\exceptions\types\SQLException as SQLException;
    use ma\mfw\exceptions\types\PageException as PageException;
    /**
     * @name Data.controller
     * @description The Data controller
     * @package ma\applications\bluemail\controllers
     * @category Controller
     * @author Miami Team
     */
    class Data extends Controller
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
         * @name types
         * @description the data types action
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
                $this->getMasterView()->set('menu_admin_data_types',true);
                
                switch ($page)
                {
                    case 'lists' :
                    {
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'types' . DS . 'lists' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_types_list',true);
                        
                        # get the data from the database
                        $list = Database::getCurrentDatabaseConnector()->executeQuery("SELECT dt.id AS id,"
                                                        . " dt.name AS type,"
                                                        . " s.name AS status,"
                                                        . " uc.username AS created_by,"
                                                        . " dt.created_at AS created_date,"
                                                        . " up.username AS last_updated_by,"
                                                        . " dt.last_updated_at AS last_updated_at"
                                                        . " FROM admin.data_types dt"
                                                        . " LEFT JOIN admin.users uc ON uc.id = dt.created_by"
                                                        . " LEFT JOIN admin.status s ON s.id = dt.status_id"
                                                        . " LEFT JOIN admin.users up ON up.id = dt.last_updated_by"
                                                        . " ORDER BY dt.id", true);
                                    
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
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'types' . DS . 'add' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_providers_add',true);
                        
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
                            
                            # set the template for the page view
                            $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'types' . DS . 'edit' . '.' . $this->getDefaultExtension());
                            
                            # set the menu item to active
                            $this->getMasterView()->set('menu_admin_data_providers_add',true);
                        
                            # retrieve the server by id
                            $dataType = DataType::first(true,array('id = ?',$id));
                            # get status list
                            $status = Status::all(true,array(),array('id','name'),'id','ASC');
                            # set the data to the template
                            $this->getPageView()->set('type',$dataType);
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
                            $type = new DataType(array("id" => $id));
                            $type->delete();
                            $message = "Record deleted successfully !";
                            $messageFlag = 'success';
                        }
                        # stores the message in the session
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);
                        # redirect to show list
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'types' . RDS . 'lists.html');
                        
                        break;
                    }
                    case 'save' :
                    {
                        # get the connected user
                        $user = Session::get('bluemail_connected_user');
                        # retrieves the data from post
                        $id = Request::getParameterFromPOST('type-id');
                        $status = Request::getParameterFromPOST('status-id');
                        $type = Request::getParameterFromPOST('type');
                        
                        if(isset($type))
                        {
                            $message = "Something went wrong !";
                            $messageFlag = 'error';
                            if($id != NULL && is_numeric($id))
                            {
                                # update case
                                $dataType = new DataType(array("id" => $id));
                                $dataType->setStatus_id(intval($status));
                                $dataType->setName($type);
                                $dataType->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                $dataType->setLast_updated_at(date("Y-m-d"));
                                $result = $dataType->save();
                                if($result > -1)
                                {
                                    $message = "Record updated succesfully !";
                                    $messageFlag = 'success';
                                }
                            }
                            else
                            {
                                # insert case
                                $dataType = new DataType(array("id" => $id));
                                $dataType->setStatus_id(intval($status));
                                $dataType->setName($type);
                                $dataType->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                $dataType->setCreated_at(date("Y-m-d"));
                                $dataType->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                $dataType->setLast_updated_at(date("Y-m-d"));
                                $result = $dataType->save();
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
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'types' . RDS . 'lists.html');
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'types' . RDS . 'lists.html');
                    }    
                }
            }
        }
        /**
         * @name lists
         * @description the lists action
         * @before init
         * @after setMenu,closeConnection
         */
        public function lists()
        {
            $arguments = func_get_args();
            $page = isset($arguments) && count($arguments) ? $arguments[0] : 'lists';
            if(isset($page))
            {
                # set the menu item to active
                $this->getMasterView()->set('menu_admin_data_lists',true);
                
                switch ($page)
                {
                    case 'all' :
                    {
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'lists' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_lists_all',true);
                        # get tables
                        $lists = DataList::all(true,['status_id < ?',3]);
                        
                        # switch th database
                        Database::switchToDatabase('lists');
                        
                        $res = array();
                                
                        if(count($lists))
                        {
                            foreach ($lists as $list)
                            {
                                if(!empty($list))
                                {
                                    $isp = Arrays::getElement(explode(".",$list['name']),0);
                                    $tablePrefix = Arrays::getElement(explode(".",$list['name']),1);
                                    $type = Arrays::getElement(explode('_', $tablePrefix),0);
                                    $flag = Arrays::getElement(explode('_', $tablePrefix),1);
                                    $flag = is_numeric($flag) ? '-' : $flag;
                                    
                                    # get the count
                                    $count = 0;
                                    
                                    if("true" == Database::getCurrentDatabaseConnector()->checkIfTableExists(Arrays::getElement(explode(".",$list['name']),0),Arrays::getElement(explode(".",$list['name']),1)))
                                    {
                                        # get the count
                                        $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT COUNT(id) AS count FROM {$list['name']}",true);

                                        if(count($result))
                                        {
                                            $count = intval($result[0]['count']);
                                        }
                                    }

                                    $res[] = array(
                                            'list_name' => $tablePrefix,
                                            'type' => ucfirst($type),
                                            'flag' => strtoupper($flag),
                                            'isp' => ucfirst($isp),
                                            'list_count' => $count
                                        );
                                }
                            }
                        }
                        
                        # get all the columns names
                        $columns = array('list_name','type','flag','ISP','list_count');
                       
                        # set the list into the template data system
                        $this->getPageView()->set('list',$res);
                        # set the columns list into the template data system
                        $this->getPageView()->set('columns',$columns);
                        
                        # check for message
                        PagesHelper::checkForMessageToPage($this);
                        break;
                    }
                    case 'blacklist' :
                    {
                        # retrieve all form data
                        $data = Request::getAllDataFromPOST();

                        if(isset($data) && count($data))
                        {
                            $messageFlag = 'error';
                            $message = "Please check be sure that you have selected everything!";
                            
                            # prevent layout to be displayed
                            $this->setShowMasterView(false);
                            $this->setShowPageView(false);
                            $emails = Request::getParameterFromPOST("emails");
                            
                            if(!empty($emails))
                            {
                                $emails = array_values(array_filter(explode(PHP_EOL,$emails), "trim"));
                                $cond = "";
                                
                                if(count($emails)) 
                                {
                                    foreach ($emails as $email) 
                                    {
                                        if(filter_var(trim($email), FILTER_VALIDATE_EMAIL))
                                        {
                                            $blacklist = new blacklist();
                                            $blacklist->setEmail(trim($email));
                                            $blacklist->save();
                                            
                                            $cond .= "'". trim($email)."',"; 
                                        }
                                    } 
                                }
                                
                                Database::switchToDatabase('lists');
                                
                                $tables = Database::getCurrentDatabaseConnector()->getAvailableTables();
                                $cond = rtrim($cond,',');
                                
                                if(count($tables))
                                {
                                    foreach ($tables as $table) 
                                    {
                                        Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM $table WHERE email IN ($cond)"); 
                                    }
                                }
                            
                                $messageFlag = 'success';
                                $message = "BlackList Emails Have been Deleted Successfully !";
                            }
                            
                            
                            Session::set('proccess_message_flag',$messageFlag);
                            Session::set('proccess_message',$message);
                            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
                        }
                        
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'blacklist' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_lists_blacklist',true);
                        
                        break;
                    }
                    case 'cleanBlacklist' :
                    {
                        $messageFlag = 'error';
                        $message = "Please check be sure that you have selected everything!";
                            
                        $emails = Database::getCurrentDatabaseConnector()->query()->from('admin.blacklist',array('email'))->all();
                        $cond = "";
                        
                        if(count($emails)) 
                        {
                            foreach ($emails as $email) 
                            {
                                if(filter_var(trim($email['email']), FILTER_VALIDATE_EMAIL))
                                {
                                    $cond .= "'". trim($email['email'])."',"; 
                                }
                            } 
                        }
                        
                        Database::switchToDatabase('lists');

                        $tables = Database::getCurrentDatabaseConnector()->getAvailableTables();
                        $cond = rtrim($cond,',');

                        if(count($tables))
                        {
                            foreach ($tables as $table) 
                            {
                                Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM $table WHERE email IN ($cond)"); 
                            }
                        } 
                        
                        $messageFlag = 'success';
                        $message = "Data has been cleaned Successfully !";
                                
                        
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
                            
                        
                    }
                    case 'add' :
                    {
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'add' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_lists_add',true);
                        
                        # get status list
                        $status = Status::all(true,array(),array('id','name'),'id','ASC');
                        
                        # get isps list
                        $isps = Isp::all(true,array(),array('id','name'),'id','ASC');
                        # check for message
                        PagesHelper::checkForMessageToPage($this);
                        
                        # set the list into the template data system
                        $this->getPageView()->set('isps',$isps);
                        $this->getPageView()->set('status',$status);
                        break;
                    }
                    case 'seeds' :
                    {
                        # retrieve all form data
                        $data = Request::getAllDataFromPOST();
                        
                        if(isset($data) && count($data))
                        {
                            # prevent layout to be displayed
                            $this->setShowMasterView(false);
                            $this->setShowPageView(false);
                            
                            $listName = Request::getParameterFromPOST("data-list");
                            $emails = Request::getParameterFromPOST("emails");
                            
                            $messageFlag = 'error';
                            $message = "Please check be sure that you have selected everything!";
                                
                            if(!empty($emails) && !empty($listName))
                            {
                                $emails = array_values(array_filter(explode(PHP_EOL,$emails), "trim"));
                                
                                if(count($emails))
                                {
                                    # switch th database
                                    Database::switchToDatabase('lists');
                                    $schema = Arrays::getElement(explode('.',$listName),0);
                                    $list = Arrays::getElement(explode('.',$listName),1);
                                    
                                    # delete the old ones
                                    Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM $listName");
                                    Database::getCurrentDatabaseConnector()->executeQuery("ALTER SEQUENCE {$schema}.seq_id_{$list} RESTART WITH 1;");
                                    
                                    foreach ($emails as $email)
                                    {
                                        $seedsObject = new Fresh();
                                        $seedsObject->setSchema($schema);
                                        $seedsObject->setTable($list);
                                        $seedsObject->setEmail(trim($email));
                                        $seedsObject->setFname(trim(Arrays::getElement(explode('@',$email),0)));
                                        $seedsObject->setLname(trim(Arrays::getElement(explode('@',$email),0)));
                                        
                                        $seedsObject->save();
                                    }
                                    $messageFlag = "success";
                                    $message = "List Updated Successfully !";
                                }  
                            }
                            
                            # stores the message in the session
                            Session::set('proccess_message_flag',$messageFlag);
                            Session::set('proccess_message',$message);
                            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'seeds.html');
                        }
                        
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'seeds' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_lists_seeds',true);
                        # get all isps from the database
                        $isps = Isp::all(true,array('status_id = ? ',1));
                        
                        # set the list into the template data system
                        $this->getPageView()->set('isps',$isps);
                        # check for message
                        PagesHelper::checkForMessageToPage($this);
                        break;
                    }
                    case 'save' :
                    {
                        # get the connected user
                        $user = Session::get('bluemail_connected_user');
            
                        # retrieves the data from post
                        $name = Request::getParameterFromPOST('list-name','');
                        $type = Request::getParameterFromPOST('type');
                        $ispId = Request::getParameterFromPOST('isp-id');
                        $flag = Request::getParameterFromPOST('flag');
                        $emailsFile = Request::getParameterFromFILES('emails');
                        $maxPerList = intval(Request::getParameterFromPOST('devide-by')) > 5000 ? intval(Request::getParameterFromPOST('devide-by')) : 5000;
                        
                        if(isset($ispId))
                        {
                            $isp = Isp::first(true,array('id = ?',$ispId));
                            $schema = strtolower($isp['name']);
                            $message = "Something went wrong !";
                            $messageFlag = 'error';
                            
                            $emailsFromFile = array_unique(array_values(array_filter(file(Arrays::getElement($emailsFile,'tmp_name'),FILE_SKIP_EMPTY_LINES), "trim")));
                            
                            $filesCount = intval(ceil(count($emailsFromFile) / $maxPerList));
                            
                            if(count($emailsFromFile) == 0)
                            {
                                $message = "List empty !";
                            }
                            else if(count($isp) == 0)
                            {
                                $message = "Please select an ISP !";
                            }
                            else
                            {
                                # switch to the apropriate database
                                Database::switchToDatabase('lists');
                                
                                # generate table name prefix;
                                $tablePrefix = strlen(trim($name)) > 0 ? strtolower($type . "_" . $flag . "_" . $name) : strtolower($type . "_" . $flag);
                                
                                # get all the tables
                                $lists = Database::getCurrentDatabaseConnector()->getAvailableTables($schema);
                                
                                $tableIndex = 0;
                                
                                # get the last index
                                foreach ($lists as $seedsObject)
                                {
                                    if(strpos($seedsObject,$tablePrefix) > -1)
                                    {
                                        $tmp = explode('_',$seedsObject);
                                        $currentIndex = intval(Arrays::getElement($tmp,(count($tmp)-1)));
                                        
                                        if($tableIndex < $currentIndex)
                                        {
                                            $tableIndex = $currentIndex;
                                        }
                                    }
                                }
                                
                                $tableIndex++;
                                $maxIndex = 0;
                                
                                for($i = 0; $i < $filesCount; $i++)
                                {
                                    # switch to the apropriate database
                                    Database::switchToDatabase('lists'); 
                                
                                    $header = "id;email;fname;lname;offers_excluded";
                                    $more = ";";
                                    $tableName = $tablePrefix . '_' . intval($tableIndex);
                                    # create the table
                                    switch ($type)
                                    {
                                        case 'fresh':
                                        {
                                            Fresh::synchronizeWithDatabase($tableName,$schema);
                                            break;
                                        }
                                        case 'clean':
                                        {
                                            Clean::synchronizeWithDatabase($tableName,$schema);
                                            break;
                                        }
                                        case 'seeds':
                                        {
                                            Seeds::synchronizeWithDatabase($tableName,$schema);
                                            break;
                                        }
                                        case 'openers':
                                        {
                                            Opener::synchronizeWithDatabase($tableName,$schema);
                                            $header = "id;email;action_date;offers_excluded;verticals;agent;ip;country;region;city;language;device_type;device_name;os;browser_name;browser_version";
                                            $more = ";;;;;;;;;;;;;;";
                                            break;
                                        }
                                        case 'clickers':
                                        {
                                            Clicker::synchronizeWithDatabase($tableName,$schema);
                                            $header = "id;email;action_date;offers_excluded;verticals;agent;ip;country;region;city;language;device_type;device_name;os;browser_name;browser_version";
                                            $more = ";;;;;;;;;;;;;;";
                                            break;
                                        }
                                        case 'leads':
                                        {
                                            Lead::synchronizeWithDatabase($tableName,$schema);
                                            $header = "id;email;action_date;offers_excluded;verticals;agent;ip;country;region;city;language;device_type;device_name;os;browser_name;browser_version";
                                            $more = ";;;;;;;;;;;;;;";
                                            break;
                                        }   
                                    }
                                    # creating csv file to store it into the database
                                    $csvData = $header . PHP_EOL;
                                    $id = 1;
                                    
                                    for ($j = $maxIndex; $j < ($maxIndex + $maxPerList); $j++)
                                    {
                                        $line = trim(preg_replace('/\s\s+/','',$emailsFromFile[$j]));
                                        $fname = '';
                                        $lname = '';
                                        
                                        if(strpos($line,';') != FALSE)
                                        {
                                            $tmp = explode(';',$line);
                                            $email = Arrays::getElement($tmp,0,'');
                                            $fname = Arrays::getElement($tmp,1,'');
                                            $lname = Arrays::getElement($tmp,2,'');
                                        }
                                        else
                                        {
                                            $email = $line;
                                        }
                                        
                                        if(filter_var($email,FILTER_VALIDATE_EMAIL))
                                        {
                                            $csvData .= "{$id};{$email};{$fname};{$lname}{$more}" . PHP_EOL;
                                            $id++; 
//                                            $EmailIsp = Arrays::getElement(explode('.',Arrays::getElement(explode('@',$email),1)),0);
//                                            
//                                            if(strtolower($EmailIsp) === strtolower(trim($isp['name'])))
//                                            { 
//                                                $csvData .= "{$id};{$email};{$fname};{$lname}{$more}" . PHP_EOL;
//                                                $id++;
//                                            }
                                        }
                                        
                                        if($j == (count($emailsFromFile) - 1))
                                        {
                                            break;
                                        }
                                    }
                                    
                                    # create the file name
                                    $randomNumber = rand(10,100);
                                    $csvFileName = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'csv' . DS . "data_file_{$tableName}_$randomNumber";
                                    
                                    # store the file into the server directory and store it into the database
                                    if(file_put_contents($csvFileName,trim($csvData,PHP_EOL)))
                                    {
                                        # get the data from the database
                                        Database::getCurrentDatabaseConnector()->executeQuery("COPY {$schema}.{$tableName} FROM '$csvFileName' WITH CSV HEADER DELIMITER AS ';' NULL AS '';");
                                        
                                        # delete the csv file
                                        unlink($csvFileName);
                                    }
                                    else
                                    {
                                        throw new SQLException("Could not store the csv file into the directory !");
                                    }
                                    
                                    # switch to default database
                                    Database::switchToDefaultDatabase();
                                    
                                    # create the list
                                    $seedsObject = new DataList();
                                    $seedsObject->setName("{$schema}.{$tableName}");
                                    $seedsObject->setIsp_id($ispId);
                                    $seedsObject->setStatus_id(1);
                                    $seedsObject->setFlag($flag);
                                    $seedsObject->setCreated_by(intval(Arrays::getElement($user,'id',1)));
                                    $seedsObject->setCreated_at(date("Y-m-d"));
                                    $seedsObject->setLast_updated_by(intval(Arrays::getElement($user,'id',1)));
                                    $seedsObject->setLast_updated_at(date("Y-m-d"));
                                    $seedsObject->save();
                                    
                                    $message = "List Created Successfully !";
                                    $messageFlag = 'success';
                                    $maxIndex = ($maxIndex + $maxPerList);
                                    $tableIndex++;
                                }
                            }
                            # stores the message in the session
                            Session::set('proccess_message_flag',$messageFlag);
                            Session::set('proccess_message',$message);
                        }
                        # redirect to show list
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
                    }
                    case 'delete' :
                    {
                        $seedsObject = base64_decode(str_replace('_','=',$arguments[1]));
                        
                        $message = "Something went wrong !";
                        $messageFlag = 'error';
                            
                        if(!empty($seedsObject) && (strpos($seedsObject,'fresh') > -1 || strpos($seedsObject,'seeds') > -1))
                        {
                            # switch to data database
                            Database::switchToDatabase('lists');
                            
                            # drop the list
                            Database::getCurrentDatabaseConnector()->executeQuery("DROP TABLE $seedsObject",true);
                            
                            # switch to default database
                            Database::switchToDefaultDatabase();
                                  
                            # drop the list
                            Database::getCurrentDatabaseConnector()->executeQuery("DELETE FROM admin.data_lists WHERE name='$seedsObject'",true);
                            
                            $message = "Record deleted successfully !";
                            $messageFlag = 'success';
                        }
                        
                        # stores the message in the session
                        Session::set('proccess_message_flag',$messageFlag);
                        Session::set('proccess_message',$message);
                 
                        # redirect to show list
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
                    }
                    case 'clean' :
                    { 
                        # retrieve all form data
                        $data = Request::getAllDataFromPOST();
                        
                        if(isset($data) && count($data))
                        {
                            # get the connected user
                            $user = Session::get('bluemail_connected_user');
                            
                            # prevent layout to be displayed
                            $this->setShowMasterView(false);
                            $this->setShowPageView(false);
                            
                            $listName = Request::getParameterFromPOST("data-list");
                            $ispId = intval(Request::getParameterFromPOST("isp-id"));
                            $serverId = intval(Request::getParameterFromPOST("server-id"));
                            
                            $messageFlag = 'error';
                            $message = "Please check be sure that you have selected everything!";
                                
                            if(!empty($listName))
                            {
                                # insert new proccess into the database
                                $proccess = new BounceCleanProccess();
                                $proccess->setUser_id(intval(Arrays::getElement($user,'id',1)));
                                $proccess->setList($listName);
                                $proccess->setStatus('in progress');
                                $proccess->setProgress('0%');
                                $proccess->setHard_bounce(0);
                                $proccess->setClean(0);
                                $proccess->setStart_time(date('Y-m-d H:i:s'));
                                $proccessId = $proccess->save(true);
                                
                                # execute the job that takes care of clean bounce proccess 
                                chdir(APPS_FOLDER. DS . Application::getPrefix() . DS  . 'jobs/');
                               // \ma\mfw\output\PrintWriter::printValue($ispId);
                                exec("nohup /opt/jdk1.8.0_131/bin/java -Dfile.encoding=UTF8 -jar bluemail.jar bounce_clean $proccessId $listName $ispId " . intval(Arrays::getElement($user,'id',1)) . " $serverId >> " . ROOT_PATH . DS . 'tmp/logs/clean.log' . ' 2>>' . ROOT_PATH . DS . 'tmp/logs/clean.log &');

                                # stores the message in the session
                                $messageFlag = 'success';
                                $message = 'Bounce Clean proccess has been started !';
                            }
                            
                            # stores the message in the session
                            Session::set('proccess_message_flag',$messageFlag);
                            Session::set('proccess_message',$message);
                            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'clean.html');
                        }
                        
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'clean' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_lists_clean',true);
                        
                        #get Servers form dv
                        $servers = Server::all(true,array('status_id = ? ',1));
                        
                        # get all isps from the database
                        $isps = Isp::all(true,array('status_id = ? ',1));
                        
                        # get the data from the database
                        $seedsObject = Database::getCurrentDatabaseConnector()->executeQuery("SELECT pr.id AS id,"
                                                        . " pr.list AS list_name,"
                                                        . " uc.username AS mailer,"
                                                        . " pr.status AS status,"
                                                        . " TO_CHAR(pr.start_time, 'YYYY-MM-DD HH24:MI:SS') AS start_time,"
                                                        . " TO_CHAR(pr.finish_time, 'YYYY-MM-DD HH24:MI:SS') AS finish_time,"
                                                        . " pr.progress AS progress,"
                                                        . " pr.hard_bounce AS hard_bounce,"
                                                        . " pr.clean AS clean"
                                                        . " FROM admin.bounce_clean_proccesses pr"
                                                        . " LEFT JOIN admin.users uc ON uc.id = pr.user_id"
                                                        . " ORDER BY pr.id", true);
                                    
                        # get all the columns names
                        $columns = array('id','list_name','mailer','status','start_time','finish_time','progress','hard_bounce','clean');
     
                        
                        # set the list into the template data system
                        $this->getPageView()->set('isps',$isps);
                        $this->getPageView()->set('servers',$servers);
                        $this->getPageView()->set('list',$seedsObject);
                        $this->getPageView()->set('columns',$columns);
                        
                        # check for message
                        PagesHelper::checkForMessageToPage($this);
                        break;
                    }
                    case 'download' :
                    {
                        # set the template for the page view
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'download' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active
                        $this->getMasterView()->set('menu_admin_data_lists_download',true);
                        # get all isps from the database
                        $isps = Isp::all(true,array('status_id = ? ',1));
                        
                        # set the list into the template data system
                        $this->getPageView()->set('isps',$isps);
                        # check for message
                        PagesHelper::checkForMessageToPage($this);
                        break;
                    }
                    case 'suppression' :
                    {
                        # retrieve all form data
                        $data = Request::getAllDataFromPOST();
                        
                        if(isset($data) && count($data))
                        {
                            # get the connected user
                            $user = Session::get('bluemail_connected_user');
                            
                            # prevent layout to be displayed
                            $this->setShowMasterView(false);
                            $this->setShowPageView(false);
                            
                            $sponsorId = intval(Request::getParameterFromPOST("sponsor"));
                            $offerId = intval(Request::getParameterFromPOST("offer"));
                            $link = Arrays::getElement($data,'direct-link');
                            $suppFile = Request::getParameterFromFILES('suppfile');
                            
                            if(!filter_var($link , FILTER_VALIDATE_URL) && is_array($suppFile) && count($suppFile) && Arrays::getElement($suppFile, 'error') == 0 )
                            {
                                $filename = \ma\mfw\types\Strings::generateRandomText(10,true,false,false,false);
                                $ext = '.txt';
                                
                                if(in_array(strtolower(trim(Arrays::getElement($suppFile, 'type'))),['application/zip','application/x-zip-compressed']))
                                {
                                    $ext = '.zip';
                                }
                                
                                $folder = ROOT_PATH . DS . DEFAULT_TEMP_DIRECTORY . DS . 'uploads';
                                $filename .= $ext;
                                
                                if(Arrays::getElement($suppFile, 'size') > 0)
                                {
                                    move_uploaded_file($suppFile["tmp_name"],$folder . DS .$filename);
                                }
                                
                                $link = '';
                                
                                if(file_exists($folder . DS .$filename))
                                {
                                    $link = URL::getBaseURL() . RDS . 'tmp' . RDS . 'uploads' . RDS . $filename;
                                }
                            }

                            $messageFlag = 'error';
                            $message = "Please check be sure that you have selected everything!";
                                
                            if($sponsorId > 0 && $offerId > 0 && filter_var($link,FILTER_VALIDATE_URL))
                            {
                                # insert new proccess into the database
                                $proccess = new SuppressionProccess();
                                $proccess->setUser_id(intval(Arrays::getElement($user,'id',1)));
                                $proccess->setSponsor_id($sponsorId);
                                $proccess->setOffer_id($offerId);
                                $proccess->setStatus('downloading');
                                $proccess->setProgress('0%');
                                $proccess->setEmails_found(0);
                                $proccess->setStart_time(date('Y-m-d H:i:s'));
                                $proccessId = $proccess->save(true);
                                
                                # convert link to base 64
                                $link = base64_encode($link);
                                
                                # execute the job that takes care of suppression proccess 
                                chdir(APPS_FOLDER. DS . Application::getPrefix() . DS  . 'jobs/');
                                exec("nohup /opt/jdk1.8.0_131/bin/java -Dfile.encoding=UTF8 -jar bluemail.jar suppression_proccess $proccessId $offerId $link >> " . ROOT_PATH . DS . 'tmp/logs/suppression.log' . ' 2>>' . ROOT_PATH . DS . 'tmp/logs/suppression.log &');

                                # stores the message in the session
                                $messageFlag = 'success';
                                $message = 'Suppression proccess has been started !';
                            }
                            
                            # stores the message in the session
                            Session::set('proccess_message_flag',$messageFlag);
                            Session::set('proccess_message',$message);
                            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'suppression.html');
                        }

                        # set the template for the page view 
                        $this->getPageView()->setFile(APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'data' . DS . 'lists' . DS . 'suppression' . '.' . $this->getDefaultExtension());
                        
                        # set the menu item to active 
                        $this->getMasterView()->set('menu_admin_data_lists_suppression',true);
                        
                        # get all sponsors from the database
                        $sponsors = Sponsor::all(true,array('status_id = ? ',1));
                        
                        # get the data from the database
                        $seedsObject = Database::getCurrentDatabaseConnector()->executeQuery("SELECT pr.id AS id,"
                                                        . " s.name AS sponsor_name,"
                                                        . " o.name AS offer_name,"
                                                        . " uc.username AS mailer,"
                                                        . " pr.status AS status,"
                                                        . " TO_CHAR(pr.start_time, 'YYYY-MM-DD HH24:MI:SS') AS start_time,"
                                                        . " TO_CHAR(pr.finish_time, 'YYYY-MM-DD HH24:MI:SS') AS finish_time,"
                                                        . " pr.progress AS progress,"
                                                        . " pr.emails_found AS emails_found"
                                                        . " FROM admin.suppression_proccesses pr"
                                                        . " LEFT JOIN admin.users uc ON uc.id = pr.user_id"
                                                        . " LEFT JOIN admin.sponsors s ON s.id = pr.sponsor_id"
                                                        . " LEFT JOIN admin.offers o ON o.id = pr.offer_id"
                                                        . " ORDER BY pr.id", true);
                                    
                        # get all the columns names 
                        $columns = array('id','sponsor_name','offer_name','mailer','status','start_time','finish_time','progress','emails_found');
                        
                        $this->getPageView()->set('sponsors',$sponsors); 
                        $this->getPageView()->set('columns',$columns);
                        $this->getPageView()->set('list',$seedsObject);
                        
                        # check for message
                        PagesHelper::checkForMessageToPage($this);
                        break;
                    }
                    default:
                    {
                        Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
                    }    
                }
            }
        }
        
        /**
         * @name getDataLists
         * @description gets the data lists by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDataLists()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                $arguments = func_get_args();
                $ispId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                $flag = isset($arguments) && count($arguments) > 1 ? $arguments[1] : null;
                
                if(isset($ispId) && is_numeric($ispId) && $this->getDefaultContentType() == 'text/json')
                {
                    $lists = array();
                    
                    $ispResult = Isp::first(true,array('id = ?',$ispId),array('name'));
                    
                    # switch th database
                    Database::switchToDatabase('lists');
                    if(count($ispResult))
                    {
                        # specify the schema
                        $schema = strtolower(trim($ispResult['name']));
                        
                        # specify the type
                        $type = 'fresh';
                        
                        # get tables
                        $tables = Database::getCurrentDatabaseConnector()->getAvailableTables($schema);
                        
                        if(count($tables))
                        {
                            foreach ($tables as $table)
                            {
                                if(!empty($table))
                                {
                                    $tableCondition = $type . '_' . $flag;
                                    
                                    if(strpos($table,$tableCondition) > -1)
                                    {
                                        $lists[] = array('id' => $schema . '.' . $table , 'name' => $table);
                                    }
                                }
                            }
                        }
                    }
                 
                    # print the result
                    die(json_encode(array( "lists" => $lists)));
                }
            }
            else
            {
                # redirect to show list
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
            }
        }
        
        /**
         * @name getDataSeedsLists
         * @description gets the data lists by id into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDataSeedsLists()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                $arguments = func_get_args();
                $ispId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                
                if(isset($ispId) && is_numeric($ispId) && $this->getDefaultContentType() == 'text/json')
                {
                    $lists = array();
                    
                    $ispResult = Isp::first(true,array('id = ?',$ispId),array('name'));
                    
                    # switch th database
                    Database::switchToDatabase('lists');
                    if(count($ispResult))
                    {
                        # specify the schema
                        $schema = strtolower(trim($ispResult['name']));
                        
                        # specify the type
                        $type = 'seeds';
                        
                        # get tables
                        $tables = Database::getCurrentDatabaseConnector()->getAvailableTables($schema);
                        
                        if(count($tables))
                        {
                            foreach ($tables as $table)
                            {
                                if(!empty($table))
                                {
                                    $tableCondition = $type . '_';
                                    
                                    if(strpos($table,$tableCondition) > -1)
                                    {
                                        $lists[] = array('id' => $schema . '.' . $table , 'name' => $table);
                                    }
                                }
                            }
                        }
                    }
                 
                    # print the result
                    die(json_encode(array( "lists" => $lists)));
                }
            }
            else
            {
                # redirect to show list
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
            }
        }
        /**
         * @name getDataListCount
         * @description gets the data count by list into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDataListCount()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                $data = Request::getAllDataFromPOST();
                
                if(count($data) && $this->getDefaultContentType() == 'text/json')
                {
                    $count = 0;
                    $listName =  $data["data-list"];
                    
                    # switch th database
                    Database::switchToDatabase('lists');
                    
                    $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT COUNT(id) AS count FROM {$listName}",true);
                    
                    if(count($result))
                    {
                        $count = intval($result[0]['count']);
                    }
                 
                    # print the result
                    die(json_encode(array( "count" => $count)));
                }
            }
            else
            {
                # redirect to show list
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
            }
        }
        
        /**
         * @name updateBounceProgress
         * @description update proccess progress
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function updateBounceProgress()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                if($this->getDefaultContentType() == 'text/json')
                {
                    $progress = '0%';
                    $hardBounce = '0';
                    $clean = '0';
                    
                    $arguments = func_get_args();
                    $proccessId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                    
                    if(isset($proccessId) && is_numeric($proccessId) && $this->getDefaultContentType() == 'text/json')
                    {
                        $proccess = BounceCleanProccess::first(true,array('id = ?',$proccessId));
                        
                        if(isset($proccess) && $proccess['status'] == 'in progress')
                        {
                            $progress = $proccess['progress'];
                            $hardBounce = $proccess['hard_bounce'];
                            $clean = $proccess['clean'];
                        } 
                    } 
                    
                    die(json_encode(array("progress" => $progress , "hard_bounce" => $hardBounce , "clean" => $clean)));
                }
            }
        }
        
        /**
         * @name updateBounceProgress
         * @description update proccess progress
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function downloadData()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                $csv = '';
                
                if($this->getDefaultContentType() == 'text/json')
                {
                    # prevent layout to be displayed
                    $this->setShowMasterView(false);
                    $this->setShowPageView(false);
                    $listName = Request::getParameterFromPOST("data-list");
                    if(!empty($listName))
                    {
                        Database::switchToDatabase('lists');
                        $emails = Database::getCurrentDatabaseConnector()->query()->from($listName,array('email'))->all();
                        $name = Arrays::getElement(explode('.',$listName),1);
                        foreach ($emails as $row)
                        {
                            $email = preg_replace( "/\r|\n/","", trim($row['email']));
                            $csv .= $email . PHP_EOL;
                        }
                    }
                    
                    die(json_encode(array("name" => $name , "content" => $csv)));
                }
            }
        }
        
        /**
         * @name updateSuppressionProgress
         * @description update proccess progress
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function updateSuppressionProgress()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                if($this->getDefaultContentType() == 'text/json')
                {
                    $progress = '0%';
                    $emailsFound = '0';
                    
                    $arguments = func_get_args();
                    $proccessId = isset($arguments) && count($arguments) ? $arguments[0] : null;
                    
                    if(isset($proccessId) && is_numeric($proccessId) && $this->getDefaultContentType() == 'text/json')
                    {
                        $proccess = SuppressionProccess::first(true,array('id = ?',$proccessId));
                        
                        if(isset($proccess) && $proccess['status'] == 'in-progress')
                        {
                            $progress = $proccess['progress'];
                            $emailsFound = $proccess['emails_found'];
                        } 
                    } 
                    
                    die(json_encode(array("progress" => $progress , "emails_found" => $emailsFound)));
                }
            }
        }
        
        /**
         * @name getDataListSeedsEmails
         * @description gets the data emails by list into a JSON format
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getDataListSeedsEmails()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                if(count(Request::getAllDataFromPOST()) && $this->getDefaultContentType() == 'text/json')
                {
                    $emails = array();
                    
                    $listName = Request::getParameterFromPOST("data-list");

                    if(strpos($listName,'seeds') > -1)
                    {
                        # switch th database
                        Database::switchToDatabase('lists');
                        
                        $result = Database::getCurrentDatabaseConnector()->executeQuery("SELECT email FROM {$listName}",true);
                        
                        if(count($result))
                        {
                            foreach ($result as $row)
                            {
                                $emails[] = trim($row['email']);
                            }
                        }
                    }
                    
                    # print the result
                    die(json_encode(array( "emails" => implode(PHP_EOL,$emails))));
                }
            }
            else
            {
                # redirect to show list
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
            }
        }
        
        /**
         * @name getSuppressionLink
         * @description getSuppressionLink
         * @type ajax
         * @before init
         * @after closeConnection
         */
        public function getSuppressionLink()
        {
            # check if the request is not AJAX request then return to index
            if(!empty(GloblServers::get('HTTP_X_REQUESTED_WITH')) && strtolower(GloblServers::get('HTTP_X_REQUESTED_WITH')))
            {
                # prevent layout to be displayed
                $this->setShowMasterView(false);
                $this->setShowPageView(false);
                
                $data = Request::getAllDataFromPOST();
                
                if(count($data) && $this->getDefaultContentType() == 'text/json')
                {
                    $link = "";
                    $sponsorId = intval($data["sponsor-id"]);
                    $offerId = intval($data["offer-id"]);
                    
                    if($sponsorId > 0 && $offerId > 0)
                    {
                        $sponsor = Sponsor::first(true,array('id = ?',$sponsorId));
                        $offer = Offer::first(true,array('id = ?',$offerId));
                        
                        if(count($sponsor) && count($offer))
                        {
                            $api = Api::getAPIClass($sponsor); 

                            if($api != null)
                            {
                                $link = $api->getSuppressionFileLink($offer);
                            }
                        } 
                    }
                    
                    # print the result
                    die(json_encode(array("link" => $link)));
                }
            }
            else
            {
                # redirect to show list
                Response::redirect(URL::getCurrentApplicationURL() . RDS . 'data' . RDS . 'lists' . RDS . 'all.html');
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
