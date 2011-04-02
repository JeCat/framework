<?php 

namespace jc\db\sql ;

use jc\lang\Exception;

class Select extends MultiTableStatement 
{
	const PREDICATE_DEFAULT = '' ;
	const PREDICATE_ALL = 'ALL' ;
	const PREDICATE_DISTINCT = 'DISTINCT' ;
	const PREDICATE_DISTINCTROW = 'DISTINCTROW' ;
	const PREDICATE_TOP = 'TOP' ;
	
	public function makeStatement($bFormat=false)
	{
		return "SELECT"
			. $this->makeStatementPredicate($bFormat)
			. $this->aColumns? $this->aColumns->makeStatement(): ' *'
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
	
	/**
	 * Enter description here ...
	 * 
	 * @var Columns
	 */
	private $aColumns = null ;
	
	
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