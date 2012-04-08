<?php
namespace org\jecat\framework\db\sql ;

use org\jecat\framework\db\sql\parser\BaseParserFactory;
use org\jecat\framework\db\sql\compiler\SqlCompiler;

class Update extends MultiTableSQL implements IDataSettableStatement
{
	function __construct($sTableName=null,$sTableAlias=null)
	{
		$this->nTablesClause = SQL::CLAUSE_UPDATE ;
		parent::__construct($sTableName,$sTableAlias) ;
	}
	public function data($sColumnName)
	{
		$arrRawSet =& $this->rawClause(SQL::CLAUSE_SET) ;
		if( isset($arrRawSet['subtree'][$sColumnName]) )
		{
			return SqlCompiler::singleton()->compile($arrRawSet['subtree'][$sColumnName]['subtree']) ;
		}
	}
	public function setData($sColumnName,$value=null,$bValueExpr=false)
	{
		list($sTableName,$sColumnName) = SQL::splitColumn($sColumnName) ;
		
		$arrRawSet =& $this->rawClause(SQL::CLAUSE_SET) ;
		if( !isset($arrRawSet['subtree'][$sColumnName]) and !empty($arrRawSet['subtree']) )
		{
			$arrRawSet['subtree'][] = ',' ;
		}
		
		$arrRawSet['subtree'][$sColumnName] = array(
				'expr_type' => 'assignment' ,
				'pretree' => array(
						SQL::createRawColumn($sTableName,$sColumnName), '='
				) ,
				//'subtree' => array( $bValueExpr? SQL::transValue($value): SQL::transValue($value) ) ,
		) ;

		if($bValueExpr)
		{
			$arrRawSet['subtree'][$sColumnName]['subtree'] = BaseParserFactory::singleton()->create(true,null,'values')->parse($value,true) ;
		}
		else
		{
			$arrRawSet['subtree'][$sColumnName]['subtree'] = array( SQL::transValue($value) ) ;
		}
	}
	
	public function removeData($sColumnName)
	{
		unset($this->arrRawSql['subtree'][SQL::CLAUSE_SET]) ;
	}

	public function clearData()
	{
		unset($this->arrRawSql['subtree'][SQL::CLAUSE_SET]) ;
	}
}

?>