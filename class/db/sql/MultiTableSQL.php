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

use org\jecat\framework\lang\Exception;

abstract class MultiTableSQL extends SQL
{
	function __construct($sTableName=null,$sTableAlias=null)
	{
		$this->arrRawSql = array(
				'expr_type' => 'query' ,
				'subtree' => array() ,
		) ;
		if($sTableName)
		{
			$this->addTable($sTableName,$sTableAlias) ;
		}
	}
	
	function __clone()
	{}
		
	// -- from --
	
	/**
	 * 参数 $table 可以是一个表示表名的字符串，也可以是一个 Table 对像 
	 * 
	 * @param $table	string,Table
	 */
	public function addTable($table,$sAlias=null)
	{
		if( is_string($table) )
		{
			$arrRawTable = self::createRawTable($table,$sAlias) ;
		}
		
		else if( $table instanceof Table )
		{
			$sAlias = $table->alias() ;
			$arrRawTable =& $table->rawSql() ;
		}
		
		else if( $table instanceof Select )
		{
			
		}
		
		// 未知类型
		else
		{
			throw new Exception("参数类型无效") ;
		}
		
		$arrRawFrom =& $this->rawClause($this->nTablesClause) ;
		
		if($sAlias)
		{
			$arrRawFrom['subtree'][$sAlias] =& $arrRawTable ;
		}
		else
		{
			$arrRawFrom['subtree'][] =& $arrRawTable ;
		}
	}
	
	static private function tableFromRaw(array & $arrRawTable)
	{
		if(!isset($arrRawTable['object']))
		{
			switch ( $arrRawTable['expr_type'] )
			{
				case 'table':
					$arrRawTable['object'] = new Table() ;
					break ;
				case 'subquery':
					$arrRawTable['object'] = new Select() ;
					break ;
				default :
					throw new Exception("遇到无效的 table 类型： %s",$arrRawTable['expr_type']) ;
				break ;
			}
	
			$arrRawTable['object']->setRawSql($arrRawTable) ;
		}
	
		return $arrRawTable['object'] ;
	}
	
	/**
	 * 返回指定数据表名所属的subtree数组，并加索引改为表名
	 */
	protected function & findTableRaw($sTableName,array&$arrRawTree)
	{		
		// 先用序号直接查找
		if( isset($this->arrRawSql['tables'][$sTableName]) )
		{
			return $this->arrRawSql['tables'][$sTableName] ;
		}
		
		foreach($arrRawTree as &$rawToken)
		{
			if( !is_array($rawToken) )
			{
				continue ;
			}
			else if( $rawToken['expr_type'] == 'table' )
			{
				if( $sTableName === (isset($rawToken['as'])? $rawToken['as']: $rawToken['table']) )
				{
					$this->arrRawSql['tables'][$sTableName] =& $rawToken ;
					return $rawToken ;
				}
			}
			else if( !empty($rawToken['subtree']) )
			{
				if( $arrNameToken =& $this->findNameRaw($sName,$rawToken['subtree'],$sType) )
				{
					return $arrNameToken ;
				}
			}
		}
		
		return $null = null ;
	}

	public function joinTable($sFromTable,$sToTable,$sAlias=null,$on=null,$using=null,$sJoinType='LEFT')
	{
		$arrTokens = array() ;
		if($on)
		{
			array_push($arrTokens,'ON','(',$on,')') ;
		}
		if($using)
		{
			array_push($arrTokens,'USING','(',$using,')') ;
		}
		
		return $this->_joinTableRaw($sFromTable,$sToTable,$sAlias,$sJoinType,$arrTokens) ;
	}
	
	public function _joinTableRaw($sFromTable,$sToTable,$sAlias=null,$sJoinType='LEFT',array $arrRawTokens=array())
	{
		$arrRawFrom =& $this->rawClause($this->nTablesClause) ;
		if( !$arrFromTableToken=&$this->findTableRaw($sFromTable,$arrRawFrom['subtree']) )
		{
			throw new Exception("名为 %s 的数据表不存在，无法在该数据表上 join 另一个数据表。",$sFromTable) ;
		}
		
		array_unshift($arrRawTokens, $sJoinType,'JOIN','(',self::createRawTable($sToTable,$sAlias),')') ;
		
		$arrJoinToken = array(
				'expr_type' => 'join_expression' ,
				'type' => $sJoinType ,
				'subtree' => &$arrRawTokens ,
		) ;
		$arrFromTableToken['subtree'][] =& $arrJoinToken ;
		
		return $this ;
	}
	
	public function clearTables()
	{
		unset($this->arrRawSql[$this->nTablesClause]) ;
	}
	
	/**
	 * @return array[Table]
	 */
	public function tableIterator()
	{
		return isset($this->arrRawSql[$this->nTablesClause])?
				new \ArrayIterator(self::allTables($this->arrRawSql[$this->nTablesClause])) :
				new \EmptyIterator() ;
	}
	
	static private function allTables(&$arrTableList)
	{
		$arrTables = array() ;
		foreach($arrTableList as &$arrRawTable)
		{
			if( $arrRawTable['expr_type'] == 'table_expression' )
			{
				$arrTables = array_merge($arrTables,self::allTables($arrRawTable['subtree'])) ;
			}
			else
			{
				$arrTables[] = self::tableFromRaw($arrRawTable) ;
			}
		}
		
		return $arrTables ;
	}
	
	/**
	 * @return array[Table]
	 */
	public function table($sAlias)
	{
		if( !$arrTableList =& self::findTableList($sAlias,$this->rawClause($this->nTablesClause)) )
		{
			return null ;
		}
		return self::tableFromRaw($arrTableList[$sAlias]) ;
	}
	
	// ----------
	
	/**
	 * @return Criteria
	 */
	public function criteria($bAutoCreate=true)
	{
		if( !$this->aCriteria and $bAutoCreate )
		{
			$this->aCriteria = new Criteria($this->rawSql()) ;
		}
		return $this->aCriteria ;
	}

	/**
	 * @return Criteria
	 */
	public function setCriteria(Criteria $aCriteria)
	{
		$aCriteria->attache($this->rawSql()) ;
	}
	/**
	 * @return Restriction
	 */
	public function where($bAutoCreate=true)
	{
		return $this->criteria($bAutoCreate)->where($bAutoCreate) ;
	}

	private $aCriteria ;
	
	protected $nTablesClause = SQL::CLAUSE_FROM ;
}


