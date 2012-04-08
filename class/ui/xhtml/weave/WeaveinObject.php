<?php
namespace org\jecat\framework\ui\xhtml\weave ;

use org\jecat\framework\util\String;
use org\jecat\framework\ui\xhtml\ObjectBase;
use org\jecat\framework\ui\IObject;

class WeaveinObject extends ObjectBase
{
	public function __construct(String $aCompiled,IObject &$aTargetObject)
	{
		$this->aCompiled = $aCompiled ;
		$this->aTargetObject = $aTargetObject ;
	}
	
	/**
	 * @return org\jecat\framework\util\String
	 */
	public function compiled()
	{
		return $this->aCompiled ;
	}
	
	/**
	 * @return org\jecat\framework\ui\IObject
	 */
	public function targetObject()
	{
		return $this->aTargetObject ;
	}
	
	private $aCompiled ;
	private $aTargetObject ;
}

?>