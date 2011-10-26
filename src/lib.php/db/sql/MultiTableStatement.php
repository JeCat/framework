<?php 
namespace jc\db\sql ;

use jc\lang\Type;
use jc\util\HashTable;
use jc\lang\Exception;

abstract class MultiTableStatement extends Statement
{
	public function __construct($sTableName=null,$sTableAlias=null)
	{
		if( $sTableName )
		{
			$this->arrTables[] = $sTableAlias?
				new Table($sTableName,$sTableAlias): $sTableName ;
		}
	}
	
	public function addTable($table)
	{
		Type::check(array('string','jc\\db\\sql\\Table'),$table) ;
		$this->arrTables[] = $table ;
	}

	public function clearTables()
	{
		$this->arrTables = array() ;
	}
	
	public function tables()
	{
		return $this->arrTables ;
	}
	
	/**
	 * @return Criteria
	 */
	public function criteria($bAutoCreate=true)
	{
		if( !$this->aCriteria and $bAutoCreate )
		{
			$this->aCriteria = $this->statementFactory()->createCriteria() ;
		}
		return $this->aCriteria ;
	}

	public function setCriteria(Criteria $aCriteria)
	{
		$this->aCriteria = $aCriteria ;
	}

	public function makeStatement($bFormat=false)
	{
		$sStatement = '' ;
		
		$sStatement.= " FROM" . $this->makeStatementTableList($bFormat) ;
	
		$sStatement.= $this->makeStatementCriteria($bFormat) ;
		
		return $sStatement ;
	}
	
	protected function makeStatementTableList($bFormat=false)
	{
		foreach($this->arrTables as $table)
		{
			$arrTables = array() ;
			foreach( $this->arrTables as $table )
			{
				$arrTables[] = ($table instanceof Table)? $table->makeStatement($bFormat): "`{$table}`" ;
			}
			
			return ' ' . implode(", ",$arrTables) ;
		}
	}
	
	protected function makeStatementCriteria($bFormat=false)
	{
		return $this->aCriteria? $this->aCriteria->makeStatement($bFormat,false): '' ;
	}
	
	public function checkValid($bThrowException=true)
	{
		if( !$this->arrTables )
		{
			if($bThrowException)
			{
				throw new Exception("对象尚未准备就绪：".__CLASS__."对象没有设置数据表(db table)") ;
			}
			
			return false ;
		}
		
		return true ;
	}
	
	/**
	 * @return Table
	 */
	public function createTable($sName,$sAlias=null,$bAdd=true)
	{
		$aTable = $this->statementFactory()->createTable($sName,$sAlias) ;
		if($bAdd)
		{
			$this->addTable($aTable) ;
		}
		return $aTable ;
	}
	
	private $arrTables = array() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @var Criteria
	 */
	private $aCriteria = null ;
	
}

?>