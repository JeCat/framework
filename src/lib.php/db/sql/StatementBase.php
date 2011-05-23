<?php 

namespace jc\db\sql ;


use jc\lang\Object;

abstract class StatementBase extends Object implements ISQLStatement
{
	public function tableNameFactory()
	{
		return $this->aTableNameFactory ;
	}
	
	public function setTableNameFactory(ITableNameFactory $aFactory)
	{
		$this->aTableNameFactory = $aFactory ;
	}
	
	public function createTableName($sTableName)
	{
		return $this->aTableNameFactory? $this->aTableNameFactory->createTableName($sTableName): $sTableName ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var ITableNameFactory
	 */
	private $aTableNameFactory = null ;
}


?>