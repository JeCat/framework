<?php
namespace org\jecat\framework\bean ;

use org\jecat\framework\fs\Folder;

use org\jecat\framework\resrc\ResourceManager;
use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Object;

class BeanFactory extends Object implements \Serializable
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
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\controller\\Controller","controller") ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\controller\\WebpageFrame","frame") ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\view\\View",'view') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\view\\FormView",'form') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\model\\db\\Model",'model') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\model\\db\\orm\\Prototype",'prototype') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\model\\db\\orm\\Association",'association') ;
			
			// jecat widgets
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\view\\widget\\Text",'text') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\view\\widget\\Select",'select') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\view\\widget\\SelectList",'list') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\view\\widget\\CheckBtn",'checkbox') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\view\\widget\\File",'file') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\view\\widget\\Group",'group') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\view\\widget\\RadioGroup",'radios') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\view\\widget\\paginator\\Paginator",'paginator') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\mvc\\view\\widget\\menu\\Menu",'menu') ;
			
			
			// verifyers
			$aSingleton->registerBeanClass("org\\jecat\\framework\\verifier\\Email",'email') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\verifier\\Length",'length') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\verifier\\Number",'number') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\verifier\\Same",'same') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\verifier\\FileExt",'extname') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\verifier\\FileSize",'filesize') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\verifier\\ImageArea",'imagearea') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\verifier\\ImageSize",'imagesize') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\verifier\\NotEmpty",'notempty') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\verifier\\Version",'version') ;
			
			// auth
			$aSingleton->registerBeanClass("org\\jecat\\framework\\auth\\Authorizer",'authorizer') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\auth\\LoginedPermission",'perm.logined') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\auth\\CallbackPermission",'perm.callback') ;
			$aSingleton->registerBeanClass("org\\jecat\\framework\\auth\\GroupPermission",'perm.group') ;
			
			self::setSingleton($aSingleton,__CLASS__) ;
		}

		return $aSingleton ;
	}
	
	/**
	 * 通过传入的对象配置数组，创建一个 IBean 对象
	 * @return org\jecat\framework\bean\IBean
	 */
	public function createBean(array &$arrConfig,$sNamespace='*',$bAutoBuild=true) 
	{
		if( $sNamespace=='*' and !empty($arrConfig['namespace']) )
		{
			$sNamespace = $arrConfig['namespace'] ;
		}
		
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
		
		$this->loadConfig($arrConfig,$sNamespace) ;
		
		if( !empty($arrConfig['class']) )
		{
			$arrConfig['class'] = $this->beanClassNameByAlias($arrConfig['class']) ?: $arrConfig['class'];
		
			if( !class_exists($arrConfig['class']) )
			{
				throw new BeanConfException("Bean对象配置数组中的 class 属性无效：%s，不存在该名称的类和别名",$arrConfig['class']) ;
			}
			
			if( !Type::hasImplements($arrConfig['class'],'org\\jecat\\framework\\bean\\IBean') )
			{
				throw new BeanConfException("Bean对象配置数组中的 class 属性无效：%s，必须是一个实现 org\\jecat\\framework\\bean\\IBean 接口的类",$arrConfig['class']) ;
			}
			
			return $arrConfig['class']::createBean($arrConfig,$sNamespace,$bAutoBuild,$this) ;
		}
		
		else 
		{
			throw new BeanConfException("无法根据配置数组创建 Bean 对象，缺少必须的 ins, config 或 class 属性: %s。",var_export($arrConfig,true)) ;
		}
	}
	
	/**
	 * @wiki /Bean/合并Bean配置
	 * BeanFactory::mergeConfig() 静态方法可以将第二个参数 $arrConfigB 中的内容递归合并到第一个参数 $arrConfigA 中。
	 * $arrConfigB 中的配置会覆盖 $arrConfigA 中相同的配置。
	 */
	static public function mergeConfig(&$arrConfigA,&$arrConfigB)
	{
		foreach($arrConfigB as $key=>&$item)
		{
			if(is_int($key))
			{
				$arrConfigA[] =& $item ;
				continue ;
			}
			
			// 在两个config中都是数组元素
			if( $arrConfigA and array_key_exists($key,$arrConfigA) and is_array($arrConfigA[$key]) and is_array($item) )
			{
				self::mergeConfig($arrConfigA[$key],$item) ;
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
			throw new BeanConfException("Bean对象配置数组中的 conf 属性无效，找不到指定的配置文件: %s, namespace: %s",array($sConfName,$sNamespace)) ;
		}
		$arrConfigFile = $aFile->includeFile(false,false) ;
		if( !is_array($arrConfigFile) )
		{
			throw new BeanConfException("Bean对象配置文件内容无效: %s，文件必须返回一个 bean 配置数组",$aFile->path()) ;
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
	
	public function loadConfig(&$arrBeanConfig,&$sNamespace)
	{
		if( !empty($arrBeanConfig['conf']) or !empty($arrBeanConfig['config']) )
		{
			$sConfName = isset($arrBeanConfig['conf'])?$arrBeanConfig['conf']:$arrBeanConfig['config'] ;
		
			// 设置 namespace
			if( strstr($sConfName,':')!==false )
			{
				list($sNamespace,) = explode(':', $sConfName, 2) ;
			}
			$arrConfigFile = $this->findConfig( $sConfName, $sNamespace ) ;
			
			// 合并数组
			self::mergeConfig($arrConfigFile,$arrBeanConfig) ;
			$arrBeanConfig = $arrConfigFile ;
		}
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
	 * @return org\jecat\framework\resrc\ResourceManager
	 */
	public function beanFolders()
	{
		if( !$this->aBeanFolders )
		{
			$this->aBeanFolders = new ResourceManager() ;
		}
		return $this->aBeanFolders ;
	}
	
	
	public function serialize ()
	{
		$arrData = array(
				'arrFolderPaths' => array() ,
				'arrBeanClassAlias' => &$this->arrBeanClassAlias ,
		) ;
		foreach($this->beanFolders()->folderNamespacesIterator() as $sNamespace)
		{
			foreach($this->beanFolders()->folderIterator($sNamespace) as $aFolder)
			{
				$arrData['arrFolderPaths'][$sNamespace][]  = $aFolder->path() ;
			}
		}
		return serialize($arrData) ;
	}
	
	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$arrData = unserialize($serialized) ;
		$this->arrBeanClassAlias =& $arrData['arrBeanClassAlias'] ;
		foreach($arrData['arrFolderPaths'] as $sNamespace=>$arrFolderPaths)
		{
			foreach($arrFolderPaths as $sPath)
			{
				if(!$aFolder=Folder::singleton()->findFolder($sPath))
				{
					throw new BeanConfException("恢复BeanFactory时无法找到Bean目录:%s; 只有挂载到系统目录下的目录才能正确序列/反序列化") ;
				}
				$this->beanFolders()->addFolder( $aFolder, $sNamespace ) ;
			}
		}
	}
	
	private $arrBeanClassAlias = array() ;
	
	private $aBeanFolders ;
}

?>