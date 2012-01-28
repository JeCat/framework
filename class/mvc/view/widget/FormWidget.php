<?php
namespace org\jecat\framework\mvc\view\widget;

use org\jecat\framework\bean\BeanFactory;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\verifier\IVerifier;

use org\jecat\framework\verifier\VerifierManager;
use org\jecat\framework\verifier\VerifyFailed;
use org\jecat\framework\message\Message;
use org\jecat\framework\util\IDataSrc;

class FormWidget extends Widget implements IViewFormWidget
{	
	static public function createBean(array & $arrConfig,$sNamespace='*',$bBuildAtOnce,\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		$sClass = get_called_class() ;
		$aBean = new $sClass() ;
		if($bBuildAtOnce)
		{
			$aBean->buildBean($arrConfig,$sNamespace,$aBeanFactory) ;
		}
		return $aBean ;
	}
	/**
	 * @wiki /mvc/视图/表单控件/表单控件/Bean配置数组
	 *
	 * value mixed 指定控件的值,用于指定值的参数可能是任何类型,如何体现这些值由控件对象自行处理.一般来说,如果是input标签控件,这个值会放到input标签的value属性中,如果是select标签,这个值会让select标签选定特定的选项,其他控件也是类似的功能
	 * valueString string 用string值指定控件的值,如果是input标签控件,这个值会放到input标签的value属性中,如果是select标签,这个值会让select标签选定特定的选项,其他控件也是类似的功能
	 * formName string 控件在表单中的name值,无特殊需求不必指定,默认使用控件的id作为name
	 * readOnly boolean 控件的只读特性,实质上是在控件的html上指定readonly属性
	 * disabled boolean 控件是否禁用,实质上是在控件的html上指定disabled属性
	 * verifiers array 控件附带的校验器列表,每个数组元素都是一个校验器的初始化数组
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		parent::buildBean($arrConfig,$sNamespace) ;
	
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
		
    	$aBeanFactory = BeanFactory::singleton() ;
		
		// 将 verifier:xxxx 转换成 verifiers[] 结构
		$aBeanFactory->_typeKeyStruct($arrConfig,array(
				'verifier:'=>'verifiers' ,
		)) ;
		
		// verifiers
		if(!empty($arrConfig['verifiers']))
		{
			foreach($arrConfig['verifiers'] as $key=>&$arrVerifierConf)
			{
    			// 自动配置缺少的 class 属性
    			$aBeanFactory->_typeProperties( $arrVerifierConf, 'length', is_int($key)?null:$key, 'class' ) ;
    			
				$this->addVerifier(
						$aBeanFactory->createBean($arrVerifierConf,$sNamespace,true)
						, isset($arrVerifierConf['message'])? $arrVerifierConf['message']: null
						, isset($arrVerifierConf['callback'])? $arrVerifierConf['callback']: null
						, isset($arrVerifierConf['callback.argvs'])? $arrVerifierConf['callback.argvs']: null
				) ;
			}
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
				
				if( !$this->aVerifiers->verify( $this->value(), true ) )
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