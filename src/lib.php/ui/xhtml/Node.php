<?php

namespace jc\ui\xhtml ;

use jc\ui\Object;

class Node extends Object
{
	public function __construct($sNodeName)
	{
		$this->sNodeName = $sNodeName ;
	}
	
	public function nodeName()
	{
		return $this->sNodeName ;
	}
	
	public function setNodeName($sNodeName)
	{
		$this->sNodeName = $sNodeName ;
	}

	/**
	 * return Attributes
	 */
	public function attributes()
	{
		if(!$this->aAttributes)
		{
			$this->aAttributes = new Attributes() ;
		}
		
		return $this->aAttributes ;
	}
	
	public function setAttributes(Attributes $aAttributes)
	{
		$this->aAttributes = $aAttributes ;
	}
	
	public function isSingle()
	{
		return $this->bSingle ;
	}
	
	public function setSingle($bSingle=true)
	{
		$this->bSingle = $bSingle? true: false ;
	}
	
	public function pre()
	{
		return $this->bPre ;
	}
	
	public function setPre($bPre=true)
	{
		$this->bPre = $bPre? true: false ;
	}
	
	
	private $sNodeName ;
	
	private $bSingle = true ;
	
	private $bPre = true ;
	
	private $aAttributes ;
}

?>