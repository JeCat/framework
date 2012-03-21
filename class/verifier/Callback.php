<?php
namespace org\jecat\framework\verifier;

use org\jecat\framework\lang\Type;

use org\jecat\framework\bean\BeanConfException;

use org\jecat\framework\bean\IBean;
use org\jecat\framework\message\Message;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class Callback extends Object implements IVerifier ,IBean
{
	public function __construct($fnCallback=null,array $arrArgvs=null)
	{
		$this->fnCallback = $fnCallback ;
		$this->arrArgvs = $arrArgvs ;
	}
	
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
	 * @wiki /MVC模式/数据交换和数据校验/数据校验
	 * ==Email格式校验==
	 * 验证输入的Email格式的正确性
	 */
	public function buildBean(array & $arrConfig,$sNamespace='*',\org\jecat\framework\bean\BeanFactory $aBeanFactory=null)
	{
		if( !is_callable($arrConfig['callback']) )
		{
			throw new BeanConfException("callback 类型的校验器的 callback 属性必须是一个回调函数") ;
		}
		$this->fnCallback = $arrConfig['callback'] ;
		
		if( array_key_exists('argvs',$arrConfig) )
		{
			$this->arrArgvs = Type::toArray($arrConfig['argvs'],Type::toArray_normal) ;
		}
		
		$this->arrBeanConfig = $arrConfig;
	}
	
	public function beanConfig()
	{
		return $this->arrBeanConfig;
	}
	
	public function verify($data,$bThrowException)
	{
		$arrArgvs = array($data) ;
		if($this->arrArgvs)
		{
			$arrArgvs = array_merge($arrArgvs,$this->arrArgvs) ;
		}

		$fnCallback = $this->fnCallback ;
		if(!call_user_func_array($fnCallback,$arrArgvs))
		{
			if ($bThrowException) {
				throw new VerifyFailed ( "数据经过回调函数校验失败" );
			}
			return false;
		}
		return true ;
	}
	
	private $arrBeanConfig = array();
	
	private $fnCallback ;
	
	private $arrArgvs ;
}

?>