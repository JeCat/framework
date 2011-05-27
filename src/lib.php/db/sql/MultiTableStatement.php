<?php 

namespace jc\db\sql ;

use jc\util\HashTable;

use jc\lang\Exception;

abstract class MultiTableStatement extends Statement
{
	public function __construct($sTableName=null,$sTableAlias=null)
	{
		$this->aTables = new Tables($this,$sTableName,$sTableAlias) ;
	}
	
	/** 
	 * @return Tables
	 */
	public function tables()
	{
		return $this->aTables ;
	}

	public function setTables(Tables $aTables)
	{
		$this->aTables = $aTables ;
	}
	
	public function realTableName($sInputName,$bAlias=false)
	{
		return $this->tableNameFactory()->tableName($sInputName) ;
	}
	
	/**
	 * @return Criteria
	 */
	public function criteria()
	{
		if(!$this->aCriteria)
		{
			$this->aCriteria = new Criteria() ;
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
	
	public function makeStatementLimit($bFormat=false)
	{
		return " LIMIT " . $this->nLimitFrom . ", " . $this->nLimitLen ;
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
	
	public function setLimit($nLen=null,$nFrom=null)
	{
		if($nLen!==null)
		{
			$this->nLimitLen = intval($nLen) ;
		}
		if($nFrom!==null)
		{
			$this->nLimitFrom = intval($nFrom) ;
		}
	}
	
	public function limitLen()
	{
		return $this->nLimitLen ;
	}
	
	public function limitFrom()
	{
		return $this->nLimitFrom ;
	}
	
	/**
	 * @var Tables
	 */
	private $aTables = null ;
	
	/**
	 * Enter description here ...
	 * 
	 * @var Criteria
	 */
	private $aCriteria = null ;
	
	private $nLimitFrom = 0 ;
	
	private $nLimitLen = 30 ;
	
}

?>