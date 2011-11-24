<?php
namespace org\jecat\framework\db\sql\name ;

use org\jecat\framework\lang\Object;

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