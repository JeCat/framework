<?php
namespace org\jecat\framework\mvc\model2 ;

use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class Selecter extends Object
{
	public function execute(Model $aModel,$where=null)
	{
		$aPrototype = $aModel->prototype() ;
		$aDB = $aModel->db() ;
		
		// $aPrototype->columns()
	}
}

