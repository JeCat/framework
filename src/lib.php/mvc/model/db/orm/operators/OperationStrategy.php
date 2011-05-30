<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\mvc\model\db\orm\AssociationPrototype;

use jc\db\sql\Criteria;
use jc\db\sql\MultiTableStatement;
use jc\mvc\model\db\IModel;
use jc\mvc\model\db\orm\ModelPrototype;
use jc\db\DB;
use jc\lang\Object;

abstract class OperationStrategy extends Object
{
	protected function setCondition(Criteria $aCriteria,$keys,$values)
	{
		$keys = array_values((array)$keys) ;
		$values = array_values((array)$values) ;
		
		foreach($keys as $nIdx=>$sKey)
		{
			$aCriteria->add($sKey,$values[$nIdx]) ;
		}
	}
}

?>