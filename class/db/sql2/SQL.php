<?php 
namespace org\jecat\framework\db\sql2 ;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\lang\Object;

abstract class SQL
{
	/**
	 * @return Select
	 */
	static public function make($sSql)
	{
		$arrRawSql = self::parser()->parse($sSql) ;
		
		if( array_key_exists('SELECT',$arrRawSql) )
		{
			$aSql = new Select() ;
		}
		
		else 
		{
			return null ;
		}
		
		return $aSql->setRawSql($arrRawSql) ;
	}
	static public function makeRestriction($sSql)
	{
		
	}
	
	// ------------
	
	
	static public function createRawAlias($sAlias,$bAs=true)
	{
		return $sAlias? array(
						'as' => $bAs?true:false ,
						'name' => $sAlias ,
						'base_expr' => ($bAs? 'AS ': '').$sAlias ,
			): null ;
	}
	static public function createRawColumn($sName,$sAlias=null)
	{
		return array(
				'expr_type' => 'colref' ,
				'base_expr' => $sName ,
				'alias' => $sAlias? self::createRawAlias($sAlias): null ,
				// 'sub_tree' => '' ,
		) ;
	}
	static public function createRawTable($sName,$sAlias=null)
	{
		return array(
				'expr_type' => 'table' ,
				'table' => $sName ,
				'alias' => $sAlias? self::createRawAlias($sAlias): null ,
				'join_type' => 'CROSS' ,
				/*
				'ref_type' => '' ,
				'ref_clause' => '' ,
				'base_expr' => '' ,
				'sub_tree' => '' ,	
				*/
		) ;
	}
	
	/**
	 * @return com\google\code\phpsqlparser\PHPSQLParser
	 */
	static protected function parser()
	{
		return Object::singleton(true,null,'com\\google\\code\\phpsqlparser\\PHPSQLParser') ;
	} 
	
	// ----------------------------------------------------------
	public function __construct()
	{
		$this->setRawSql($arrRawSql=array()) ; 
	}
	
	public function & rawSql()
	{
		return $this->arrRowSql ;
	}
	public function setRawSql(array & $arrRawSql)
	{
		$this->arrRowSql =& $arrRawSql ;
		
		return $this ;
	}
	
	protected $arrRowSql = array() ;
}


?>
