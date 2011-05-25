<?php
namespace jc\db\sql ;

use jc\util\FilterMangeger;

class TableNameFactory extends FilterMangeger implements ITableNameFactory
{	
	public function tableName($sInputName)
	{
		list($sTableName) = $this->handle($sInputName) ;
		return $sTableName ;
	}
}

?>