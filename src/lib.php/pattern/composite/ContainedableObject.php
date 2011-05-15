<?php 

namespace jc\pattern\composite ;

use jc\lang\Object;

class ContainedableObject extends Object implements IContainedable
{
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