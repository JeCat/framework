<?php
namespace org\jecat\framework\mvc\model\db\orm ;

use org\jecat\framework\mvc\model\db\Recordset;
use org\jecat\framework\mvc\model\db\Model;
use org\jecat\framework\db\sql\IDataSettableStatement;
use org\jecat\framework\mvc\model\IModel;
use org\jecat\framework\db\sql\StatementFactory;
use org\jecat\framework\lang\Object;

abstract class OperationStrategy extends Object
{
	protected function makeResrictionForAsscotion(array & $arrDataRow,$sFromTableName=null,array $arrFromKeys,$sToTableName=null,array $arrToKeys, StatementFactory $aSqlFactory)
	{
		if($sToTableName)
		{
			$sToTableName = "`{$sToTableName}`." ;
		}
		
		$aRestriction = $aSqlFactory->createRestriction() ;
		
		foreach($arrFromKeys as $nIdx=>$sFromKey)
		{
			if($sFromTableName)
			{
				$sFromKey = $sFromTableName.'.'.$sFromKey ;
			}
			$aRestriction->eq( "{$sToTableName}`{$arrToKeys[$nIdx]}`", $arrDataRow[$sFromKey] ) ;
		}
		
		return $aRestriction ;
	}

	protected function setValue(IDataSettableStatement $aStatement,$keys,$names=null,IModel $aDataSource,$sTableName=null)
	{
		$keys = array_values((array)$keys) ;
		if($names)
		{
			$names = array_values((array)$names) ;
		}
		else 
		{
			$names = $keys ;
		}
		
		if($sTableName)
		{
			$sTableName = "`{$sTableName}`." ;
		}
		
		foreach($keys as $idx=>$sKey)
		{
			$aStatement->setData(
				"{$sTableName}`{$sKey}`"
				, $aDataSource->data($names[$idx])
			) ;
		}
	}
}

?>