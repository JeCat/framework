<?php 
namespace org\jecat\framework\db\sql ;

use org\jecat\framework\lang\Type;
use org\jecat\framework\util\HashTable;
use org\jecat\framework\lang\Exception;

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
		Type::check(array('string','org\\jecat\\framework\\db\\sql\\Table'),$table) ;
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

	public function makeStatement(StatementState $aState)
	{
		$sStatement = '' ;
		
		$sStatement.= " FROM" . $this->makeStatementTableList($aState) ;
	
		$sStatement.= $this->makeStatementCriteria($aState) ;
		
		return $sStatement ;
	}
	
	protected function makeStatementTableList(StatementState $aState)
	{
		foreach($this->arrTables as $table)
		{
			$arrTables = array() ;
			foreach( $this->arrTables as $table )
			{
				$arrTables[] = ($table instanceof Table)? $table->makeStatement($aState): "`{$table}`" ;
			}
			
			return ' ' . implode(", ",$arrTables) ;
		}
	}
	
	protected function makeStatementCriteria(StatementState $aState)
	{
		return $this->aCriteria? $this->aCriteria->makeStatement($aState): '' ;
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