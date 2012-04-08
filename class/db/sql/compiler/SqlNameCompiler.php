<?php
namespace org\jecat\framework\db\sql\compiler ;

use org\jecat\framework\db\DB;
use org\jecat\framework\setting\Setting;

class SqlNameCompiler
{	
	public function compile(SqlCompiler $aSqlCompiler,array & $arrTokenTree,array & $arrToken,array & $arrFactors=null)
	{
		// 处理表名
		if( $arrToken['expr_type']==='table' )
		{
			$sTable = $arrToken['table'] ;
			$sAlias = empty($arrToken['as'])? null: $arrToken['as'] ;

			// 表名称转换
			if($this->arrTableNameTranslaters)
			{
				foreach($this->arrTableNameTranslaters as &$translaters)
				{
					list($sTable,$sAlias) = call_user_func($translaters,$sTable,$sAlias,$arrToken,$arrTokenTree) ;
				}
			}
			
			$sSql = '`'.$sTable.'`' ;
			if($sAlias)
			{
				$sSql.= ' AS `' . $sAlias . '`' ;
			}
			
			// 处理 join 子句
			$sSql.= $aSqlCompiler->compile($arrToken,$arrFactors,$arrTokenTree) ;

			return $sSql ;
		}

		// 处理字段名
		else if( $arrToken['expr_type']==='column' )
		{
			$sTable = empty($arrToken['table'])? null: $arrToken['table'] ;
			$sColumn = $arrToken['column'] ;
			$sAlias = empty($arrToken['as'])? null: $arrToken['as'] ;
				
			// 字段名称转换
			if($this->arrColumnNameTranslaters)
			{
				foreach($this->arrColumnNameTranslaters as &$translaters)
				{
					list($sTable,$sColumn,$sAlias) = call_user_func($translaters,$sTable,$sColumn,$sAlias,$arrToken,$arrTokenTree) ;
				}
			}
			
			$sSql = $sTable? ('`'.$sTable.'`.'): '' ;
			$sSql.= ($sColumn==='*')? $sColumn: ('`'.$sColumn.'`') ;
			if($sAlias)
			{
				$sSql.= ' AS `' . $sAlias . '`' ;
			}
			
			return $sSql ;
		}
	}
	
	public function registerColumnNameTranslaters($fnTranslaters)
	{
		$this->arrColumnNameTranslaters[] = $fnTranslaters ;
	}
	public function registerTableNameTranslaters($fnTranslaters)
	{
		$this->arrTableNameTranslaters[] = $fnTranslaters ;
	}

	static public function translateTableName($sTable,$sAlias,array & $arrToken,array & $arrTokenTree)
	{
		return array( DB::singleton()->transTableName($sTable), $sAlias ) ;
	}
	
	private $arrColumnNameTranslaters ;
	
	private $arrTableNameTranslaters = array( array(__CLASS__,'translateTableName') ) ;
	
}