<?php namespace ma\mfw\ssh2
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
    use ma\mfw\core\Base as Base;
    /**
     * @name            SSHAuthentication.class 
     * @description     It's a parent class of authentications types
     * @package		ma\mfw\ssh2
     * @category        SSH
     * @author		Miami Team			
     */
    class SSHAuthentication extends Base
    {}  
}