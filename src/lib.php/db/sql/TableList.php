<?php 
namespace jc\db\sql ;

use jc\util\HashTable;
use jc\lang\Exception;

class TableList extends SubStatement
{
	public function __construct($table) 
	{
		$this->arrTables[] = $table ;
	}

	public function makeStatement($bFormat=false)
	{
		$arrTables = array() ;
		foreach( $this->arrTables as $table )
		{
			$arrTables[] = ($table instanceof Table)? $table->makeStatement($bFormat): "`{$table}`" ;
		}
		
		return implode(", ",$arrTables) ;
	}
	
	public function checkValid($bThrowException=true)
	{
		if( empty($this->sTableName) )
		{
			if($bThrowException)
			{
				throw new Exception("对象尚未准备就绪：还没有设置数据表") ;
			}
			return false ;
		}
		return true ;		
	}

	/**
	 * Enter description here ...
	 * 
	 * @var array
	 */
	private $arrTables = array() ;
	
}

?>