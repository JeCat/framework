<?php
namespace org\jecat\framework\mvc\model\executor ;

use org\jecat\framework\mvc\model\Prototype;
use org\jecat\framework\lang\Object;

abstract class Executor extends Object
{
	protected function makeFromClause(array & $arrPrototype)
	{
		return " FROM `{$arrPrototype['table']}` AS `" . addslashes($arrPrototype['tableAlias']) . '`' ;
	}
	protected function makeWhereClause(array & $arrPrototype,$sTmpWhere=null)
	{
		$arrWhere = isset($arrPrototype['where'])? $arrPrototype['where']: array() ;
		
		if( $sTmpWhere )
		{
			$arrWhere[] = $sTmpWhere ;
		}
		
		switch( count($arrWhere) )
		{
			case 0 :
				return '' ;
			case 1 :
				return "\r\nWHERE\r\n\t" . implode(' AND ',$arrWhere) ;
			default :
				return "\r\nWHERE\r\n\t(" . implode(') AND (',$arrWhere) . ')' ;
		}
	}
	protected function makeOrderByClause(array & $arrPrototype)
	{
		if( !empty($arrPrototype['orderBy']) )
		{
			$arrColumns = array() ;
			foreach( $arrPrototype['orderBy'] as $sColumn=>&$bDesc )
			{
				$arrColumns[] = '`'.$arrPrototype['tableAlias'].'`.`'.$sColumn.'`' . ($bDesc?' DESC':' ASC') ;
			}
			return "\r\nORDER BY\r\n\t" . implode(',',$arrColumns) ;
		}
		else
		{
			return '' ;
		}
	}
	protected function makeGroupByClause(array & $arrPrototype)
	{
		if( !empty($arrPrototype['groupBy']) )
		{
			$arrColumns = array() ;
			foreach( $arrPrototype['groupBy'] as &$sColumn )
			{
				$arrColumns[] = '`'.$arrPrototype['tableAlias'].'`.`'.$sColumn.'`' ;
			}
			return "\r\nGROUP BY\r\n\t" . implode(',',$arrColumns) ;
		}
		else
		{
			return '' ;
		}
	}
	protected function makeLimitClause(array & $arrPrototype)
	{		
		if( !isset($arrPrototype['limitLen']) )
		{
			return '' ;
		}
		else
		{
			if(!isset($arrPrototype['limitFrom']))
			{
				return "\r\nLIMIT\r\n\t" . $arrPrototype['limitLen'] ;
			}
			else
			{
				return "\r\nLIMIT\r\n\t" . $arrPrototype['limitFrom'] . ', ' . $arrPrototype['limitLen'] ;
			}
		}
	}
}

