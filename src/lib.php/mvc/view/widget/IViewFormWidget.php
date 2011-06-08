<?php

namespace jc\mvc\view\widget ;


use jc\util\IDataSrc;

interface IViewFormWidget extends IViewWidget
{	
	public function value() ;
	
	public function setValue($data=null) ;
	
	public function valueToString() ;
	
	public function setValueFromString($data) ;
	
	public function setDataFromSubmit(IDataSrc $aDataSrc) ;
	
	public function dataVerifiers() ;

	public function verifyData() ;
	
	public function readOnly();
	
	public function setReadOnly($bReadOnly);
	
	public function enable();
	
	public function setEnable($bEnable);

}

?>