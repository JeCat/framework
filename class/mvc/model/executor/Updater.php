<?php
namespace org\jecat\framework\mvc\model\executor ;

use org\jecat\framework\mvc\model\Prototype;
use org\jecat\framework\mvc\model\Model;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class Updater extends Executor
{
	public function execute(array & $arrPrototype,array & $arrDataSheet,$sWhere=null,DB $aDB=null)
	{
		// 为所有一对一关联建立 sql
		$arrMultiAssocs = array() ;
		
		$sSql = $this->makeSql($arrPrototype,$arrMultiAssocs,$sWhere) ;

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
		$arrSqlStat['columnList'] = $this->makeColumnList($arrPrototype) ;
		$arrSqlStat['from'] = $this->makeFromClause($arrPrototype) ;
		$arrSqlStat['multiAssocs'] =& $arrMultiAssocs ;

		$this->joinTables($arrPrototype,$arrSqlStat) ;
		
		return $sSql = "SELECT \r\n\t" . $arrSqlStat['columnList']
					. $arrSqlStat['from']
					. $this->makeWhereClause($arrPrototype,$sWhere)
					. $this->makeGroupByClause($arrPrototype)
					. $this->makeOrderByClause($arrPrototype)
					. $this->makeLimitClause($arrPrototype) . " ; \r\n" ;
	}

	protected function joinTables(array & $arrPrototype,array & $arrSqlStat)
	{
		// 字段表
		$arrSqlStat['columnList'].= "\r\n	, " . $this->makeColumnList($arrPrototype) ;

		parent::joinTables($arrPrototype,$arrSqlStat) ;
	}

	private function makeColumnList(array & $arrPrototype)
	{
		$sPrefix = $arrPrototype['xpath']? ($arrPrototype['xpath'].'.'): '' ;
		
		$arrColunms = $arrPrototype['columns'] ;
		
		foreach($arrColunms as &$sColumn)
		{
			$sColumn = "`{$arrPrototype['tableAlias']}`.`{$sColumn}` as `{$sPrefix}{$sColumn}`" ;
		}
		return implode("\r\n	, ",$arrColunms) ;
	}
}

