<?php 
namespace org\jecat\framework\db\sql ;

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
	const CLAUSE_WHERE = 11 ;	// WHERE
	const CLAUSE_GROUP = 12 ;	// GROUP
	const CLAUSE_ORDER = 13 ;	// ORDER
	const CLAUSE_LIMIT = 16 ;	// LIMIT
	const CLAUSE_INTO = 21 ;	// INTO
	const CLAUSE_VALUES = 22 ;	// VALUES
	const CLAUSE_SET = 23 ;	// SET
	
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
		
		$arrArgvs = func_get_args() ;
		array_shift($arrArgvs) ;
		if($arrArgvs)
		{
			call_user_func_array(array($aSql,'addFactors'),$arrArgvs) ;
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
	static public function & transValue(& $value)
	{
		if( is_string($value) )
		{
			$value = "'" . addslashes($value) . "'" ;
		}
		else if( is_bool($value) )
		{
			$value = $value? "'1'" : "'0'" ;
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
			return self::compiler()->compile($this->arrRawSql['subtree'],$this->arrFactors) ;
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
	protected function & rawClause($sType,& $arrParentToken=null)
	{
		if(!$arrParentToken)
		{
			$arrParentToken =& $this->arrRawSql ;
		}
		
		if( !isset($arrParentToken['subtree'][$sType]) )
		{
			$arrParentToken['subtree'][$sType] = array(
					'expr_type' => 'clause_'. self::$mapClausesLower[$sType] ,
					'pretree' => array( self::$mapClauses[$sType] ) ,
					'subtree' => array() ,
			) ;
			
			if( $sType == self::CLAUSE_GROUP or $sType==self::CLAUSE_ORDER )
			{
				$arrParentToken['subtree'][$sType]['pretree'][] = 'BY' ;
			}
		}
		return $arrParentToken['subtree'][$sType] ;
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
		if( $this->arrFactors===null )
		{
			$this->arrFactors = array() ;
		}
		
		$arrArgs = func_get_args() ;
		if( is_array($arrArgs[0]) )
		{
			$this->arrFactors+= self::makeFactors($arrArgs[0]) ;
		}
		else
		{
			$this->arrFactors+= self::makeFactors($arrArgs) ;
		}
			
		return $this ;
	}
	
	protected $arrRawSql = array() ;
	
	protected $arrFactors ;
}


