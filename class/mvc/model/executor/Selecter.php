<?php
namespace org\jecat\framework\mvc\model\executor ;

use org\jecat\framework\mvc\model\Prototype;
use org\jecat\framework\mvc\model\Model;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class Selecter extends Executor
{
	public function execute(Model $aModel,array & $arrPrototype,array & $arrDataSheet,$sWhere=null,DB $aDB=null)
	{
	    //判断xpath是否需要加点。
	    $sXpath = empty($arrPrototype['xpath'])?"":$arrPrototype['xpath'].".";
	    
		// 为所有一对一关联建立 sql
		$arrMultiAssocs = array() ;
		
		$sSql = $this->makeSql($arrPrototype,$arrMultiAssocs,$sWhere) ;

		// 查询
		$aPdoRecordset = $aDB->query( $sSql ) ;
		
		$arrDataSheet = $aPdoRecordset->fetchAll(\PDO::FETCH_ASSOC) ;
		
		
		// 处理多属关联
		for( reset($arrDataSheet); ($row=key($arrDataSheet))!==null; next($arrDataSheet) )
		{
			$arrRow =& $arrDataSheet[$row] ;
		    
			foreach($arrMultiAssocs as &$arrAssoc)
			{
				// 多属关联条件
				if($arrAssoc['assoc']==Prototype::hasMany)
				{
					$arrClauseWhere = array() ;
					foreach($arrAssoc['toKeys'] as $nIdx=>$sToKey)
					{
						$arrClauseWhere[] = "`{$arrAssoc['tableAlias']}`.`{$sToKey}` = '". addslashes($arrRow["{$sXpath}{$arrAssoc['fromKeys'][$nIdx]}"]) . "'" ;
					}
					$sClauseWhere = implode(' AND ',$arrClauseWhere) ;
				}
				else if($arrAssoc['assoc']==Prototype::hasAndBelongsToMany)
				{
					$arrClauseWhere = array() ;
					foreach($arrAssoc['toBridgeKeys'] as $nIdx=>$sToBridgeKey)
					{
						$arrClauseWhere[] = "`{$arrAssoc['tableAlias']}#bridge`.`{$sToBridgeKey}` = '". addslashes($arrRow["{$sXpath}{$arrAssoc['fromKeys'][$nIdx]}"]) . "'" ;
					}
					$sClauseWhere = implode(' AND ',$arrClauseWhere) ;
				}
				$this->execute(
						$aModel
						, $arrAssoc
						, $aModel->buildSheet($arrAssoc['xpath']) // 
						, $sClauseWhere
						, $aDB
				) ;
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
		
		 $sSql = "SELECT \r\n\t" . $arrSqlStat['columnList']
					. $arrSqlStat['from']
					. $this->makeWhereClause($arrPrototype,$sWhere)
					. $this->makeGroupByClause($arrPrototype)
					. $this->makeOrderByClause($arrPrototype)
					. $this->makeLimitClause($arrPrototype) . " ; \r\n" ;
		 
		 //echo "<pre>";print_r($sSql);echo "</pre>";
		 return $sSql;
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
			$sColumn = "`{$arrPrototype['name']}`.`{$sColumn}` as `{$sPrefix}{$sColumn}`" ;
		}
		return implode("\r\n	, ",$arrColunms) ;
	}
}

