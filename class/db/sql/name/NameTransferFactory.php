<?php
namespace jc\db\sql\name ;

use jc\lang\Object;

class NameTransferFactory extends Object
{
	/**
	 * @return NameTransfer
	 */
	public function create()
	{
		return new NameTransfer() ;
	}
}

?>