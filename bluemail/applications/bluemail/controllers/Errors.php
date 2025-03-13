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
    use ma\mfw\registry\Packager as Packager;
    use ma\mfw\http\Response as Response;
    use ma\mfw\http\Session as Session;
    use ma\mfw\www\URL as URL;
    use ma\mfw\application\Application as Application;
    use ma\mfw\application\View as View;
    /**
     * @name            Errors.controller 
     * @description     The Errors controller
     * @package		ma\applications\bluemail\controllers
     * @category        Controller
     * @author		Miami Team			
     */
    class Errors extends Controller 
    {
        /**
         * @name init
         * @description initializing proccess before the action method executed
         * @once
         * @protected
         */
        public function init() 
        {
            # prevent access to it like a normal controller
            Response::redirect(URL::getCurrentApplicationURL() . RDS . 'authentication' . RDS . 'login.html');
        }

        /**
         * @name index
         * @description the index action
         * @before init
         */
        public function index() 
        {
            $this->showErrorPage();
        }
        
        /**
         * @name showErrorPage
         * @description displays the error page
         * @protected
         */
        public function showErrorPage() 
        {            
            # get the exception from the database
            $exception = Session::getThenDel("bluemail-error-exception");
            
            # make sure that the master page does not show
            $this->setShowMasterView(false);

            # disconnect from all databases 
            Database::secureDisconnect();
            
            # initialize error page info 
            $message = "Unknown Error !";
            $code = 500;
            
            if(isset($exception) && is_object($exception))
            {
                $message = $exception->getMessage();
                $code = $exception->getCode();
            }
            
            # change the router controller to error controler
            $router = Packager::get('router');
            
            if (isset($router))
            {
                $router->setController('errors');
                $router->setAction('index');
            }
            
            # get the router object
            Packager::set('router',$router);

            # create a new view as an error action view and fill it with data 
            
            $view = new View(array("file" => APPS_FOLDER. DS . Application::getPrefix() . DS . $this->getTemplatesPath() . DS . 'errors' . DS . 'index.html'));
            $this->fillViewWithDefaultData($view);
            $view->set('code',$code);
            $view->set('message',$message);
            $this->setPageView($view);   
        } 
    }
}