<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/ 
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
					$arrRawColumns['subtree'][] = self::createRawColumn(null,$sColumnName,is_string($key)?$key:null,true) ;
				}
			}
			else
			{
				if(!$arrRawColumns['subtree'])
				{
					$arrRawColumns['subtree'][] = ',' ;
				}
				$arrRawColumns['subtree'][] = self::createRawColumn(null,(string)$column,null,true) ;
			}
		}
		
		return $this ;
	}
	
	/**
	 * 向Select对像 添加多个返回字段。
	 */
	public function addColumn($sClmName,$sAlias=null,$sTable=null)
	{		
		if( is_string($sClmName) )
		{
			$arrRawColumns =& $this->rawClause(self::CLAUSE_SELECT) ;
			
			if( $arrRawColumns['subtree'] )
			{
				$arrRawColumns['subtree'][] = ',' ;
			}
			
			$arrRawColumns['subtree'][] = self::createRawColumn($sTable,$sClmName,$sAlias,true) ;
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

