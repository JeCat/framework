<?php
namespace jc\mvc\model\db\orm;

use jc\lang\Object;
use jc\mvc\model\db\IModel;
use jc\db\sql\Statement;

class Select extends Object
{
	public function __construct(Prototype $aPrototype,IModel $aModel=null)
	{
		$aPrototype = $aPrototype ;
		$aModel = $aModel ;
	}
}

?>