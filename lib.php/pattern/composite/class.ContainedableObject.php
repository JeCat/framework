<?php 


class ContainedableObject extends NamableObject implements IContainedable
{
	// implement for IContainedable //////////////////
	public function setParent(IContainer $aParent)
	{
		$this->aParent = $aParent ;
	}
	
	public function getParent()
	{
		return $this->aParent ;
	}
	
	
	
	private $aParent = null ;
}

?>