<?php

namespace jc\mvc\view\widget ;

use jc\system\Request;

interface IViewFormWidget extends IViewWidget
{
	public function value() ;
	
	public function setValue($data=null) ;
	
	public function valueToString() ;
	
	public function setValueFromString($data) ;
	
	public function setDataFromSubmit(Request $aRequest) ;
	
	public function dataVerifiers() ;

	public function verifyData() ;

}

?>