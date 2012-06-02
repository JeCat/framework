<?php
namespace org\jecat\framework\mvc\model\executor ;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\mvc\model\Prototype;
use org\jecat\framework\mvc\model\Model;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class Deleter extends Executor
{	
	public function execute(array & $arrPrototype, $sWhere=true , $sOrder=true, $sLimit=true,DB $aDB=null)
	{
		$arrSqlStat['from'] = preg_replace("/AS .*/", "", $this->makeFromClause($arrPrototype));
		
		// 删除记录时，不对 belongsTo 自动关联操作
		$this->joinTables($arrPrototype,$arrSqlStat,Prototype::hasOne, false) ;
        
		$joinTableNames = '';
		if (!empty($arrPrototype['associations'])) {
		    
		    if( $sOrder ||  $sLimit)
		    {
		        throw new Exception("多表关联删除时不能使用 order 或 limie操作") ;
		    }
		    
		    $joinTableNames = '`'.$arrPrototype['table'].'`';
     		foreach($arrPrototype['associations'] as &$arrAssoc)
    		{
    			if($arrAssoc['type'] == Prototype::hasOne)
    			{
    			    $joinTableNames .= ',`' . $arrAssoc['table'] .'`' ;
    			}
    		}
		}
		
		
		$sSql = "DELETE " 
		            . $joinTableNames
		            . $arrSqlStat['from'];
			
		if($sWhere)
		{
		    $sSql .= ' WHERE '.$sWhere;
		}
		if($sOrder)
		{
		    $sSql .= ' ORDER BY '.$sOrder;
		}
		if($sLimit)
		{
		    $sSql .= ' LIMIT '.$sLimit;
		}
		
		echo $sSql;
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

