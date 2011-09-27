<?php 

namespace jc\db\sql ;

use jc\system\Application;

use jc\lang\Exception;
use jc\db\sql\Criteria;

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
	
	public function makeStatementForCount($sCntClmName='rowCount',$bFormat=false)
	{
		$this->checkValid(true) ;
		
		return "SELECT"
			. $this->makeStatementPredicate($bFormat)
			. " count(*) AS {$sCntClmName} "
			. parent::makeStatement($bFormat)
			. ' ;' ;
	}
	
	public function makeStatement($bFormat=false)
	{
		$this->checkValid(true) ;
		
		return "SELECT"
			. $this->makeStatementPredicate($bFormat)
			. ' ' . ($this->arrColumns? implode(',', $this->arrColumns): '*')
			. parent::makeStatement($bFormat)
			. ' ;' ;
	}

	public function makeStatementPredicate($bFormat=false)
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
		/*if( !$this->aColumns )
		{
			if($bThrowException)
			{
				throw new Exception("对象尚未准备好：没有设置返回字段。") ;
			}
			return false ;
		}*/
		
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
	
	public function createCriteria(){
		$aCriteria = parent::createCriteria() ;
		$aCriteria->setEnableLimitStart(true) ;
		return $aCriteria;
	}
	
	private $arrColumns = array() ;
	
	
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