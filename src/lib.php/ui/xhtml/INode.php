<?php

namespace jc\ui\xhtml ;


interface INode
{
	public function nodeName() ;
	
	public function setNodeName($sNodeName) ;
	
	/**
	 * return Attribute
	 */
	public function Attributes() ;
	
	public function setAttributes(Attributes $aAttributes) ;
	
	public function isSingle() ;
	
	public function setSingle($bSingle=true) ;
	
	public function pre() ;
	
	public function setPre() ;
	
}

?>