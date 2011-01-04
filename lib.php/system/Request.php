<?php
namespace jc\system ;

use jc\util\DataSrc;

abstract class Request extends DataSrc
{
	const TYPE_HTTP = 'jc\\system\\HttpRequest' ;
	const TYPE_CL = 'jc\\system\\CLRequest' ;
	

	/**
	 * 
	 * Enter description here ...
	 * @param int $nType
	 * @return Request
	 * @throws Exception
	 */
	static public function createRequest($sType)
	{
		if( !class_exists($sType) or !in_array(__CLASS__,class_parents($sType))  )
		{
			throw new Exception("unkonw request type: ".$sType) ;
		}

		return new $sType() ;
	}
	
}


?>