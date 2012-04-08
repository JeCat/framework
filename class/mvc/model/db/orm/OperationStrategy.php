<?php
namespace org\jecat\framework\mvc\model\db\orm ;

use org\jecat\framework\db\sql\SQL;

use org\jecat\framework\db\sql\Restriction;
use org\jecat\framework\mvc\model\db\Recordset;
use org\jecat\framework\mvc\model\db\Model;
use org\jecat\framework\db\sql\IDataSettableStatement;
use org\jecat\framework\db\sql\StatementFactory;
use org\jecat\framework\lang\Object;

abstract class OperationStrategy extends Object
{
	protected function makeResrictionForAsscotion(array & $arrDataRow,$sFromTableName=null,array $arrFromKeys,$sToTableName=null,array $arrToKeys)
	{
		$aRestriction = new Restriction() ;
		
		foreach($arrFromKeys as $nIdx=>$sFromKey)
		{
			if($sFromTableName)
			{
				$sFromKey = $sFromTableName.'.'.$sFromKey ;
			}
			$aRestriction->expression( array(
					SQL::createRawColumn($sToTableName, $arrToKeys[$nIdx]) ,
					'=' , SQL::transValue($arrDataRow[$sFromKey])
			), true, true ) ;
		}
		
		return $aRestriction ;
	}

	protected function setValue(IDataSettableStatement $aStatement,$keys,$names=null,Model $aDataSource,$sTableName=null)
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