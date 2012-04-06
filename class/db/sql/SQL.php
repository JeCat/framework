<?php 
namespace org\jecat\framework\db\sql ;

use org\jecat\framework\db\sql\compiler\SqlCompiler;

use org\jecat\framework\lang\Type;

use org\jecat\framework\db\sql\parser\BaseParserFactory;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\lang\Object;

abstract class SQL
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
		if( ! $arrRawSqls = self::parser()->parse($sSql) )
		{
			return ;
		}
		
		if( isset($arrRawSqls[0]['command']) and $arrRawSqls[0]['command']==='SELECT' )
		{
			$aSql = new Select() ;
			$aSql->setRawSql($arrRawSqls[0]) ;
		}
		
		else 
		{
			return null ;
		}
		
		if($factors)
		{
			$factors = Type::toArray($factors,Type::toArray_ignoreNull) ;
			$aSql->addFactors($factors) ;
		}
		
		return $aSql ;
	}
	/**
	 * @return Restriction
	 */
	static public function makeRestriction($sSql,$factors=null)
	{		
		$arrTrees = BaseParserFactory::singleton()->create(true,null,'where')->parse($sSql) ;
		
		$aRestriction = empty($arrTrees[0])? new Restriction(): new Restriction( true, $arrTrees[0] ) ;

		if($factors)
		{
			$factors = Type::toArray($factors,Type::toArray_ignoreNull) ;
			$aRestriction->addFactors($factors) ;
		}
		
		return $aRestriction ;
	}
	static public function makeColumn($sColumnExpr)
	{
		return BaseParserFactory::singleton()->create(true,null,'column')->parse($sColumnExpr,true) ;
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
	static public function & transValue(&$value)
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
	
	public function setRawClause($sType,array & $arrRawWhere=null)
	{
		if($arrRawWhere===null)
		{
			unset($this->arrRawSql['subtree'][$sType]) ;
		}
		else
		{
			$this->arrRawSql['subtree'][$sType] =& $arrRawWhere ;
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
	
	public function addFactors(/* ... */)
	{
		if( !isset($this->arrRawSql['factors']) )
		{
			$this->arrRawSql['factors'] = array() ;
		}
		
		$arrArgs = func_get_args() ;
		if( is_array($arrArgs[0]) )
		{
			$this->arrRawSql['factors']+= self::makeFactors($arrArgs[0]) ;
		}
		else
		{
			$this->arrRawSql['factors']+= self::makeFactors($arrArgs) ;
		}
			
		return $this ;
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


