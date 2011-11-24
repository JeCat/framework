<?php

namespace org\jecat\framework\mvc\view\widget ;


use org\jecat\framework\verifier\IVerifier;
use org\jecat\framework\util\IDataSrc;

interface IViewFormWidget extends IViewWidget
{	
	public function value() ;
	
	public function setValue($data=null) ;
	
	public function valueToString() ;
	
	public function setValueFromString($data) ;
	
	public function setDataFromSubmit(IDataSrc $aDataSrc) ;
	
	/**
	 * @return org\jecat\framework\verifier\VerifierManager
	 */
	public function dataVerifiers() ;

	public function verifyData() ;
	
	/**
	 * @return org\jecat\framework\verifier\VerifierManager
	 */
	public function addVerifier(IVerifier $aVerifier, $sExceptionWords=null, $callback=null, $arrCallbackArgvs=array()) ;
	
	public function isReadOnly();
	
	public function setReadOnly($bReadOnly);
	
	public function isDisabled();
	
	public function setDisabled($bDisabled);

}

?>