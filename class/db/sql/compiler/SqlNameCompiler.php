<?php
namespace org\jecat\framework\db\sql\compiler ;

class SqlNameCompiler
{
	public function compile(SqlCompiler $aSqlCompiler,array & $arrTokenTree,array & $arrToken,array & $arrFactors=null)
	{
		// 处理表名
		if( $arrToken['expr_type']==='table' )
		{
			//$sTable = $arrToken['table'] ;
			//$sDB = empty($arrToken['db'])? null: $arrToken['db'] ;
			//$sAlias = empty($arrToken['as'])? null: $arrToken['as'] ;
			
			//$sSql = $sDB? '': ('`'.$sDB.'`.') ;
			$sSql = '`'.$arrToken['table'].'`' ;
			if(!empty($arrToken['as']))
			{
				$sSql.= ' AS `' . $arrToken['as'] . '`' ;
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
					list($sTable,$sColumn,$sAlias) = call_user_func($translaters,$sTable,$sColumn,$sAlias,& $arrToken,& $arrTokenTree) ;
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
	
	private $arrColumnNameTranslaters ;
}