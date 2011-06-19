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
			$this->aCriteria = new Criteria($this) ;
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
			$sStatement.= " FROM " . $this->aTables->makeStatement($bFormat) ;
		}
	
		if($this->aCriteria)
		{
			$sStatement.= " WHERE " . $this->aCriteria->makeStatement($bFormat) ;
		}
				
		return $sStatement ;
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
	
	public function setLimit($nLen=null)
	{
		if($nLen!==null)
		{
			$this->nLimitLen = intval($nLen) ;
		}
	}
	
	public function limitLen()
	{
		return $this->nLimitLen ;
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
	
	private $nLimitLen = null ;
	
}

?>