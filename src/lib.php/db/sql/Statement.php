<?php 

namespace jc\db\sql ;


use jc\lang\Object;

abstract class Statement extends Object implements IStatement
{
	public function tableNameFactory()
	{
		if(!$this->aTableNameFactory)
		{
			$this->aTableNameFactory = TableNameFactory::singleton() ;
		}
		return $this->aTableNameFactory ;
	}

	public function setTableNameFactory(ITableNameFactory $aFactory)
	{
		$this->aTableNameFactory = $aFactory ;
	}
		
	/**
	 * Enter description here ...
	 * 
	 * @var ITableNameFactory
	 */
	private $aTableNameFactory = null ;
}


?>