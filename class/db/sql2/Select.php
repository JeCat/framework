<?php 
namespace org\jecat\framework\db\sql2 ;

use org\jecat\framework\lang\Exception;

class Select extends MultiTableSQL 
{
	const PREDICATE_DEFAULT = '' ;
	const PREDICATE_ALL = 'ALL' ;
	const PREDICATE_DISTINCT = 'DISTINCT' ;
	const PREDICATE_DISTINCTROW = 'DISTINCTROW' ;
	const PREDICATE_TOP = 'TOP' ;
	/*
	public function makeStatementForCount($sCntClmName='rowCount',$sColumn='*',StatementState $aState)
	{
		$aState->setSupportLimitStart(true)
				->setSupportTableAlias(true) ;
		
		$this->checkValid(true) ;
		
		return "SELECT"
			. $this->makeStatementPredicate($aState)
			. " count({$sColumn}) AS {$sCntClmName} "
			. parent::makeStatement($aState)
			. ' ;' ;
	}*/
	/*
	public function makeStatement(StatementState $aState)
	{
		$aState->setSupportLimitStart(true)
				->setSupportTableAlias(true) ;
	
		$this->checkValid(true) ;
		
		return "SELECT"
			. $this->makeStatementPredicate($aState)
			. ' ' . ($this->arrColumns? implode(',', $this->arrColumns): '*')
			. parent::makeStatement($aState)
			. ' ;' ;
	}

	public function makeStatementPredicate(StatementState $aState)
	{
		return ' ' . $this->sPredicate . (
				$this->sPredicate==self::PREDICATE_TOP?
					" " . $this->nPredicateTopLen . (
							$this->bPredicateTopPercent?
								' PERCENT': ''
					): ''
		) ;
	}*/
		
	//public function setPredicateTop($nLength=30,$bPercent=false)
	//{}
	
	public function addColumns()
	{
		
	}
	
	public function addColumn($sClmName,$sAlias=null)
	{
		if( is_string($sClmName) )
		{
			$this->arrRawColumns[] = self::createRawColumn($sClmName,$sAlias) ;
		}
		
		// 未知类型
		else
		{
			throw new Exception("参数类型无效") ;
		}
	}
	
	public function clearColumns()
	{
	    $this->arrRawColumns = null;
	}
	
	private $arrRawColumns = null ;
	
	
	/**
	 * Enter description here ...
	 * 
	 * @var string
	 */
	private $sPredicate = self::PREDICATE_DEFAULT ;

	/**
	 * Enter description here ...
	 * 
	 * @var bool
	 */
	private $bPredicateTopPercent = false ;
	
	/**
	 * Enter description here ...
	 * 
	 * @var int
	 */
	private $nPredicateTopLen = 30 ;
}

?>
