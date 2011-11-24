<?php
namespace org\jecat\framework\db\sql ;

use org\jecat\framework\db\sql\name\NameTransferFactory;
use org\jecat\framework\db\sql\name\NameTransfer;
use org\jecat\framework\lang\Object;

class StatementFactory extends Object
{
	// Statement /////////////////////
	
	/**
	 * @return Select
	 */
	public function createSelect($sTableName=null,$sTableAlias=null)
	{
		return $this->initStatement(
			new Select($sTableName,$sTableAlias)
		) ;
	}
	
	/**
	 * @return Delete
	 */
	public function createDelete($sTableName=null)
	{
		return $this->initStatement(
			new Delete($sTableName)
		) ;
	}
	
	/**
	 * @return Insert
	 */
	public function createInsert($sTableName="")
	{
		return $this->initStatement(
			new Insert($sTableName)
		) ;
	}
	
	/**
	 * @return Update
	 */
	public function createUpdate($sTableName=null,$sTableAlias=null)
	{
		return $this->initStatement(
			new Update($sTableName,$sTableAlias)
		) ;
	}
	
	
	// SubStatement /////////////////////

	/**
	 * @return Criteria
	 */
	public function createCriteria(Restriction $aRestriction = null)
	{
		return $this->initStatement(
			new Criteria($aRestriction)
		) ;
	}

	/**
	 * @return Order
	 */
	public function createOrder($sColumn=null , $bOrderType=true)
	{
		return $this->initStatement(
			new Order($sColumn,$bOrderType)
		) ;
	}

	/**
	 * @return Restriction
	 */
	public function createRestriction($bLogic=true)
	{
		return $this->initStatement(
			new Restriction($bLogic)
		) ;
	}

	/**
	 * @return Table
	 */
	public function createTable($sTableName,$sAlias=null)
	{
		return $this->initStatement(
			new Table($sTableName,$sAlias)
		) ;
	}

	/**
	 * @return TablesJoin
	 */
	public function createTablesJoin($sType=TablesJoin::JOIN_LEFT)
	{
		return $this->initStatement(
			new TablesJoin($sType)
		) ;
	}
	
	
	//////////////////

	
	/**
	 * @return org\jecat\framework\db\sql\name\NameTransferFactory
	 */
	public function createNameTransferFactory()
	{
		return NameTransferFactory::singleton() ;
	}

	/**
	 * @return org\jecat\framework\db\sql\name\NameTransfer
	 */
	public function nameTransfer()
	{
		return $this->aNameTransfer ;
	}
	
	public function setNameTransfer(NameTransfer $aNameTransfer=null)
	{
		$this->aNameTransfer = $aNameTransfer ;
	}
	
	
	
	protected function initStatement(Statement $aStatement)
	{
		$aStatement->setNameTransfer($this->nameTransfer()) ;
		$aStatement->setStatementFactory($this) ;
		
		return $aStatement ;
	}
	
	
	
	private $aNameTransfer ;
}

?>