<?php
namespace jc\mvc\view\widget;

use jc\verifier\VerifyFailed;
use jc\message\Message;
use jc\util\IDataSrc;
use jc\pattern\composite\Container;

class FormWidget extends Widget implements IViewFormWidget
{	
	public function value()
	{
		return $this->value ;
	}
	
	public function setValue($data = null) {
		$this->value = $data;
	}
	
	public function valueToString() {
		return strval ( $this->value () );
	}
	
	public function setValueFromString($data) {
		return $this->setValue ( $data );
	}
	
	public function setDataFromSubmit(IDataSrc $aDataSrc) {
		$this->setValueFromString ( $aDataSrc->get ( $this->formName () ) );
	}
	
	public function dataVerifiers() {
		if (! $this->aVerifiers) {
			$this->aVerifiers = new Container ();
		}
		return $this->aVerifiers;
	}

	public function verifyData()
	{
		if( $this->aVerifiers )
		{
			foreach($this->aVerifiers->iterator() as $aVerifier)
			{
				try{
					
					$aVerifier->verify( $this->value(), true ) ;
					
				} catch (VerifyFailed $e) {
					
					new Message(
						Message::error
						, "栏位%s输入的内容无效：".$e->getMessage()
						, array_merge(array($this->title()),$e->getMessageArgvs())
					) ;
					
					return false ;
				}
			}
		}
		
		return true;
	}
	
	public function formName() {
		return $this->sFormName === null ? $this->id () : $this->sFormName;
	}
	
	public function setFormName($sFormName) {
		$this->sFormName = $sFormName;
	}
	
	//如果要求有readonly属性则返回true
	public function isReadOnly() {
		return $this->bReadOnly ;
	}
	
	public function setReadOnly($bReadOnly) {
		$this->bReadOnly = $bReadOnly;
	}
	
	//如果要求有disable属性则返回true
	public function isDisabled() {
		return $this->bDisabled ;
	}
	
	public function setDisabled($bDisabled) {
		$this->bDisabled = $bDisabled;
	}
	
	private $sFormName;
	
	private $value;
	
	private $aVerifiers;
	
	private $bReadOnly = false;
	
	private $bDisabled = false;
}

?>