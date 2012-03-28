<?php 
namespace org\jecat\framework\db\sql2 ;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\lang\Object;

abstract class SQL
{
	const CLAUSE_SELECT = 'SELECT' ;
	const CLAUSE_FROM = 'FROM' ;
	const CLAUSE_WHERE = 'WHERE' ;
	const CLAUSE_GROUP = 'GROUP' ;
	const CLAUSE_ORDER = 'ORDER' ;
	const CLAUSE_LIMIT = 'LIMIT' ;
	
	static public function insert($sTableName)
	{
	}
	static public function delete($sTableName)
	{
		
	}
	/**
	 * @return Select
	 */
	static public function select($sTableName=null)
	{
		return new Select($sTableName) ;
	}
	static public function update($sTableName)
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
		
		if( array_key_exists('SELECT',$arrRawSqls[0]) )
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
		if($sAlias)
		{
			$arrRaw['alias'] = $sAlias ;
		}
		if($sDB)
		{
			$arrRaw['db'] = $sDB ;
		}
		
		return $arrRaw ;
	}
	static public function createRawTable($sName,$sAlias=null,$sDB=null)
	{
		$arrRaw = array(
				'expr_type' => 'table' ,
				'table' => $sName ,
		) ;
		
		if($sAlias)
		{
			$arrRaw['alias'] = $sAlias ;
		}
		if($sDB)
		{
			$arrRaw['db'] = $sDB ;
		}
		
		return $arrRaw ;
	}
	
	/**
	 * @return parser\Parser
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
		try{
			return self::compiler()->compile($this->arrRawSql) ;
		} catch (\Exception $e) {
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
		$this->arrRawSql[$sType] =& $arrRawWhere ;
	}
	protected function & rawClause($sType)
	{
		if( !isset($this->arrRawSql[$sType]) )
		{
			$this->arrRawSql[$sType] = array('subtree'=>array()) ;
		}
		return $this->arrRawSql[$sType] ;
	}
	
	protected $arrRawSql = array() ;
}


?>
