<?php

namespace jc\ui\xhtml ;

use jc\ui\UIObject;

class Node extends UIObject
{
	public function __construct($sNodeName)
	{
		$this->sNodeName = $sNodeName ;
	}
	
	public function nodeName()
	{
		return $this->sNodeName ;
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
	
	private $sNodeName ;
	
	private $aAttributes ;
}

?>