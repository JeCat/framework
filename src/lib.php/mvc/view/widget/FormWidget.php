<?php
namespace jc\mvc\view\widget;

use jc\bean\BeanFactory;

use jc\lang\Exception;

use jc\verifier\IVerifier;

use jc\verifier\VerifierManager;
use jc\verifier\VerifyFailed;
use jc\message\Message;
use jc\util\IDataSrc;

class FormWidget extends Widget implements IViewFormWidget
{	
    /**
     * properties:	
     *  value				mixed
     *  valueString			string
     *  formName			string
     *  readOnly			bool
     *  disabled			bool
     *  exchange			string 		( 在 View中实现 )
     *  verifier.ooxx		array
     *  
     *   
     * @see jc\bean\IBean::build()
     */
	public function build(array & $arrConfig)
	{
		parent::build($arrConfig) ;
	
		if( array_key_exists('value',$arrConfig) )
		{
			$this->setValue($arrConfig['value']) ;
		}
		if( array_key_exists('valueString',$arrConfig) )
		{
			$this->setValueFromString($arrConfig['valueString']) ;
		}
		if( !empty($arrConfig['formName']) )
		{
			$this->setFormName($arrConfig['formName']) ;
		}
		if( array_key_exists('readOnly',$arrConfig) )
		{
			$this->setReadOnly($arrConfig['readOnly']?true:false) ;
		}
		if( array_key_exists('disabled',$arrConfig) )
		{
			$this->setDisabled($arrConfig['disabled']?true:false) ;
		}
		
		// -------------------
		// verifiers
    	$aBeanFactory = BeanFactory::singleton() ;
    	foreach($aBeanFactory->createBeanArray($arrConfig,'verifier:',null,'class') as $aVerifier)
		{
			$arrConfig = $aVerifier->beanConfig() ;
			
			$this->addVerifier(
				$aVerifier
				, isset($arrConfig['word'])? $arrConfig['word']: null
				
				, isset($arrConfig['callback'])? $arrConfig['callback']: null
				, isset($arrConfig['callback.argvs'])? $arrConfig['callback.argvs']: null
			) ;
		}
	}
	
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
					, "%s无效：".$e->getMessage()
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