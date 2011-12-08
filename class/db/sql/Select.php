<?php 

namespace org\jecat\framework\db\sql ;

use org\jecat\framework\system\Application;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\db\sql\Criteria;

class Select extends MultiTableStatement 
{
	const PREDICATE_DEFAULT = '' ;
	const PREDICATE_ALL = 'ALL' ;
	const PREDICATE_DISTINCT = 'DISTINCT' ;
	const PREDICATE_DISTINCTROW = 'DISTINCTROW' ;
	const PREDICATE_TOP = 'TOP' ;

	public function __construct($sTableName=null,$sTableAlias=null)
	{
		parent::__construct($sTableName,$sTableAlias) ;
		
		$this->criteria()->setLimit(30) ;
	}
	
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
	}
	
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
	}
	
	public function checkValid($bThrowException=true)
	{
		return true ;
	}
	
	public function setPredicateTop($nLength=30,$bPercent=false)
	{
		
	}
	
	public function addColumn($sClmName,$sAlias=null)
	{
		if($sAlias)
		{
			$sClmName.= " AS '".$sAlias."'" ;
		}
		$this->arrColumns[] = $sClmName ;
	}
	
	public function clearColumns()
	{
	    $this->arrColumns = null;
	}
	
	private $arrColumns = null ;
	
	
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
