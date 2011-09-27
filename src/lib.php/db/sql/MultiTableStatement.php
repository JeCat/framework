<?php 
namespace jc\db\sql ;

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
	
	public function addTable($aTable)
	{
		$this->arrTables[] = $aTable ;
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
	
		if($this->aCriteria)
		{
			$sStatement.= $this->aCriteria->makeStatement($bFormat) ;
		}
		
		return $sStatement ;
	}
	
	public function makeStatementTableList($bFormat=false)
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