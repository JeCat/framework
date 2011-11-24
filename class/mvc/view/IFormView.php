<?php
namespace jc\mvc\view ;

use jc\util\IDataSrc;

interface IFormView {
	
	public function loadWidgets(IDataSrc $aDataSrc) ;
	
	public function verifyWidgets() ;
	
	public function isSubmit(IDataSrc $aDataSrc) ;
	
	public function isShowForm() ;
	
	public function hideForm($bHide=true) ;
}

?>