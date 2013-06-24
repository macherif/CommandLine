<?php

/**
 * @package CommandLine
 * @subpackage CommandLine
 * @author amine <amine.cherif@nicetowebyou.com>
 */

/**
 * Force the class to implements these methods
 * initZendEnv
 * getDb
 */
interface CommandLine_BaseScriptInterface {

    public function initZendEnv($applicationPath);

    public function getDb();
}

/**
 * Instanciate a Zend Environnement project
 */
class CommandLine_BaseScript implements CommandLine_BaseScriptInterface {

    private $_db;
    private $_dbName;
    protected $_config;

    public function __construct()
    {
        
    }

    /**
     * Instanciate a Zend Environnement project
     * @method initZendEnv
     * @author amine <amine.cherif@nicetowebyou.com>
     * @return null
     */
    public function initZendEnv($applicationPath)
    {
        // Define path to application directory
        defined('APPLICATION_PATH')
                || define('APPLICATION_PATH', $applicationPath);

        // Define ZF library path environment
        $defaultValue = realpath(APPLICATION_PATH . '/../../library');
        defined('ZF_LIBRARY_PATH')
                || define('ZF_LIBRARY_PATH', (getenv('ZF_LIBRARY_PATH') ? getenv('ZF_LIBRARY_PATH') : $defaultValue));
        define('APPLICATION_ENV', 'development');
        // Ensure library/ is on include_path
        set_include_path(
                implode(
                        PATH_SEPARATOR, array(
                    ZF_LIBRARY_PATH,
                    realpath(APPLICATION_PATH . '/../library'),
                    get_include_path(),
                        )
                )
        );
        require_once 'Zend/Application.php';
        // Specific ini files cache
        require_once 'Zend/Config/Ini.php';
        require_once 'Zend/Db/Table.php';
        $application = new Zend_Application(
                        APPLICATION_ENV,
                        APPLICATION_PATH . '/configs/application.ini'
        );
        $this->_config = new Zend_Config_Ini(
                        APPLICATION_PATH . '/configs/application.ini',
                        APPLICATION_ENV
        );
        // Only load resources we need for script, in this case db
        $application->bootstrap(array('multidb'));
    }

    /**
     * Get the DB layer object from the current project application.ini
     * @see initZendEnv()
     * @author amine <amine.cherif@nicetowebyou.com>
     * @return object $_db
     */
    public function getDb()
    {
        if ((null === $this->_db)) {
            /*if(in_array('db', $this->_config->ressources) ){
            $dbConfig = $this->_config->resources->db;
            $this->_db = Zend_Db::factory($dbConfig->adapter, $dbConfig->params);
            } else {*/
                $this->_db = Zend_db_Table::getDefaultAdapter();
           /* }*/
        }
        return $this->_db;
    }

    /**
     * Get the current Database Name
     * @author amine <amine.cherif@nicetowebyou.com>
     * @return string current databse name
     */
    public function getDbName()
    {
        if ($this->_dbName === null) {
            $sql = 'SELECT DATABASE();';
            $this->_dbName = $this->getDb()->query($sql)->fetchColumn();
        }
        return $this->_dbName;
    }

}