<?php 
namespace org\jecat\framework\db\sql2 ;

use org\jecat\framework\db\sql2\parser\BaseParserFactory;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\lang\Object;

abstract class SQL
{
	const CLAUSE_SELECT = 1 ; // SELECT
	const CLAUSE_FROM = 10 ;  // FROM
	const CLAUSE_WHERE = 11 ; // WHERE
	const CLAUSE_GROUP = 12 ; // GROUP
	const CLAUSE_ORDER = 13 ; // ORDER
	const CLAUSE_LIMIT = 16 ; // LIMIT
	
	static private $mapClauses = array(
			self::CLAUSE_SELECT => 'SELECT' ,
			self::CLAUSE_FROM => 'FROM' ,
			self::CLAUSE_WHERE => 'WHERE' ,
			self::CLAUSE_GROUP => 'GROUP' ,
			self::CLAUSE_ORDER => 'ORDER' ,
			self::CLAUSE_LIMIT => 'LIMIT' ,
	) ;
	static private $mapClausesLower = array(
			self::CLAUSE_SELECT => 'select' ,
			self::CLAUSE_FROM => 'from' ,
			self::CLAUSE_WHERE => 'where' ,
			self::CLAUSE_GROUP => 'group' ,
			self::CLAUSE_ORDER => 'order' ,
			self::CLAUSE_LIMIT => 'limit' ,
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
	static public function make($sSql)
	{
		if( ! $arrRawSqls = self::parser()->parse($sSql) )
		{
			return ;
		}
		
		if( isset($arrRawSqls[0]['commend']) and $arrRawSqls[0]['commend']==='SELECT' )
		{
			$aSql = new Select() ;
		}
		
		else 
		{
			return null ;
		}
		
		return $aSql->setRawSql($arrRawSqls[0]) ;
	}
	static public function makeRestriction($sSql)
	{
		
	}
	static public function makeColumn($sColumnExpr)
	{
		return BaseParserFactory::singleton()->create(true,null,'column')->parse($sColumnExpr,true) ;
	}
	
	// ------------
	static public function createRawColumn($sTable,$sName,$sAlias=null,$sDB=null)
	{
		$arrRaw = array(
				'expr_type' => 'column' ,
				'column' => $sName ,
		) ;
		
		if($sTable)
		{
			$arrRaw['table'] = $sTable ;
		}
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
		return parser\SqlCompiler::singleton() ;
	}
	
	// ----------------------------------------------------------
	public function __construct()
	{
		$this->setRawSql($arrRawSql=array()) ; 
	}
	
	/**
	 *
	 * @return string
	 */
	public function __toString()
	{
		ksort($this->arrRawSql['subtree']) ;
		
		try{
			return self::compiler()->compile($this->arrRawSql['subtree']) ;
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
	
	protected function setRawClause($sType,array & $arrRawWhere)
	{
		$this->arrRawSql['subtree'][$sType] =& $arrRawWhere ;
	}
	protected function & rawClause($sType)
	{
		if( !isset($this->arrRawSql['subtree'][$sType]) )
		{
			$this->arrRawSql['subtree'][$sType] = array(
					'expr_type' => 'clause_'. self::$mapClausesLower[$sType] ,
					'subtree' => array( self::$mapClauses[$sType] ) ,
			) ;
			
			if( $sType == self::CLAUSE_GROUP or $sType==self::CLAUSE_ORDER )
			{
				$this->arrRawSql['subtree'][$sType]['subtree'][] = 'BY' ;
			}
		}
		return $this->arrRawSql['subtree'][$sType] ;
	}
	
	protected $arrRawSql = array() ;
}


?>
