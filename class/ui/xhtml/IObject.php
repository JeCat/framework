<?php
namespace jc\ui\xhtml ;


use jc\pattern\composite\IContainer;
use jc\ui\IObject as IUiObject ;

interface IObject extends IUiObject
{	
	public function position() ;
	public function setPosition($nPosition) ;
	
	public function endPosition() ;
	public function setEndPosition($nEndPosition) ;
	
	public function line() ;
	public function setLine($nLine) ;

	public function source() ;
	public function setSource($sSource) ;
	
}

?>