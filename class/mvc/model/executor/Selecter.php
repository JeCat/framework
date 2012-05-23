<?php
namespace org\jecat\framework\mvc\model\executor ;

use org\jecat\framework\mvc\model\Prototype;
use org\jecat\framework\mvc\model\Model;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class Selecter extends Executor
{
	public function execute(array & $arrPrototype,array & $arrDataSheet,$sWhere=null,DB $aDB=null)
	{
		// 为所有一对一关联建立 sql
		$arrMultiAssocs = array() ;
		
		echo $sSql = $this->makeSql($arrPrototype,$arrMultiAssocs,$sWhere) ;

		// 查询
		$aPdoRecordset = $aDB->query( $sSql ) ;
		
		$arrDataSheet = $aPdoRecordset->fetchAll(\PDO::FETCH_ASSOC) ;
		
		// 处理多属关联
		foreach ($arrDataSheet as &$arrRow)
		{
			foreach($arrMultiAssocs as &$arrAssoc)
			{
				// 多属关联条件
				if($arrAssoc['assoc']==Prototype::hasMany)
				{
					$arrClauseWhere = array() ;
					foreach($arrAssoc['toKeys'] as $nIdx=>$sToKey)
					{
						$arrClauseOn[] = "`{$arrAssoc['xpath']}`.`{$sToKey}` = '". addslashes($arrRow["{$arrPrototype['xpath']}.{$arrAssoc['fromKeys'][$nIdx]}"]) . "'" ;
					}
					$sClauseWhere = implode(' AND ',$arrClauseWhere) ;
				}
				else if($arrAssoc['assoc']==Prototype::hasAndBelongsToMany)
				{
					$arrClauseWhere = array() ;
					foreach($arrAssoc['toBridgeKeys'] as $nIdx=>$sToBridgeKey)
					{
						$arrClauseOn[] = "`{$arrAssoc['xpath']}#bridge`.`{$sToBridgeKey}` = '". addslashes($arrRow["{$arrPrototype['xpath']}.{$arrAssoc['fromKeys'][$nIdx]}"]) . "'" ;
					}
					$sClauseWhere = implode(' AND ',$arrClauseWhere) ;
				}
				
				$arrRow[$arrAssoc['xpath']] = array() ;
				$arrRow[$arrAssoc['xpath'].chr(0).'sheet'] = true ;
				$this->execute($arrAssoc,$arrRow[$arrAssoc['xpath']],$sClauseWhere,$aDB) ;
			}
		}
		
		// 初始化数据表的行指针
		reset($arrDataSheet) ;
	}
	
	private function makeSql(array & $arrPrototype,array & $arrMultiAssocs,$sWhere)
	{
		$sSqlClauseColumnList = $this->makeColumnList($arrPrototype) ;
		$sSqlClauseFrom = $this->makeFromClause($arrPrototype) ;

		// 关联 多对多 的桥接表
		if( !empty($arrPrototype['assoc']) and $arrPrototype['assoc']==Prototype::hasAndBelongsToMany )
		{
			$sSqlClauseFrom.= " LEFT JOIN (`{$arrPrototype['bridge']}` AS `{$arrPrototype['bridgeTableAlias']}`" ;

			$arrClauseOn ;
			foreach($arrAssoc['fromBridgeKeys'] as $nIdx=>$sFromBridgeKey)
			{
				$arrClauseOn[] = "`{$arrPrototype['bridgeTableAlias']}`.`{$sFromBridgeKey}` = `{$arrAssoc['tableAlias']}`.`{$arrAssoc['toKeys'][$nIdx]}`" ;
			}
			$sSqlClauseFrom.= ") ON (".implode(' AND ',$arrClauseOn).")" ;
		}
		
		$this->joinTables($arrPrototype,$sSqlClauseColumnList,$sSqlClauseFrom,$arrMultiAssocs) ;
		
		return $sSql = "SELECT \r\n\t" . $sSqlClauseColumnList
					. $sSqlClauseFrom
					. $this->makeWhereClause($arrPrototype,$sWhere)
					. $this->makeGroupByClause($arrPrototype)
					. $this->makeOrderByClause($arrPrototype)
					. $this->makeLimitClause($arrPrototype) . " ; \r\n" ;
	}
	
	private function joinTables(array & $arrPrototype,& $sSqlClauseColumnList,& $sSqlClauseFrom,array & $arrMultiAssocs)
	{
		if(empty($arrPrototype['associations']))
		{
			return ;
		}
		
		foreach($arrPrototype['associations'] as $arrAssoc)
		{
			if( $arrAssoc['assoc']&Prototype::oneToOne )
			{
				// 字段表
				$sSqlClauseColumnList.= "\r\n	, " . $this->makeColumnList($arrAssoc) ;
				
				// join table
				$sSqlClauseFrom.= " LEFT JOIN (`{$arrAssoc['table']}` AS `".addslashes($arrAssoc['tableAlias'])."`" ;
				
				$this->joinTables($arrAssoc,$sSqlClauseFrom,$sSqlClauseColumnList,$arrMultiAssocs) ;
				
				// join table on
				$arrClauseOn ;
				foreach($arrAssoc['fromKeys'] as $nIdx=>$sFromKey)
				{
					$arrClauseOn[] = "`{$arrPrototype['tableAlias']}`.`{$sFromKey}` = `{$arrAssoc['tableAlias']}`.`{$arrAssoc['toKeys'][$nIdx]}`" ;
				}
				$sSqlClauseFrom.= ") ON (".implode(' AND ',$arrClauseOn).")" ;
			}
			else
			{
				$arrMultiAssocs[] =& $arrAssoc ;
			}
		}
	}

	private function makeColumnList(array & $arrPrototype)
	{
		$sPrefix = $arrPrototype['xpath']? ($arrPrototype['xpath'].'.'): '' ;
		
		/*if(empty($arrPrototype['columns']))
		{
			return '`'.$arrPrototype['tableAlias'].'`.*' ;
		}*/
		
		$arrColunms = $arrPrototype['columns'] ;
		
		foreach($arrColunms as &$sColumn)
		{
			$sColumn = "`{$arrPrototype['tableAlias']}`.`{$sColumn}` as `{$sPrefix}{$sColumn}`" ;
		}
		return implode("\r\n	, ",$arrColunms) ;
	}
}

