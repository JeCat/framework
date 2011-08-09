<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\db\sql\IStatement;
use jc\db\sql\Criteria;
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
			$this->assocPrototype($this->aPrototype) ;
			
			$this->sSql = $this->realStatement()->makeStatement($bFormat) ;
		}
		
		return $this->sSql ;
	}

	public function checkValid($bThrowException=true)
	{
		return $this->realStatement()->checkValid($bThrowException) ;
	}
	
	protected function assocPrototype(PrototypeInFragment $aPrototype)
	{
		// process table in sql
		// ----------------
		$aTable = $aPrototype->sqlTable() ;
		
		// 
		if( $aAssociateBy=$aPrototype->associateBy() and $aAssociateBy->isOneToOne() )
		{
			if( !($aAssociateBy->fromPrototype() instanceof PrototypeInFragment) )
			{
				throw new Exception("orm 片段中的prototype对象必须为 PrototypeInFragment 类") ;
			}
			
			// build sql table join by association
			$aTableJoin = new TablesJoin() ;
			$aTableJoin->addTable($aTable,$this->createAssociationCriteria($aAssociateBy)) ;
			$aAssociateBy->fromPrototype()->sqlTable()->addJoin($aTableJoin) ;
		}
		
		else
		{
			$this->realStatement()->addTable( $aPrototype->sqlTable() ) ;
		}
		
		// process association prototype
		foreach($aPrototype->associations() as $aAssoc)
		{
			if( $aAssoc->isOneToOne() )
			{
				$this->assocPrototype( $aAssoc->toPrototype() ) ;
			}
		}
	}
	
	/**
	 * create criteria object for "join on" 
	 * @return jc\db\sql\Criteria
	 */
	public function createAssociationCriteria(Association $aAssoc)
	{
		$aCriteria = new Criteria() ;
		
		$arrToKeys = $aAssoc->toKeys() ;
		foreach($aAssoc->fromKeys() as $nKeyIdx=>$sFromKey)
		{
			$aCriteria->addExpression(
				$aAssoc->fromPrototype()->columnName($sFromKey)
				. '=' .
				$aAssoc->toPrototype()->columnName($arrToKeys[$nKeyIdx])
			) ;
		}
		
		return $aCriteria ;
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