<?php
namespace jc\mvc\view\widget;

use jc\verifier\IVerifier;

use jc\verifier\VerifierManager;
use jc\verifier\VerifyFailed;
use jc\message\Message;
use jc\util\IDataSrc;

class FormWidget extends Widget implements IViewFormWidget
{	
	public function value()
	{
		return $this->value ;
	}
	
	public function setValue($data = null)
	{
		$this->value = $data;
	}
	
	public function valueToString()
	{
		return strval ( $this->value () );
	}
	
	public function setValueFromString($data)
	{
		$this->setValue ( $data );
	}
	
	public function setDataFromSubmit(IDataSrc $aDataSrc) 
	{
		$this->setValueFromString ( $aDataSrc->get ( $this->formName () ) );
	}
	
	public function addVerifier(IVerifier $aVerifier, $sExceptionWords=null, $callback=null, $arrCallbackArgvs=array())
	{
		return $this->dataVerifiers()->add($aVerifier,$sExceptionWords,$callback,$arrCallbackArgvs) ;
	}
	
	public function dataVerifiers() 
	{
		if (! $this->aVerifiers) 
		{
			$this->aVerifiers = new VerifierManager() ;
		}
		return $this->aVerifiers;
	}

	public function verifyData()
	{
		if( $this->aVerifiers )
		{			
			try{
				
				if( !$this->aVerifiers->verifyData( $this->value(), true ) )
				{
					return false ;
				}
				
			} catch (VerifyFailed $e) {
				
				new Message(
					Message::error
					, "栏位%s输入的内容无效：".$e->getMessage()
					, array_merge(array($this->title()),$e->messageArgvs())
				) ;
				
				return false ;
			}
		}
		
		return true;
	}
	
	public function formName()
	{
		return $this->sFormName === null ? $this->id () : $this->sFormName;
	}
	
	public function setFormName($sFormName)
	{
		$this->sFormName = $sFormName;
	}
	
	//如果要求有readonly属性则返回true
	public function isReadOnly()
	{
		return $this->bReadOnly ;
	}
	
	public function setReadOnly($bReadOnly)
	{
		$this->bReadOnly = $bReadOnly;
	}
	
	//如果要求有disable属性则返回true
	public function isDisabled()
	{
		return $this->bDisabled ;
	}
	
	public function setDisabled($bDisabled)
	{
		$this->bDisabled = $bDisabled;
	}
	
	private $sFormName;
	
	private $value;
	
	private $aVerifiers;
	
	private $bReadOnly = false;
	
	private $bDisabled = false;
}

?>