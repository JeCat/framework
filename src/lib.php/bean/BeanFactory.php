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
			$aSingleton->registerBeanClass("jc\\mvc\\model\\db\\Category",'category') ;
			$aSingleton->registerBeanClass("jc\\mvc\\model\\db\\orm\\Prototype",'prototype') ;
			$aSingleton->registerBeanClass("jc\\mvc\\model\\db\\orm\\Association",'association') ;
			
			// jecat widgets
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\Text",'text') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\Select",'select') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\SelectList",'list') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\CheckBtn",'checkbox') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\File",'file') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\Group",'group') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\RadioGroup",'radios') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\paginator\\Paginator",'paginator') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\menu\\Menu",'menu') ;
			$aSingleton->registerBeanClass("jc\\mvc\\view\\widget\\menu\\Item",'menuItem') ;
			
			// verifyers
			$aSingleton->registerBeanClass("jc\\verifier\\Email",'email') ;
			$aSingleton->registerBeanClass("jc\\verifier\\Length",'length') ;
			$aSingleton->registerBeanClass("jc\\verifier\\Number",'number') ;
			$aSingleton->registerBeanClass("jc\\verifier\\Same",'same') ;
			$aSingleton->registerBeanClass("jc\\verifier\\FileExt",'extname') ;
			$aSingleton->registerBeanClass("jc\\verifier\\FileSize",'filesize') ;
			$aSingleton->registerBeanClass("jc\\verifier\\ImageArea",'imagearea') ;
			$aSingleton->registerBeanClass("jc\\verifier\\ImageSize",'imagesize') ;
			$aSingleton->registerBeanClass("jc\\verifier\\NotEmpty",'notempty') ;
			$aSingleton->registerBeanClass("jc\\verifier\\Version",'version') ;
			
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
		if( !empty($arrConfig['ins']) or !empty($arrConfig['instance']) )
		{
			$sInstance = isset($arrConfig['ins'])?$arrConfig['ins']:$arrConfig['instance'] ;
			if( !$aFile = $this->beanFolders()->find($sInstance.'.ins.php',$sNamespace) )
			{
				throw new BeanConfException("Bean对象配置数组中的 ins 属性无效: %s，找不到指定的实例文件",$arrConfig['ins']) ;
			}
			return $aFile->includeFile(false,false) ;
		}
		
		else if( !empty($arrConfig['conf']) or !empty($arrConfig['config']) )
		{
			$sConfName = isset($arrConfig['conf'])?$arrConfig['conf']:$arrConfig['config'] ;
			
			// 设置 namespace 
			if( strstr($sConfName,':')!==false )
			{
				list($sNamespace,) = explode(':', $sConfName, 2) ;
			}
			$arrConfigFile = $this->findConfig( $sConfName, $sNamespace 
			) ;
			// 合并数组
			self::MergeConfig($arrConfigFile,$arrConfig) ;
			$arrConfig = $arrConfigFile ;
		}
		
		if( !empty($arrConfig['class']) )
		{
			$arrConfig['class'] = $this->beanClassNameByAlias($arrConfig['class']) ?: $arrConfig['class'];
		
			if( !class_exists($arrConfig['class']) )
			{
				throw new BeanConfException("Bean对象配置数组中的 class 属性无效：%s，不存在该名称的类和别名",$arrConfig['class']) ;
			}
			
			if( !Type::hasImplements($arrConfig['class'],'jc\\bean\\IBean') )
			{
				throw new BeanConfException("Bean对象配置数组中的 class 属性无效：%s，必须是一个实现 jc\\bean\\IBean 接口的类",$arrConfig['class']) ;
			}
			
			$aBean = new $arrConfig['class'] ;
			
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
	
	static public function MergeConfig(&$arrConfigA,&$arrConfigB)
	{
		foreach($arrConfigB as $key=>&$item)
		{
			if(is_int($key))
			{
				$arrConfigA[] =& $item ;
				continue ;
			}
			
			// 在两个config中都是数组元素
			if( is_array($item) and is_array($arrConfigA[$key]) )
			{
				self::MergeConfig($arrConfigA[$key],$item) ;
			}
			
			// 否则 b元素覆盖a的
			else
			{
				$arrConfigA[$key] =& $item ;
			}
		}
	}
	
	public function createBeanByConfig($sConfName,$sNamespace='*',$aAutoBuild=true)
	{
		$arrConfig = $this->findConfig($sConfName,$sNamespace) ;
		return $this->createBean($arrConfig,$sNamespace,$aAutoBuild) ;
	}
	
	public function findConfig($sConfName,$sNamespace='*') 
	{
		if( !$aFile = $this->beanFolders()->find($sConfName.'.conf.php',$sNamespace) )
		{
			throw new BeanConfException("Bean对象配置数组中的 conf 属性无效，找不到指定的配置文件: %s",$sConfName) ;
		}
		$arrConfigFile = $aFile->includeFile(false,false) ;
		if( !is_array($arrConfigFile) )
		{
			throw new BeanConfException("Bean对象配置文件内容无效: %s，文件必须返回一个 bean 配置数组",$aFile->url()) ;
		}
			
		return $arrConfigFile ;
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
			$this->_typeProperties($arrConfigArray,$sDefaultClass,is_int($key)?null:$key,$sKeyAs) ;			
			$arrBeans[$key] = $this->createBean($arrConfig,$sNamespace,$bAutoBuild) ;
		}
		
		return $arrBeans ;
	}
	
	/**
	 * 将config数组中的 model:xxx 转换为 models[] 结构
	 */
	public function _typeKeyStruct(&$arrConfig,$arrKeys)
	{
		foreach($arrConfig as $sKey=>&$item)
		{    		
			foreach($arrKeys as $sKeyPrefix=>$sContainerName)
			{
				if( strpos($sKey,$sKeyPrefix)===0 )
				{
					$sName = substr($sKey,strlen($sKeyPrefix)) ;
					
					if( !is_array($item) )
					{
						throw new BeanConfException("视图Bean配置的 %s 必须是一个数组",$sKey) ;
					}
					
					$arrConfig[$sContainerName][$sName] = &$item ;
				}
			}
		}
	}
	
	public function _typeProperties(&$arrConfig,$sDefaultClass=null,$sKey=null,$sKeyProperty='name')
	{
		// 以数组的 key 作为 name,id 等属性
		if( $sKey and !isset($arrConfig[$sKeyProperty]) )
		{
			$arrConfig[$sKeyProperty] = $sKey ;
		}
		
		// 默认的 class
		if($sDefaultClass)
		{
			if( empty($arrConfig['class']) and empty($arrConfig['ins']) and empty($arrConfig['instance']) and empty($arrConfig['conf']) and empty($arrConfig['config']) )
			{
				$arrConfig['class'] = $sDefaultClass ;
			}
		}
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