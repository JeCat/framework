<?php 

namespace jc\pattern\composite ;

class ContainedableObject extends NamableObject implements IContainedable
{	
	// implement for IContainedable //////////////////
	static public function type()
	{
		return __CLASS__ ;
	}
	
	public function setParent(IContainer $aParent)
	{
		$this->aParent = $aParent ;
	}
	
	public function parent()
	{
		return $this->aParent ;
	}
	
	
	
	private $aParent = null ;
}

?>