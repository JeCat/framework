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

use org\jecat\framework\cache\Cache;
use org\jecat\framework\db\sql\compiler\SqlCompiler;
use org\jecat\framework\lang\Type;
use org\jecat\framework\db\sql\parser\BaseParserFactory;
use org\jecat\framework\lang\Exception;

class SQL
{
	const CLAUSE_SELECT = 1 ;	// SELECT
	const CLAUSE_INSERT = 2 ;	// INSERT
	const CLAUSE_REPLACE = 3 ;	// REPLACE
	const CLAUSE_UPDATE = 4 ;	// UPDATE
	const CLAUSE_DELETE = 5 ;	// DELETE	
	const CLAUSE_FROM = 10 ;	// FROM
	const CLAUSE_WHERE = 15 ;	// WHERE
	const CLAUSE_GROUP = 16 ;	// GROUP
	const CLAUSE_ORDER = 17 ;	// ORDER
	const CLAUSE_LIMIT = 18 ;	// LIMIT
	const CLAUSE_INTO = 21 ;	// INTO
	const CLAUSE_VALUES = 22 ;	// VALUES
	const CLAUSE_SET = 13 ;	// SET
	
	static public $mapClauses = array(
			self::CLAUSE_SELECT => 'SELECT' ,
			self::CLAUSE_INSERT => 'INSERT' ,
			self::CLAUSE_REPLACE => 'REPLACE' ,
			self::CLAUSE_UPDATE => 'UPDATE' ,
			self::CLAUSE_DELETE => 'DELETE' ,
			self::CLAUSE_FROM => 'FROM' ,
			self::CLAUSE_WHERE => 'WHERE' ,
			self::CLAUSE_GROUP => 'GROUP' ,
			self::CLAUSE_ORDER => 'ORDER' ,
			self::CLAUSE_LIMIT => 'LIMIT' ,
			self::CLAUSE_INTO => 'INTO' ,
			self::CLAUSE_VALUES => 'VALUES' ,
			self::CLAUSE_SET => 'SET' ,
	) ;
	static public $mapClausesLower = array(
			self::CLAUSE_SELECT => 'select' ,
			self::CLAUSE_INSERT => 'insert' ,
			self::CLAUSE_REPLACE => 'replace' ,
			self::CLAUSE_UPDATE => 'update' ,
			self::CLAUSE_DELETE => 'delete' ,
			self::CLAUSE_FROM => 'from' ,
			self::CLAUSE_WHERE => 'where' ,
			self::CLAUSE_GROUP => 'group' ,
			self::CLAUSE_ORDER => 'order' ,
			self::CLAUSE_LIMIT => 'limit' ,
			self::CLAUSE_INTO => 'into' ,
			self::CLAUSE_VALUES => 'values' ,
			self::CLAUSE_SET => 'set' ,
	) ;
	
	
	static public function insert($sTableName,$sTableAlias=null)
	{}
	static public function delete($sTableName,$sTableAlias=null)
	{}
	/**
	 * @return Select
	 */
	static public function select($sTableName=null,$sTableAlias=null)
	{
		return new Select($sTableName,$sTableAlias) ;
	}
	static public function update($sTableName,$sTableAlias=null)
	{
		
	}
	
	
	/**
	 * @return Select
	 */
	static public function make($sSql,$factors=null)
	{
		$factors = func_get_args() ;
		$sSql = array_shift($factors) ;
		
		if( !$arrRawSqls=self::parseSql($sSql) or empty($arrRawSqls[0]) )
		{
			return ;
		}
		
		if( isset($arrRawSqls[0]['command']) )
		{
			switch($arrRawSqls[0]['command'])
			{
				case 'SELECT' :
					$aSql = new Select() ;
				case 'INSERT' :
					$aSql = new Insert() ;
				case 'UPDATE' :
					$aSql = new Update() ;
				case 'DELETE' :
					$aSql = new Delete() ;
					break ;
				default:
					$aSql = new SQL() ;
					break ;
			}
		}
		
		else 
		{
			$aSql = new SQL() ;
		}
		
		$aSql->setRawSql($arrRawSqls[0]) ;
		
		if($factors)
		{
			$aSql->addFactors($factors) ;
		}
		
		return $aSql ;
	}
	/**
	 * @return Restriction
	 */
	static public function makeRestriction($sSql,$factors=null)
	{
		$factors = func_get_args() ;
		$sSql = array_shift($factors) ;

		if( !$arrTrees=self::parseSql($sSql,'where') or empty($arrTrees[0]) )
		{
			return ;
		}
		
		$aRestriction = empty($arrTrees[0])? new Restriction(): new Restriction( true, $arrTrees[0] ) ;

		if($factors)
		{
			$aRestriction->addFactors($factors) ;
		}
		
		return $aRestriction ;
	}
	static public function makeColumn($sColumnExpr)
	{
		return self::parseSql($sColumnExpr,'column',true) ;
	}
	
