<?php 

namespace jc\db\sql ;

use jc\lang\Exception;

abstract class MultiTableStatement extends StatementBase
{
	public function __construct($sTableName="")
	{
		$this->aTables = new Tables($sTableName) ;
	}

	public function tableNameFactory()
	{
		return $this->aTableNameFactory->tableNameFactory() ;
	}
	public function setTableNameFactory(ITableNameFactory $aFactory)
	{
		$this->aTables->setTableNameFactory($aFactory) ;
	}
	
	/** 
	 * @return ISQLStatementFrom
	 */
	public function tables()
	{
		return $this->aTables ;
	}

	public function setTables(ISQLStatementFrom $aTables)
	{
		$this->aTables = $aTables ;
	}
	
	/**
	 * @return Criteria
	 */
	public function criteria()
	{
		return $this->aCriteria ;
	}

	public function setCriteria(Criteria $aCriteria)
	{
		$this->aCriteria = $aCriteria ;
	}
	
	
	
	public function makeStatement($bFormat=false)
	{
		$sStatement = '' ;
		
		if($this->aTables)
		{
			$sStatement.= " " . $this->aTables->makeStatement($bFormat) ;
		}
	
		if($this->aCriteria)
		{
			$sStatement.= " " . $this->aCriteria->makeStatement($bFormat) ;
		}
		
		return $sStatement ;
	}
	
	public function checkValid($bThrowException=true)
	{
		if( !$this->aTables )
		{
			if($bThrowException)
			{
				throw new Exception("对象尚未准备就绪：".__CLASS__."对象没有设置数据表(db table)") ;
			}
			
			return false ;
		}
		
		return $this->aTables->checkValid($bThrowException) ;
	}
	
	/**
	 * @var ISQLStatementFrom
	 */
	private $aTables = null ;
	
	/**
	 * Enter description here ...
	 * 
	 * @var Criteria
	 */
	private $aCriteria = null ;
}

?>