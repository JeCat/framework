<?php
namespace jc\ui\xhtml\weave ;

use jc\ui\xhtml\ObjectBase;

class WeaveinObject extends ObjectBase
{
	public function __construct(String $aCompiled)
	{
		$this->aCompiled = $aCompiled ;
	}
	
	/**
	 * @return jc\util\String
	 */
	public function compiled()
	{
		return $this->aCompiled ;
	}
	
	private $aCompiled ;
}

?>