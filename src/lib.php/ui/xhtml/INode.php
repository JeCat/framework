<?php

namespace jc\ui\xhtml ;


use jc\ui\IObject;
use jc\util\IDataSrc;

interface INode extends IObject
{
	public function tagName() ;
	
	public function setTagName($sTagName) ;
	
	/**
	 * return Attribute
	 */
	public function Attributes() ;
	
	public function setAttributes(Attributes $aAttributes) ;
	
	public function isSingle() ;
	
	public function setSingle($bSingle=true) ;
	
	public function pre() ;
	
	public function setPre() ;
	
	public function isInline() ;
	
	public function setInline($bInline=true) ;
	
	public function isMultiLine() ;
	
	public function setMultiLine($bMultiLine=true) ;
}

?>