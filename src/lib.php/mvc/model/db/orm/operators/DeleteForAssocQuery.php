<?php
namespace jc\mvc\model\db\orm\operators ;

use jc\db\sql\Delete;

class DeleteForAssocQuery extends StatementForAssocQuery 
{
	
	public function realStatement()
	{
		if(!$this->aStatemen)
		{
			$this->aStatemen = new Delete() ;
		}
		
		return $this->aStatemen ;
	}
	
	
	private $aStatemen ;
}

?>