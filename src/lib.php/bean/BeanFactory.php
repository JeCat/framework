<?php
namespace jc\bean ;

use jc\lang\Exception;

use jc\lang\Object;

class BeanFactory extends Object
{
	/**
	 * @return BeanFactory
	 */
	static public function singleton($bCreateNew=true,$createArgvs=null,$sClass=null)
	{
		$aSingleton = parent::singleton(false,null,__CLASS__) ;
		
		if(!$aSingleton)
		{
			$aSingleton = new self() ;
			
			// mvc
			$aSingleton->registerBeanClass("controller","jc\\mvc\\controller\\Controller") ;
			$aSingleton->registerBeanClass("view","jc\\mvc\\view\\View") ;
			$aSingleton->registerBeanClass("mode","jc\\mvc\\model\\db\\Model") ;
			
			// jecat widgets
			$aSingleton->registerBeanClass("text","jc\\mvc\\view\\widget\\Text") ;
			$aSingleton->registerBeanClass("select","jc\\mvc\\view\\widget\\Select") ;
			$aSingleton->registerBeanClass("list","jc\\mvc\\view\\widget\\SelectList") ;
			$aSingleton->registerBeanClass("checkbox","jc\\mvc\\view\\widget\\CheckBtn") ;
			$aSingleton->registerBeanClass("file","jc\\mvc\\view\\widget\\File") ;
			$aSingleton->registerBeanClass("paginator","jc\\mvc\\view\\widget\\Paginator") ;
			
			// verifyers
			$aSingleton->registerBeanClass("verifier.email","jc\\verifier\\Email") ;
			$aSingleton->registerBeanClass("verifier.length","jc\\verifier\\Length") ;
			$aSingleton->registerBeanClass("verifier.number","jc\\verifier\\Number") ;
			
			self::setSingleton($aSingleton,__CLASS__) ;
		}

		return $aSingleton ;
	}
	
	/**
	 * 通过传入的对象配置数组，创建一个 IBean 对象
	 * @return jc\bean\IBean
	 */
	public function createBean(array &$arrConfig) 
	{
		if( !empty($arrConfig['instance']) )
		{
			
		}
		else if( !empty($arrConfig['config']) )
		{
			
		}
		else if( !empty($arrConfig['class']) )
		{
			$sClass = $this->beanClassNameByAlias($arrConfig['class']) ?: $arrConfig['class'];
		
			if( !class_exists($sClass) )
			{
				throw new Exception("Bean对象配置数组中的 class 属性无效：%s，不存在该名称的类和别名",$arrConfig['class']) ;
			}
			if( is_a($sClass,'jc\\bean\\IBean') )
			{
				throw new Exception("Bean对象配置数组中的 class 属性无效：%s，必须是一个实现 jc\\bean\\IBean 接口的类",$arrConfig['class']) ;
			}
			
			$aBean = new $sClass ;
			$aBean->build($arrConfig) ;
		}
		
		else 
		{
			throw new Exception("无法根据配置数组创建 Bean 对象，缺少必须的 instance, config 或 class 属性: %s。",var_export($arrConfig,true)) ;
		}
		
		return $aBean ;
	}
	
	/**
	 * 通过传入的对象配置数组列表，创建一系列 IBean 对象
	 * @return jc\bean\IBean
	 */
	public function createBeanArray(array &$arrConfigArray,$sKeyPrefix,$sDefaultClass=null,$sKeyAs='name') 
	{
		$arrBeans = array() ;
		$nKeyPrefixLen = strlen($sKeyPrefix) ;
		
		foreach($arrConfigArray as $key=>&$arrConfig)
		{
			if( substr($key,0,$nKeyPrefixLen)!=$sKeyPrefix )
			{
				continue ;
			}
			
			// 以数组的 key 作为 name,id 等属性
			if( $sKeyAs and $key=substr($key,$nKeyPrefixLen) )
			{
				if(!isset($arrConfig[$sKeyAs]))
				{
					$arrConfig[$sKeyAs] = strval($key) ;
				}
			}
			
			// 默认的 model class
			if($sDefaultClass)
			{
				if( empty($arrConfig['class']) or empty($arrConfig['instance']) or empty($arrConfig['config']) )
				{
					$arrModelConfig['class'] = $sDefaultClass ;
				}
			}
			
			$arrBeans[] = $this->createBean($arrModelConfig) ;
		}
		
		return $arrBeans ;
	}
	
	/**
	 * 通过传入的对象配置数组列表，创建一系列 IBean 对象
	 * @return jc\bean\IBean
	 */
	public function createBeanArray(array &$arrConfigArray,$sKeyPrefix) 
	{
		
	}
	
	
	public function registerBeanClass($sClassName,$sAlias=null)
	{
		$this->arrBeanClassAlias[ $sAlias ?: $sClassName ] = $sClassName ;
	}
	
	public function beanClassNameByAlias($sAlias)
	{
		return isset($this->arrBeanClassAlias[$sAlias])? $this->arrBeanClassAlias[$sAlias]: null ;
	}
	
	private $arrBeanClassAlias = array() ;
}

?>