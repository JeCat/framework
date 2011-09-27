<?php 
namespace jc\db\sql ;

use jc\db\sql\name\NameTransferFactory;
use jc\db\sql\name\NameTransfer;
use jc\lang\Object;

abstract class Statement extends Object
{
	abstract public function makeStatement($bFormat=false) ;
	
	abstract public function checkValid($bThrowException=true) ;
	
	/**
	 * @return jc\db\sql\name\NameTransfer
	 */
	public function nameTransfer($bAutoCreate=true)
	{
		if( !$this->aNameTransfer and $bAutoCreate )
		{
			$this->aNameTransfer = NameTransferFactory::singleton()->create() ;
		}
		
		return $this->aNameTransfer ;
	}
	
	public function setNameTransfer(NameTransfer $aNameTransfer=null)
	{
		$this->aNameTransfer = $aNameTransfer ;
	}
	
	/**
	 * @return jc\db\sql\StatementFactory
	 */
	public function statementFactory($bAutoCreate=true)
	{
		if(!$this->aStatementFactory and $bAutoCreate)
		{
			$this->aStatementFactory = StatementFactory::singleton() ;
		}
		return $this->aStatementFactory ;
	}
	
	public function setStatementFactory(StatementFactory $aStatementFactory=null)
	{
		$this->aStatementFactory = $aStatementFactory ;
	}
	
	private $aNameTransfer ;
	
	private $aStatementFactory ;
}


?>