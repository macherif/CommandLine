<?php

/**
 * @package Custom
 * @subpackage Mapper
 * Description of common
 * @author <amine.cherif@nicetowebyou.com>
 */
abstract class CommandLine_Mapper_Common implements CommandLine_Mapper_CommonInterface {

    /**
     * @var Zend_Db_Table_Abstract
     */
    protected $_dbTable;
    protected $_tableName;
    protected $_modelSetters = array();
    protected $_modelGetters = array();
    //All fields created in physical db table
    protected $_attributes = array();

    abstract public function __construct();

    abstract public function getDbTable();
    
    abstract public function _hydrate($row);

    public function getDbShema()
    {
        return $this->_attributes;
    }

    /**
     * Specify Zend_Db_Table instance to use for data operations
     *
     * @param  Zend_Db_Table_Abstract $dbTable
     * @return Application_Model_ApplicationMapper
     */
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    /**
     * Get an array of ContenuPlanning objects
     * @access public
     * @return array of objects
     */
    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries = array();
        foreach ($resultSet as $row) {
            $entries[] = $this->_hydrate($row);
        }
        return $entries;
    }

    public function populateForm($id)
    {
        $planning = $this->find($id);
        $data = array();
        $attributes = array_keys($this->getDbShema());
        $getters = $this->generateModelGetters();
        foreach ($attributes as $iterator => $field) {
            $data[$field] = $planning->$getters[$iterator]();
        }
        return $data;
    }

    /**
     * Similar to getElementById
     * @param int $id
     * @return build the video object
     */
    public function find($id)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            //die("User does not exist");
            return false;
        }
        $row = $result->current();
        return $this->_hydrate($row);
    }

    /**
     * toCamelCase
     */
    public function toCamelCase($str)
    {
        $strNoUnderScore = str_replace('_', ' ', trim($str));
        $strCapitalize = ucwords($strNoUnderScore);             // Hello World
        $strNoblanks = str_replace(' ', '', trim($strCapitalize));  // HelloWorld
        $strCamelCase = lcfirst($strNoblanks); // helloWorld
        return $strCamelCase;
    }

    /**
     * AUTO DETECT AND GENERATE AN ARRAY OF CURRENT MODEL SETTERS
     */
    public function generateModelSetters()
    {
        if (count($this->_modelSetters))
            return $this->_modelSetters;
        $fieldsNames = array_keys($this->getDbShema());
        foreach ($fieldsNames as $field) {
            $camelCase = $this->toCamelCase($field);
            $this->_modelSetters[] = 'set' . ucfirst($camelCase);
        }
        return $this->_modelSetters;
    }

    /**
     * AUTO DETECT AND GENERATE AN ARRAY OF CURRENT MODEL GETTERS
     */
    public function generateModelGetters()
    {
        if (count($this->_modelGetters))
            return $this->_modelGetters;
        $fieldsNames = array_keys($this->getDbShema());
        foreach ($fieldsNames as $field) {
            $camelCase = $this->toCamelCase($field);
            $this->_modelGetters[] = 'get' . ucfirst($camelCase);
        }
        return $this->_modelGetters;
    }

    //delete

    public function delete($id)
    {
        $this->getDbTable()->delete('id = ' . (int) $id);
    }

    /**
     * Insert and update object
     * if $id == null execute insert action
     * else execute update action
     * @param int $id
     * @param $buisnessObject
     * @return build the $buisnessObject object with id
     */
    public function save($buisnessObject)
    {
        $data = array();
        $attributes = array_keys($this->getDbShema());
        $getters = $this->generateModelGetters();
        foreach ($attributes as $iterator => $field) {
            $data[$field] = $buisnessObject->$getters[$iterator]();
        }
        if (null === $buisnessObject->getId()) {
            unset($data['id']);
            $lastInsertedId = $this->getDbTable()->insert($data);
            $buisnessObject->setId($lastInsertedId);
            return $buisnessObject;
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $data['id']));
        }
    }

}

?>
