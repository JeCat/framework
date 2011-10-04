<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\db\sql\Delete;

class DeleteForAssocQuery extends StatementForAssocQuery 
{
	public function createRealStatement()
	{
		return new Delete() ;
	}	
}

?>