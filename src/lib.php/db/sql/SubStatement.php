<?php
namespace jc\db\sql ;

class SubStatement extends Statement
{
	public function __construct(Statement $aStatement)
	{
		parent::__construct() ;
		
		$this->setStatement($aStatement) ;
	}
	
	public function tableNameFactory()
	{
		return $this->statement()->tableNameFactory() ;
	}
	
	public function setTableNameFactory(ITableNameFactory $aFactory)
	{
		// nothing todo
	}

	/**
	 * @return Statement
	 */
	public function statement()
	{
		return $this->aStatement ;
	}
	
	public function setStatement(Statement $aStatement)
	{
		$this->aStatement = $aStatement ;
	}
	
	
	private $aStatement ;
}

?>