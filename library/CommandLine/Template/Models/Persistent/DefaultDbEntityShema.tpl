<?php

/**
 * @package Persitent
 * @subpackage persistent
 * @author <amine.cherif@nicetowebyou.com>
 * @since version 0.1
 * @version 0.1
 * !! DON'T MODIFY THIS FILE
 * This file is automatically generated by the CommandLine_ModelsGenerator script
 * This file will be overriden at next execution of this script.
 * If you wan't to customize an Entity behaviour, put your code in the Entity
 * class in Application_Entity_%current_entity.php file
 */
class Application_Model_Persistent_%current_entityDbMapper extends CommandLine_Mapper_Common
{
    /**
     * @var Zend_Db_Table_Abstract
     */
    protected $_dbTable;
    protected $_tableName;
    protected $_modelSetters = array();
    protected $_modelGetters = array();
    //All fields created in physical db table
    var $_attributes = array();
    
  // __construct function overload to define type of storage.
  public function __construct()
  {
    $this->_tableName = "%table_name";

    $this->_attributes = array(

      %attributes

    );

  }
  
    /**
     * Get registered Zend_Db_Table instance
     * @return Zend_Db_Table_Abstract
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_%current_entity');
        }
        return $this->_dbTable;
    }

    public function getDbShema()
    {
        return $this->_attributes;
    } 
    
    /**
     * Build a buisness object from an array
     * @param array $row
     * @return \Application_Model_%current_entity
     */
    protected function _hydrate($row)
    {
        $%current_entity = new Application_Model_Entity_%current_entity();
        $attributes = array_keys($this->getDbShema());
        $setters = $this->generateModelSetters();
        foreach ($attributes as $iterator => $attribut){
            $%current_entity->$setters[$iterator]($row->$attribut);
        }
        return $%current_entity;
    }
     

}
