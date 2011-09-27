<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\db\sql\Statement;
use jc\mvc\model\db\orm\Association;
use jc\lang\Exception;
use jc\mvc\model\db\orm\PrototypeInFragment;

abstract class StatementForAssocQuery extends Statement 
{
	public function __construct(PrototypeInFragment $aPrototype)
	{
		$this->aPrototype = $aPrototype ;
	}
	
	public function makeStatement($bFormat=false)
	{
		if(!$this->sSql)
		{
			$this->preprocessMakeStatement($this->aPrototype) ;
			
			$this->sSql = $this->realStatement()->makeStatement($bFormat) ;
		}
		
		return $this->sSql ;
	}

	public function checkValid($bThrowException=true)
	{
		return $this->realStatement()->checkValid($bThrowException) ;
	}
	
	protected function preprocessMakeStatement(PrototypeInFragment $aPrototype)
	{
		// 未被其他原型单属关联，做为"片段"的起点
		if( !$aAssocBy=$aPrototype->associateBy() or !$aAssocBy->isOneToOne() )
		{
			$aStatement = $this->realStatement() ;
			$aStatement->addTable( $aPrototype->sqlTable() ) ;
		}
	}
	
	
	public function __call($sMethod,$arrArgs=array())
	{
		return call_user_func_array(array($this->realStatement(),$sMethod), $arrArgs) ;
	}
	
	
	public function prototype()
	{
		return $this->aPrototype ;
	}

	/**
	 * @return jc\db\sql\Statement
	 */
	public function realStatement()
	{
		if(!$this->aStatement)
		{
			$this->aStatement = $this->createRealStatement() ;
		}
		
		return $this->aStatement ;
	}
	
	/**
	 * @return jc\db\sql\Statement
	 */
	abstract public function createRealStatement() ;
	
	private $aPrototype ;
	
	private $sSql ;
	
	private $aStatement ;
}

?>