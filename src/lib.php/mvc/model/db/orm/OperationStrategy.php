<?php
namespace jc\mvc\model\db\orm ;

use jc\mvc\model\db\Model;
use jc\db\sql\IDataSettableStatement;
use jc\mvc\model\IModel;
use jc\db\sql\StatementFactory;
use jc\lang\Object;

abstract class OperationStrategy extends Object
{
	protected function makeResrictionForAsscotion(Model $aModel,array $arrFromKeys,$sToTableName=null,array $arrToKeys, StatementFactory $aSqlFactory)
	{
		if($sToTableName)
		{
			$sToTableName = "`{$sToTableName}`." ;
		}
		
		$aRestriction = $aSqlFactory->createRestriction() ;
		
		foreach($arrFromKeys as $nIdx=>$sFromKey)
		{
			$aRestriction->eq( "{$sToTableName}`{$arrToKeys[$nIdx]}`", $aModel->data($sFromKey) ) ;
		}
		
		return $aRestriction ;
	}

	protected function makeResrictionForForeignKey($sFromTableName=null,$sToTableName=null,$arrFromKeys,$arrToKeys, StatementFactory $aSqlFactory)
	{
		if($sToTableName)
		{
			$sToTableName = "`{$sToTableName}`." ;
		}
		if($sFromTableName)
		{
			$sFromTableName = "`{$sFromTableName}`." ;
		}
		
		$aRestriction = $aSqlFactory->createRestriction() ;
		
		foreach ($arrFromKeys as $nIdx=>$sFromKey)
		{
			$aRestriction->eqColumn(
				"{$sFromTableName}`{$sFromKey}`"
				, "{$sToTableName}`{$arrToKeys[$nIdx]}`"
			) ;
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