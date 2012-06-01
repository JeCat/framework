<?php
namespace org\jecat\framework\mvc\model\executor ;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\mvc\model\Prototype;
use org\jecat\framework\mvc\model\Model;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class Deleter extends Executor
{	
	public function execute(array & $arrPrototype,$sWhere=true,DB $aDB=null)
	{
		$arrSqlStat['from'] = preg_replace("/AS .*/", "", $this->makeFromClause($arrPrototype));
		
		// 删除记录时，不对 belongsTo 自动关联操作
		//$this->joinTables($arrPrototype,$arrSqlStat,Prototype::total^Prototype::belongsTo) ;

		echo $sSql = "DELETE " . $arrSqlStat['from']
					. $this->makeWhereClause($arrPrototype,$sWhere) . " ; \r\n" ;
			
		$aDB->execute($sSql) ;
		
		// 删除下级多属关联
		/*
		if (empty($arrPrototype['multiAssocs'])) $arrPrototype['multiAssocs'] = array();
 		foreach($arrPrototype['multiAssocs'] as &$arrAssoc)
		{
			$this->execute($arrAssoc,null,$aDB) ;
		}
		*/
	}
	
	
}

