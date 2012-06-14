<?php
namespace org\jecat\framework\mvc\model\executor ;

use org\jecat\framework\mvc\model\Prototype;
use org\jecat\framework\mvc\model\Model;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class Updater extends Executor
{
	public function execute(Model $aModel,array & $arrPrototype,array & $arrDataRow,$sWhere=null,DB $aDB=null)
	{
		$arrSqlStat['from'] = preg_replace("/^ FROM/", "", $this->makeFromClause($arrPrototype));
		
		$this->joinTables($arrPrototype,$arrSqlStat,Prototype::oneToOne) ;
		
		$arrClauseSet = array() ;
		foreach($arrDataRow as $column=>&$value)
		{
			if( is_int($column) )
			{
				$arrClauseSet[] = $value ;
			}
			else if( is_string($column) )
			{
				$pos=strrpos($column,'0') ;
				if($pos!==false)
				{
					$sTableAlias = $arrPrototype['name'].'.'.substr($column,0,$pos);
					$column = substr($column,$pos+1);
				}
				else 
				{
					$sTableAlias = $arrPrototype['name'] ;
				}
				
				$arrClauseSet[] = "`{$sTableAlias}`.`{$column}`=" . self::escValue($value) ;
			}
		}
		if( !empty($arrClauseSet) )
		{
			$sSql = "UPDATE "
								. $arrSqlStat['from']
			                    . " SET\r\n\t" . implode("\r\n\t, ",$arrClauseSet)."\r\n"
								. $this->makeWhereClause($arrPrototype,$sWhere)
								. $this->makeGroupByClause($arrPrototype)
								. $this->makeOrderByClause($arrPrototype) . " ; \r\n" ;
			
			$aDB->execute($sSql) ;
		}
	}
}

