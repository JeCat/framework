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
	public function criteria()
	{
		if(!$this->aCriteria)
		{
			$this->aCriteria = $this->createCriteria() ;
		}
		return $this->aCriteria ;
	}
	
	public function createCriteria(){
		return new Criteria();
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
			$sStatement.= " WHERE " . $this->aCriteria->makeStatement($bFormat) ;
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
	
	public function makeStatementLimit($bFormat=false)
	{
		if($this->nLimitLen!==null)
		{
			return " LIMIT " . $this->nLimitLen ;
		}
		else
		{
			return '' ;
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
	
	private $arrTables = array() ;
	
	/**
	 * Enter description here ...
	 * 
	 * @var Criteria
	 */
	private $aCriteria = null ;
	
}

?>