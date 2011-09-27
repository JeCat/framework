<?php
namespace jc\db\sql ;

abstract class SubStatement extends Statement
{
	final public function __construct(Statement $aStatement=null)
	{
		$this->aStatement = $aStatement ;
	}
	
	public function setStatement(Statement $aStatement=null)
	{
		$this->aStatement = $aStatement ;
	}
	
	/**
	 * @return Statement
	 */
	public function statement()
	{
		return $this->aStatement ;
	}

	/**
	 * @return jc\db\sql\name\NameTransfer
	 */
	public function nameTransfer()
	{
		return parent::nameTransfer(false)?: (
			$aStatement=$this->statement()? $aStatement->nameTransfer(true): null
		) ;
	}
	
	private $aStatement ;
}

?>