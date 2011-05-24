<?php 

namespace jc\db\sql ;


use jc\lang\Object;

abstract class Statement extends Object implements ISQLStatement
{
	public function tableNameFactory()
	{
		if(!$this->aTableNameFactory)
		{
			$this->aTableNameFactory = TableNameFactory::singleton() ;
		}
		return $this->aTableNameFactory ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @var ITableNameFactory
	 */
	private $aTableNameFactory = null ;
}


?>