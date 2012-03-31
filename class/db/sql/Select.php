<?php 
namespace org\jecat\framework\db\sql ;

use org\jecat\framework\db\sql\parser\BaseParserFactory;

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
	
	/**
	 * 向Select对像 添加多个返回字段。
	 * 可以传入多个参数，每个参数是一个或一组返回字段：
	 * 如果参数类型为字符串，则做为字段名称; 
	 * 如果参数类型为数组，则数组里的字符串类型的键名做为别名，值做为字段名
	 */
	public function addColumns($columnName/* ... */)
	{
		$arrRawColumns =& $this->rawClause(self::CLAUSE_SELECT) ;
		
		foreach (func_get_args() as $column)
		{
			if( is_array($column) and !empty($column) )
			{
				$nIdx = 0 ;
				foreach($column as $key=>&$sColumnName)
				{
					if( $nIdx++ or $arrRawColumns['subtree'] )
					{
						$arrRawColumns['subtree'][] = ',' ;
					}
					$arrRawColumns['subtree'][] = self::createRawColumn(null,$sColumnName,is_string($key)?$key:null) ;
				}
			}
			else
			{
				if(!$arrRawColumns['subtree'])
				{
					$arrRawColumns['subtree'][] = ',' ;
				}
				$arrRawColumns['subtree'][] = self::createRawColumn(null,(string)$column) ;
			}
		}
		
		return $this ;
	}
	
	/**
	 * 向Select对像 添加多个返回字段。
	 */
	public function addColumn($sClmName,$sAlias=null,$sTable=null,$sDB=null)
	{		
		if( is_string($sClmName) )
		{
			$arrRawColumns =& $this->rawClause(self::CLAUSE_SELECT) ;
			
			if( $arrRawColumns['subtree'] )
			{
				$arrRawColumns['subtree'][] = ',' ;
			}
			
			$arrRawColumns['subtree'][] = self::createRawColumn($sTable,$sClmName,$sAlias,$sDB) ;
		}
		
		// 未知类型
		else
		{
			throw new Exception("参数类型无效") ;
		}
		
		return $this ;
	}
	
	/**
	 * 以sql表达式的形式，向select对像添加一个或多个返回字段。
	 */
	public function addColumnsExpr($sExpression)
	{
		$arrSubTree = BaseParserFactory::singleton()->create(true,null,'select')
				->parse($sExpression,true) ;
		if( !empty($arrSubTree) )
		{
			$arrRawColumns =& $this->rawClause(self::CLAUSE_SELECT) ;
			
			if( $arrRawColumns['subtree'] )
			{
				$arrRawColumns['subtree'][] = ',' ;
			}
			
			$arrRawColumns['subtree'] = array_merge($arrRawColumns['subtree'],$arrSubTree) ;
		}
		
		return $this ;
	}
	
	public function clearColumns()
	{
	    $this->arrRawSql[self::CLAUSE_SELECT]['subtree'] = array() ;
	    return $this ;
	}
	
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
