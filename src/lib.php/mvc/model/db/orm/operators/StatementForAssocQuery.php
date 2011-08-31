<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\db\sql\IStatement;
use jc\mvc\model\db\orm\Association;
use jc\db\sql\TablesJoin;
use jc\lang\Exception;
use jc\db\sql\Table;
use jc\mvc\model\db\orm\PrototypeInFragment;

abstract class StatementForAssocQuery implements IStatement 
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
			$this->realStatement()->addTable( $aPrototype->sqlTable() ) ;
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
	
	abstract public function realStatement() ;
	
	private $aPrototype ;
	
	private $sSql ;
}

?>