<?php
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\util\IDataSrc;

interface IFormView {
	
	public function loadWidgets(IDataSrc $aDataSrc) ;
	
	public function verifyWidgets() ;
	
	public function isSubmit(IDataSrc $aDataSrc) ;
	
	public function isShowForm() ;
	
	public function hideForm($bHide=true) ;
}

?>