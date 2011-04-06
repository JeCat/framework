<?php 

namespace jc\db\sql ;


abstract class StatementBase implements ISQLStatement
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