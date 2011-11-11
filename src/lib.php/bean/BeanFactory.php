<?php
namespace jc\bean ;

use jc\resrc\ResourceManager;
use jc\lang\Type;
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
			$aSingleton->registerBeanClass("jc\\mvc\\controller\\Controller","controller") ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\View",'view') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\FormView",'form') ;
			$aSingleton->registerBeanClass("jc\\mvc\\model\\db\\Model",'model') ;
			$aSingleton->registerBeanClass("jc\\mvc\\model\\db\\orm\\Prototype",'prototype') ;
			$aSingleton->registerBeanClass("jc\\mvc\\model\\db\\orm\\Association",'association') ;
			
			// jecat widgets
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\Text",'text') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\Select",'select') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\SelectList",'list') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\CheckBtn",'checkbtn') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\File",'file') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\Group",'group') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\RadioGroup",'radiogroup') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\Paginator",'paginator') ;
			
			// verifyers
			$aSingleton->registerBeanClass("jc\\verifier\\Email",'email') ;
			$aSingleton->registerBeanClass("jc\\verifier\\Length",'length') ;
			$aSingleton->registerBeanClass("jc\\verifier\\Number",'number') ;
			$aSingleton->registerBeanClass("jc\\verifier\\Same",'same') ;
			$aSingleton->registerBeanClass("jc\\verifier\\NotEmpty",'notEmpty') ;
			
			self::setSingleton($aSingleton,__CLASS__) ;
		}

		return $aSingleton ;
	}
	
	/**
	 * 通过传入的对象配置数组，创建一个 IBean 对象
	 * @return jc\bean\IBean
	 */
	public function createBean(array &$arrConfig,$sNamespace='*',$bAutoBuild=true) 
	{
		// ins 
		if( !empty($arrConfig['ins']) )
		{			
			if( !$aFile = $this->beanFolders()->find($arrConfig['ins'].'.ins.php',$sNamespace) )
			{
				throw new BeanConfException("Bean对象配置数组中的 ins 属性无效: %s，找不到指定的实例文件",$arrConfig['ins']) ;
			}
			return $aFile->includeFile(false,false) ;
		}
		
		else if( !empty($arrConfig['conf']) )
		{
			$sConfigName = $arrConfig['conf'] ;
						
			if( !$aFile = $this->beanFolders()->find($sConfigName.'.conf.php',$sNamespace) )
			{
				throw new BeanConfException("Bean对象配置数组中的 conf 属性无效，找不到指定的配置文件: %s",$sConfigName) ;
			}
			$arrConfigFile = $aFile->includeFile(false,false) ;
			if( !is_array($arrConfigFile) )
			{
				throw new BeanConfException("Bean对象配置文件内容无效: %s，文件必须返回一个 bean 配置数组",$aFile->url()) ;
			}

			// 合并数组
			$arrConfig = array_merge($arrConfigFile,$arrConfig) ;
		
			// 设置 namespace 
			if( strstr($sConfigName,':')!==false )
			{
				list($sNamespace,) = explode(':', $sConfigName, 2) ;
			}
		}
		
		if( !empty($arrConfig['class']) )
		{
			$sClass = $this->beanClassNameByAlias($arrConfig['class']) ?: $arrConfig['class'];
		
			if( !class_exists($sClass) )
			{
				throw new BeanConfException("Bean对象配置数组中的 class 属性无效：%s，不存在该名称的类和别名",$arrConfig['class']) ;
			}
			
			if( !Type::hasImplements($sClass,'jc\\bean\\IBean') )
			{
				throw new BeanConfException("Bean对象配置数组中的 class 属性无效：%s，必须是一个实现 jc\\bean\\IBean 接口的类",$arrConfig['class']) ;
			}
			
			$aBean = new $sClass ;
			
			if($bAutoBuild)
			{
				$aBean->build($arrConfig,$sNamespace) ;
			}
		}
		
		else 
		{
			throw new BeanConfException("无法根据配置数组创建 Bean 对象，缺少必须的 ins, config 或 class 属性: %s。",var_export($arrConfig,true)) ;
		}
		
		return $aBean ;
	}
	
	/**
	 * 通过传入的对象配置数组列表，创建一系列 IBean 对象
	 * @return array
	 */
	public function createBeanArray(array &$arrConfigArray,$sDefaultClass=null,$sKeyAs='name',$sNamespace='*',$bAutoBuild=true) 
	{
		$arrBeans = array() ;
		
		foreach($arrConfigArray as $key=>&$arrConfig)
		{
			// 以数组的 key 作为 name,id 等属性
			if( $sKeyAs and !isset($arrConfig[$sKeyAs]) and !is_int($key) )
			{
				$arrConfig[$sKeyAs] = strval($key) ;
			}
			
			// 默认的 class
			if($sDefaultClass)
			{
				if( empty($arrConfig['class']) and empty($arrConfig['ins']) and empty($arrConfig['conf']) )
				{
					$arrConfig['class'] = $sDefaultClass ;
				}
			}
			
			$arrBeans[] = $this->createBean($arrConfig,$sNamespace,$bAutoBuild) ;
		}
		
		return $arrBeans ;
	}
	
	public function registerBeanClass($sClassName,$sAlias=null)
	{
		$this->arrBeanClassAlias[ $sAlias ?: $sClassName ] = $sClassName ;
	}
	
	public function beanClassNameByAlias($sAlias)
	{
		return isset($this->arrBeanClassAlias[$sAlias])? $this->arrBeanClassAlias[$sAlias]: null ;
	}
	
	/**
	 * @return jc\resrc\ResourceManager
	 */
	public function beanFolders()
	{
		if( !$this->aBeanFolders )
		{
			$this->aBeanFolders = new ResourceManager() ;
		}
		return $this->aBeanFolders ;
	}
	
	private $arrBeanClassAlias = array() ;
	
	private $aBeanFolders ;
}

?>