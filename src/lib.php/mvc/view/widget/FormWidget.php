<?php
namespace jc\mvc\view\widget ;

use jc\util\IDataSrc;
use jc\pattern\composite\Container;

class FormWidget extends Widget implements IViewFormWidget
{
	public function value()
	{
		return $this->value ;
	}

	public function setValue($data=null)
	{
		$this->value = $data ;
	}

	public function valueToString()
	{
		return strval($this->value()) ;
	}

	public function setValueFromString($data)
	{
		return $this->setValue($data) ;
	}
	
	
	public function setDataFromSubmit(IDataSrc $aDataSrc)
	{
		$this->setValueFromString(
			$aDataSrc->get($this->formName()) 
		) ;
	}
	
	public function dataVerifiers()
	{
		if( !$this->aVerifiers )
		{
			$this->aVerifiers = new Container() ;
		}
		return $this->aVerifiers ;
	}

	public function verifyData()
	{
		if( $this->aVerifiers )
		{
			foreach($this->aVerifiers->iterator() as $aVerifier)
			{
				if( !$aVerifier->verify( $this->value() ) )
				{
					return false ;
				}
			}
		}
		
		return true ;
	}

	public function formName()
	{
		return $this->sFormName===null? $this->id(): $this->sFormName ;
	}
	
	public function setFormName($sFormName)
	{
		$this->sFormName = $sFormName ;
	}
	
	private $sFormName ;
	
	private $value ;
	
	private $aVerifiers ;
}

?>