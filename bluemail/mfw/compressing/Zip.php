<?php namespace ma\mfw\compressing
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
     * @name            Zip.class 
     * @description     It's a class that deals with zipping / unzipping mechanism
     * @package		ma\mfw\compressing
     * @category        Helper Class
     * @author		Miami Team			
     */
    class Zip extends Base
    {
        /**
         * @name extractZipFileTo
         * @description extracts a zip file to a given directory
         * @access static
         * @param  string $fileName
         * @return int
         */
        public static function extractZipFileTo($fileName,$extractPath)
        {
            if (file_exists($extractPath))
            {
                $zip = new \ZipArchive();
                $res = $zip->open($fileName);

                if ($res === true)
                {
                    $zip->extractTo($extractPath);
                    $zip->close();
                    return true;
                }
            }

            return false;
        }

        /**
         * @name getFileNames
         * @description gets the names of all the files in a specefic zip file
         * @access static
         * @param string $zipFile
         * @param array
         * @return
         */
        public static function getFileNames($zipFile)
        {
            $names = [];
            $zip = new \ZipArchive();
            $res = $zip->open($zipFile);

            if ($res === true)
            {
                for ($i = 0; $i < $zip->numFiles; $i++)
                {
                    $names[] = $zip->getNameIndex($i);
                }
            }

            return $names;
        }
    }
}