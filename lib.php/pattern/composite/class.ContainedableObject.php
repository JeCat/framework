<?php 


class ContainedableObject extends NamableObject implements IContainedable
{
	// implement for IContainedable //////////////////
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