<?php 
namespace org\jecat\framework\db\sql2 ;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\lang\Type;

abstract class MultiTableSQL extends SQL
{	
	public function setRawSql(array & $arrRawSql)
	{
		parent::setRawSql($arrRawSql) ;
		
		if( !isset($this->arrRawSql['FROM']) )
		{
			$this->arrRawSql['FROM'] = array() ;
		}
		
		$this->arrRawSqlTables =& $this->arrRawSql['FROM'] ;
		
		return $this ;
	}
	
	public function addTable($table,$sAlias=null)
	{
		if( is_string($table) )
		{
			$this->arrRawSqlTables[] = self::createRawTable($table,$sAlias) ;
		}
		
		// 未知类型
		else
		{
			throw new Exception("参数类型无效") ;
		}
	}

	public function clearTables()
	{
		$this->arrRawSqlTables = array() ;
	}
	
	public function & rawTables()
	{
		return $this->arrRawSqlTables ;
	}	
	
	private $arrRawSqlTables ;
}

?>