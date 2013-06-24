<?php

  require_once 'BaseScript.php';

  /**
   * Generate all project Models
   */
  class CommandLine_ModelsGenerator extends CommandLine_BaseScript
  {

      private $_tablesNames = array();
      private $_shemaList = array();
      private $_currentEntity;
      private $_currentDbEntityShema;
      private $_dbEntityTpl;
      private $_dbEntityShemaTpl;
      private $_EntityTpl;
      private $_MapperTpl;
      private $_currentDbTable;
      private $_dbTableTpl;
      private $_modelVars;
      private $_modelGetters;
      private $_modelSetters;

      public function __construct()
      {
          parent::__construct();
      }

      /**
       * Run ModelsGenerator
       * @author amine <amine.cherif@nicetowebyou.com>
       */
      public function run()
      {
          //first step get DB Name
          $this->getDbName();
          //second step get tables names
          $this->getTablesNames();
          $this->buildShemaList();
          //third step get tempaltes
          $this->getDbEntityShemaTpl();
          $this->getDbEntityTpl();
          $this->getDbTableTpl();
          #check if persistent folder exist
          $this->manageStructure();
          //forth step replace tokens on templates
          foreach ($this->_tablesNames as $tableName) {
              list($dbEntityShemaTpl, $dbEntityTpl, $dbTableTpl, $entityTpl, $mapperTpl) = $this->generateCodeForEntity($tableName);
              //fifth step create new files
              $this->createDbEntityDbShema($dbEntityShemaTpl);
              $this->createDbEntity($dbEntityTpl);
              $this->createDbTable($dbTableTpl);
              $this->createEntity($entityTpl);
              $this->createMapper($mapperTpl);
          }
      }

      /**
       * Get a list of tables in current database
       * @method getTablesNames
       * @author amine <amine.cherif@nicetowebyou.com>
       * @return array $_tablesNames
       */
      protected function getTablesNames()
      {
          /**
           * @example see behind
           * mysql> SHOW TABLES;
           * +----------------------+
           * | Tables_in_song|
           * +----------------------+
           * |     artist           |
           * |     song             |
           * |     track            |
           * +----------------------+
           *
           */
          $sql = 'SHOW TABLES;';
          $tempList = $this->getDb()->query($sql)->fetchAll();
          //arrange array list $tempList to be stored into $_tablesNames:
          for ($i = 0; $i < count($tempList); $i++) {
              $this->_tablesNames[$i] =
                      $tempList[$i]['Tables_in_' . trim($this->getDbName())];
          }
          return $this->_tablesNames;
      }

      /**
       * show the schema of a MySQL table
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param string $table table name
       */
      protected function getTableShema($table)
      {
          /**
           * @example see behind
           * mysql> desc customers;
           * +-------+------------------+------+-----+---------+----------------+
           * | Field | Type             | Null | Key | Default | Extra          |
           * +-------+------------------+------+-----+---------+----------------+
           * | id    | int(10) unsigned | NO   | PRI | NULL    | auto_increment |
           * | name  | varchar(20)      | NO   |     | NULL    |                |
           * +-------+------------------+------+-----+---------+----------------+
           */
          $sql = 'DESC ' . $table;
          return $this->getDb()->query($sql)->fetchAll();
      }

      /**
       * Build a structured arrays with table names as key
       * and list of table fields as value
       * @author amine <amine.cherif@nicetowebyou.com>
       * @return array $this->_shemaList
       */
      protected function buildShemaList()
      {
          //Check if table names are already buided
          if (count($this->_tablesNames) == 0) {
              $this->getTablesNames();
          }
          for ($i = 0; $i < count($this->_tablesNames); $i++) {
              $this->_shemaList[$this->_tablesNames[$i]] =
                      $this->getTableShema($this->_tablesNames[$i]);
          }
          return $this->_shemaList;
      }

      /**
       * Replace Tokens with the appropriate text
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param string $tokens
       * @param string $newText
       * @param string $content
       * @return string $content
       */
      protected function replaceTokens($tokens, $newText, $content)
      {
          return str_replace($tokens, $newText, $content);
      }

      /**
       * Get the content of DefaultDbEntityShema.php
       * @author amine <amine.cherif@nicetowebyou.com>
       * @return string
       */
      protected function getDbEntityShemaTpl()
      {
          if ($this->_dbEntityTpl === null) {
              $this->_dbEntityTpl =
                      file_get_contents('../library/CommandLine/Template/Models/Persistent/DefaultDbEntityShema.tpl');
          }
          return $this->_dbEntityTpl;
      }

      /**
       * Get the content of DefaultEntity.tpl
       * @author amine <amine.cherif@nicetowebyou.com>
       * @return string
       */
      protected function getEntityTpl()
      {
          if ($this->_EntityTpl === null) {
              $this->_EntityTpl =
                      file_get_contents('../library/CommandLine/Template/Models/Entity.tpl');
          }
          return $this->_EntityTpl;
      }

      /**
       * Get the content of Mapper.tpl
       * @author amine <amine.cherif@nicetowebyou.com>
       * @return string
       */
      protected function getMapperTpl()
      {
          if ($this->_MapperTpl === null) {
              $this->_MapperTpl =
                      file_get_contents('../library/CommandLine/Template/Models/Mapper.tpl');
          }
          return $this->_MapperTpl;
      }

      /**
       * Get the content of DefaultDbEntity.php
       * @author amine <amine.cherif@nicetowebyou.com>
       * @return string
       */
      protected function getDbEntityTpl()
      {
          if ($this->_dbEntityShemaTpl === null) {
              $this->_dbEntityShemaTpl =
                      file_get_contents('../library/CommandLine/Template/Models/Persistent/DefaultDbEntity.tpl');
          }
          return $this->_dbEntityShemaTpl;
      }

      /**
       * Get the content of DefaultDbTable.php
       * @author amine <amine.cherif@nicetowebyou.com>
       * @return string
       */
      protected function getDbTableTpl()
      {
          if ($this->_dbTableTpl === null) {
              $this->_dbTableTpl =
                      file_get_contents('../library/CommandLine/Template/DbTable/Default.tpl');
          }
          return $this->_dbTableTpl;
      }

      /**
       * Format table name to been an Entity name
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param string $tableName
       * @return string
       */
      protected function tableToEntityName($tableName)
      {
          $entityName = $this->strSeekGetter($tableName);
          return $entityName;
      }

      /**
       * Return the approporiate camelcase of a table column name
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param string $chaine
       * @return string
       */
      protected function strSeekGetter($chaine)
      {
          $charac = '';
          $rValue = '';
          $chaine = (string) strtolower($chaine);

          for ($i = 0; $i < strlen($chaine); $i++) {
              $charac = $chaine{$i};
              if ($charac == '_') {
                  $characReplace = strtoupper($chaine{$i + 1});
                  $i = $i + 1;
              } else {
                  $characReplace = $charac;
              }
              $rValue .= $characReplace;
          }
          return ucfirst($rValue);
      }

      /**
       * set the Current Entity to be reachable into next level
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param string $tableName
       * @return string
       */
      protected function setCurrentEntity($tableName)
      {
          $this->_currentEntity = trim($this->tableToEntityName($tableName));
          return $this->_currentEntity;
      }

      /**
       * Retrieve current table shema
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param string $tableName
       * @return string
       */
      public function getCurrentTableShema($tableName)
      {
          $this->_currentDbEntityShema = $this->_shemaList[$tableName];
          return $this->_currentDbEntityShema;
      }

      /**
       * Retrieve current DbTable shema
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param string $tableName
       * @return string
       */
      public function getCurrentDbTable($tableName)
      {
          $this->_currentDbTable = $this->_shemaList[$tableName];
          return $this->_currentDbTable;
      }

      /**
       * List of attributes to be used on the DbShema file
       * @author amine <amine.cherif@nicetowebyou.com>
       * @return string $attribute
       */
      public function attributeToText()
      {
          $fieldsNames = $this->getFieldsNames();
          // add quotes DB attributes to be wrighted as a php array
          foreach ($fieldsNames as $key => $value) {
              $fieldsNames[$key] = '\'' . $value . '\'';
          }
          $attribute = implode(
                  " => null,
      ", $fieldsNames
          );
          $attribute .= " => null
      ";
          return $attribute;
      }

      /**
       * Check for current table structure
       * @author amine <amine.cherif@nicetowebyou.com>
       * @return type
       */
      protected function getFieldsNames()
      {
          $fieldsNames = array();
          for ($i = 0; $i < count($this->_currentDbEntityShema); $i++) {
              $fieldsNames[$i] = $this->_currentDbEntityShema[$i]['Field'];
          }
          return $fieldsNames;
      }

      protected function generateModelVars()
      {
          $this->_modelVars = NULL;
          $fieldsNames = $this->getFieldsNames();
          // add quotes DB attributes to be wrighted as a php array
          foreach ($fieldsNames as $value) {
              $this->_modelVars .= ' protected $_' . $this->toCamelCase($value) . "; \n ";
          }
      }

      protected function generateModelGetters()
      {
          $this->_modelGetters = NULL;
          $fieldsNames = $this->getFieldsNames();
          // add quotes DB attributes to be wrighted as a php array
          foreach ($fieldsNames as $value) {
              $camelCase = $this->toCamelCase($value);
              $this->_modelGetters .= " \n /** \n  * getter of $" . $camelCase . " \n  */ \n"
                      . ' public function get' . ucfirst($camelCase)
                      . '()' . "\n "
                      . "{ \n "
                      . ' return $this->_' . $camelCase . ';'
                      . "\n } \n ";
          }
      }

      protected function generateModelSetters()
      {
          $this->_modelSetters = NULL;
          $fieldsNames = $this->getFieldsNames();
          // add quotes DB attributes to be wrighted as a php array
          foreach ($fieldsNames as $value) {
              $camelCase = $this->toCamelCase($value);
              $this->_modelSetters .= " \n /** \n  * setter of $" . $camelCase . " \n  */ \n"
                      . ' public function set' . ucfirst($camelCase)
                      . '($' . $camelCase . ')' . "\n "
                      . "{ \n "
                      . '$this->_' . $camelCase . ' = $' . $camelCase . "; \n"
                      . ' return $this;'
                      . " \n } \n ";
          }
      }

      /**
       * generateCodeForEntity
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param type $tableName
       * @return type
       */
      protected function generateCodeForEntity($tableName)
      {
          //Mark this table as current
          $this->setCurrentEntity($tableName);
          $this->getCurrentTableShema($tableName);
          $this->generateModelVars();
          $this->generateModelGetters();
          $this->generateModelSetters();

          $dbEntityShemaTpl =
                  $this->replaceTokens('%current_entity', $this->_currentEntity, $this->getDbEntityShemaTpl());

          $attribute = $this->attributeToText();
          $dbEntityShemaTpl =
                  $this->replaceTokens('%attributes', $attribute, $dbEntityShemaTpl);

          $dbEntityShemaTpl =
                  $this->replaceTokens('%table_name', $tableName, $dbEntityShemaTpl);

          $dbEntityTpl =
                  $this->replaceTokens('%current_entity', addslashes($this->_currentEntity), $this->getDbEntityTpl());
          $dbEntityTpl =
                  $this->replaceTokens('%current_vars_entity', $this->_modelVars, $dbEntityTpl);
          $dbEntityTpl =
                  $this->replaceTokens('%current_setters_entity', $this->_modelSetters, $dbEntityTpl);
          //echo $this->_modelSetters;
          $dbEntityTpl =
                  $this->replaceTokens('%current_getters_entity', $this->_modelGetters, $dbEntityTpl);

          $dbTableTpl =
                  $this->replaceTokens('%table_name', $tableName, $this->getDbTableTpl());
          $dbTableTpl =
                  $this->replaceTokens('%current_entity', $this->_currentEntity, $dbTableTpl);
          $entityTpl =
                  $this->replaceTokens('%current_entity', $this->_currentEntity, $this->getEntityTpl());
          $mapperTpl =
                  $this->replaceTokens('%current_entity', $this->_currentEntity, $this->getMapperTpl());
          return array($dbEntityShemaTpl, $dbEntityTpl, $dbTableTpl, $entityTpl, $mapperTpl);
      }

      /**
       * Check if persistent folder exist if not we create it
       * @author amine <amine.cherif@nicetowebyou.com>
       *
       */
      protected function manageStructure()
      {
          if (!is_dir('../application/models')) {
              //create persistent directorie
              mkdir('../application/models');
          }
          if (!is_dir('../application/models/persistent')) {
              //create persistent directorie
              mkdir('../application/models/persistent');
          }
          if (!is_dir('../application/models/DbTable')) {
              //create DbTable directorie
              mkdir('../application/models/DbTable');
          }
          if (!is_dir('../application/models/Entity')) {
              //create DbTable directorie
              mkdir('../application/models/Entity');
          }
          if (!is_dir('../application/models/Mapper')) {
              //create DbTable directorie
              mkdir('../application/models/Mapper');
          }
      }

      /**
       * Create DbEntityDbShema.php file
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param string $dbEntityShemaTpl
       */
      protected function createDbEntityDbShema($dbEntityShemaTpl)
      {
          $fileName = '../application/models/persistent/' . $this->_currentEntity . 'DbMapper.php';
          file_put_contents($fileName, $dbEntityShemaTpl);
      }

      /**
       * Create dbEntity.php file
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param string $dbEntityTpl
       */
      protected function createDbEntity($dbEntityTpl)
      {
          $fileName = '../application/models/persistent/' . $this->_currentEntity . 'DbEntity.php';
          file_put_contents($fileName, $dbEntityTpl);
      }

      /**
       * Create Model Entity file
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param string $ModelEntityTpl
       */
      protected function createEntity($ModelEntityTpl)
      {
          $fileName = '../application/models/Entity/' . $this->_currentEntity . '.php';
          if (!file_exists($fileName)) {
              file_put_contents($fileName, $ModelEntityTpl);
          }
      }

      /**
       * Create Model Mapper file
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param string $ModelMapperTpl
       */
      protected function createMapper($ModelMapperTpl)
      {
          $fileName = '../application/models/Mapper/' . $this->_currentEntity . '.php';
          if (!file_exists($fileName)) {
              file_put_contents($fileName, $ModelMapperTpl);
          }
      }

      /**
       * Create dbTable.php file
       * @author amine <amine.cherif@nicetowebyou.com>
       * @param string $dbTableTpl
       */
      protected function createDbTable($dbTableTpl)
      {
          $fileName = '../application/models/DbTable/' . $this->_currentEntity . '.php';
          file_put_contents($fileName, $dbTableTpl);
      }

      /**
       * toCamelCase
       */
      protected function toCamelCase($str)
      {
          $strNoUnderScore = str_replace('_', ' ', trim($str));
          $strCapitalize = ucwords($strNoUnderScore);             // Hello World
          $strNoblanks = str_replace(' ', '', trim($strCapitalize));  // HelloWorld
          $strCamelCase = lcfirst($strNoblanks); // helloWorld
          return $strCamelCase;
      }

  }