<?php

namespace jc\mvc\view\widget ;


use jc\verifier\IVerifier;
use jc\util\IDataSrc;

interface IViewFormWidget extends IViewWidget
{	
	public function value() ;
	
	public function setValue($data=null) ;
	
	public function valueToString() ;
	
	public function setValueFromString($data) ;
	
	public function setDataFromSubmit(IDataSrc $aDataSrc) ;
	
	/**
	 * @return jc\verifier\VerifierManager
	 */
	public function dataVerifiers() ;

	public function verifyData() ;
	
	/**
	 * @return jc\verifier\VerifierManager
	 */
	public function addVerifier(IVerifier $aVerifier, $sExceptionWords=null, $callback=null, $arrCallbackArgvs=array()) ;
	
	public function isReadOnly();
	
	public function setReadOnly($bReadOnly);
	
	public function isDisabled();
	
	public function setDisabled($bDisabled);

}

?>