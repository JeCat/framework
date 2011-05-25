<?php 

namespace jc\db\sql ;

use jc\util\HashTable;

use jc\lang\Exception;

abstract class MultiTableStatement extends Statement
{
	public function __construct($sTableName="")
	{
		$this->aTables = new Tables($this,$sTableName) ;
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
		$sTableName = $this->tableNameFactory()->tableName($sInputName) ;
		
		if($bAlias)
		{
			return $this->tables()->tableNameAliases()->get($sTableName)?: $sTableName ;
		}
		else
		{
			return  $sTableName ;
		}
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
	 * @var Tables
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