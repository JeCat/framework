<?php
namespace jc\db\sql\tablename ;

use jc\util\FilterMangeger;

class TableNameFactory extends FilterMangeger implements ITableNameFactory
{
	public function __construct()
	{
		parent::__construct() ;
		
		$this->add($callback) ;
	}
	
	public function tableName($sInputName)
	{
		return $this->handle($sInputName) ;
	}
}

?>