<?php
namespace org\jecat\framework\ui\xhtml\weave ;

use org\jecat\framework\util\String;
use org\jecat\framework\ui\xhtml\ObjectBase;

class WeaveinObject extends ObjectBase
{
	public function __construct(String $aCompiled)
	{
		$this->aCompiled = $aCompiled ;
	}
	
	/**
	 * @return org\jecat\framework\util\String
	 */
	public function compiled()
	{
		return $this->aCompiled ;
	}
	
	private $aCompiled ;
}

?>