	static public function & parseSql($sStatement,$sParserName='statement',$bReturnFirstTree=false)
	{
		$aCache = Cache::highSpeed() ;
		$sSqlCacheKey = '/db/sql/raw/'.md5($sStatement) ;
		
		// 优先从缓存中查找
		if( !$arrTrees = $aCache->item($sSqlCacheKey) )
		{
			if( !$arrTrees =& BaseParserFactory::singleton()->create(true,null,$sParserName)->parse($sStatement,$bReturnFirstTree) )
			{
				$null = null ;
				return $null ;
			}
				
			// 写入缓存
			$aCache->setItem($sSqlCacheKey,$arrTrees) ;
		}
		
		return $arrTrees ;
	}
	
	
	// ------------
	static public function createRawColumn($sTable,$sName,$sAlias=null,$bReturnedList=false)
	{
		$arrRaw = array(
				'expr_type' => 'column' ,
				'column' => $sName ,
		) ;
		
		if($sTable)
		{
			$arrRaw['table'] = $sTable ;
		}
		if($sAlias)
		{
			$arrRaw['as'] = $sAlias ;
		}
		if($bReturnedList)
		{
			$arrRaw['declare'] = true ;
		}
		
		return $arrRaw ;
	}
	static public function createRawTable($sName,$sAlias=null,$sDB=null)
	{
		$arrRaw = array(
				'expr_type' => 'table' ,
				'table' => $sName ,
		) ;
		
		if($sDB)
		{
			$arrRaw['db'] = $sDB ;
		}
		if($sAlias)
		{
			$arrRaw['as'] = $sAlias ;
		}
		
		return $arrRaw ;
	}
	
    /**
     *
     * 对直接量进行转化,使其在组合后的sql语句中合法.
     * @param mix $value 条件语句中的直接量
     * @return string
     */
	static public function & transValue($value)
    {
    	if (is_string ( $value ))
    	{
    		$value = "'" . addslashes ( $value ) . "'";
    	}
    	else if (is_numeric ( $value ))
    	{
    		$value = "'" .$value. "'";
    	}
    	else if (is_bool ( $value ))
    	{
    		$value = $value ? "'1'" : "'0'";
    	}
    	else if ($value === null)
    	{
    		$value = "NULL";
    	}
    	else
    	{
    		$value = "'" . strval ( $value ) . "'";
    	}
    	
    	return $value ;
    }
	
	/**
	 * @return parser\SqlParser
	 */
	static public function parser()
	{
		return parser\BaseParserFactory::singleton()->create() ;
	}
	/**
	 * @return parser\SqlCompiler
	 */
	static public function compiler()
	{
		return compiler\SqlCompiler::singleton() ;
	}
	
	// ----------------------------------------------------------
	public function __construct(array & $arrRawSql=null)
	{
		if($arrRawSql)
		{
			$this->arrRawSql =& $arrRawSql ;
		}
		else
		{
			$this->arrRawSql = array() ;
		}
	}
	
	/**
	 *
	 * @return string
	 */
	public function toString(SqlCompiler $aSqlCompiler=null)
	{
		if(!$aSqlCompiler)
		{
			$aSqlCompiler = self::compiler() ;
		}
		
		ksort($this->arrRawSql['subtree']) ;
		
		return $aSqlCompiler->compile($this->arrRawSql) ;
	}
		
	/**
	 *
	 * @return string
	 */
	public function __toString()
	{	
		try{
			return $this->toString() ;
		} catch (\Exception $e) {
			error_log($e->getMessage()) ;
			return '' ;
		} catch (Exception $e) {
			error_log($e->message()) ;
			return '' ;
		}
	}
	
	public function & rawSql()
	{
		return $this->arrRawSql ;
	}
	public function setRawSql(array & $arrRawSql)
	{
		$this->arrRawSql =& $arrRawSql ;
		
		return $this ;
	}
	
	public function setRawClause($sType,array & $arrRawClause=null)
	{
		if($arrRawClause===null)
		{
			unset($this->arrRawSql['subtree'][$sType]) ;
		}
		else
		{
			$this->arrRawSql['subtree'][$sType] =& $arrRawClause ;
		}
	}
	public function & rawClause($sType,$bAutoCreate=true,array & $parentToken=null)
	{
		if(!$parentToken)
		{
			$parentToken =& $this->arrRawSql ;
		}
		
		if( !isset($parentToken['subtree'][$sType]) )
		{
			if(!$bAutoCreate)
			{
				return Type::$null ;
			}
			
			$parentToken['subtree'][$sType] = array(
					'expr_type' => 'clause_'. self::$mapClausesLower[$sType] ,
					'pretree' => array( self::$mapClauses[$sType] ) ,
					'subtree' => array() ,
			) ;
			
			if( $sType === self::CLAUSE_GROUP or $sType===self::CLAUSE_ORDER )
			{
				$parentToken['subtree'][$sType]['pretree'][] = 'BY' ;
			}
		}
		return $parentToken['subtree'][$sType] ;
	}
	
	protected function & makeFactors(& $factors)
	{
		if(is_array($factors))
		{
			foreach($factors as $key=>&$value)
			{
				if( is_int($key) )
				{
					unset($factors[$key]) ;
					$factors['@'.(1+$key)] =& $value ;
				}
			}
		}
		else
		{
			$factors = array('@1'=>$factors) ;
		}
		
		return $factors ;
	}
	
	public function addFactors(array & $factors)
	{
		if( !isset($this->arrRawSql['factors']) )
		{
			$this->arrRawSql['factors'] = array() ;
		}
		
		if( is_array($factors[0]) )
		{
			$this->arrRawSql['factors']+= self::makeFactors($factors[0]) ;
		}
		else
		{
			$this->arrRawSql['factors']+= self::makeFactors($factors) ;
		}
			
		return $this ;
	}
	
	public function __clone()
	{
		// 解除对原对像的引用
		$arrRawSql = $this->arrRawSql ;
		$this->arrRawSql =& $arrRawSql ;
	}

	static public function splitColumn($sColumn)
	{
		$pos = strrpos($sColumn,'.') ;
		if($pos!==false)
		{
			$sTableName = substr($sColumn,0,$pos) ;
			$sColumn = substr($sColumn,$pos+1) ;
		}
		else
		{
			$sTableName = null ;
		}
		
		return array($sTableName,$sColumn) ;
	}
	
	protected $arrRawSql = array() ;
	
	// protected $arrFactors ;
}




