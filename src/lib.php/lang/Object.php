<?php
namespace jc\lang ;

use jc\system\AppFactory;

use jc\system\Application ;

class Object implements IObject
{
	public function __construct()
	{return ;
	
		// “恐龙妈妈”模式: 从调用堆栈上设置 application
		if( $aApp = self::findApplicationOnCallStack(debug_backtrace()) )
		{
			$this->setApplication($aApp) ;
		}
	}

	/**
	 * 创建一个对象，并设置该对象的 application 属性
	 * 
	 * @param	string	$sClassName		类名称（可以是包含完整路径的类名，也可以只是类名通过后面的 $sNamespace 参数指定所属的包名）
	 * @param	array	$arrArgvs		传递给构造函数的参数
	 * @return stdClass
	 */
	public function create($sClassName,$sNamespace='\\',array $arrArgvs=array())
	{		
		$aObject = Factory::createNewObject($sClassName,$sNamespace,$arrArgvs) ;
		
		if( $aObject instanceof IObject )
		{
			$aObject->setApplication($this->application(true)) ;
		}
		
		return $aObject ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\Application
	 */
	public function application($bDefaultGlobal=true)
	{
		if($this->aApplication)
		{
			return $this->aApplication ;
		}
		else 
		{
			return $bDefaultGlobal? Application::singleton(): null ;
		}
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return jc\system\Application
	 */
	public function setApplication(Application $aApp)
	{
		$this->aApplication = $aApp ;
	}
	
	static public function singleton ($bCreateNew=true,$createArgvs=null)
	{
		$sClass = get_called_class() ;
		
		if( !isset(self::$arrGlobalInstancs[$sClass]) )
		{
			if($bCreateNew)
			{
				if($createArgvs)
				{
					self::$arrGlobalInstancs[$sClass] = new $sClass() ;
				}
				
				else 
				{
					self::$arrGlobalInstancs[$sClass] = Factory::createNewObject($sClass,null,(array)$createArgvs) ;
				}
			}
			else 
			{
				return null ;
			}
		}
		
		return self::$arrGlobalInstancs[$sClass] ;
	}
	
	static public function setSingleton (self $aInstance=null)
	{
		$sClass = get_called_class() ;
		
		// 移除全局实例
		if(!$aInstance)
		{
			unset(self::$arrGlobalInstancs[$sClass]) ;
		}
		
		if( !($aInstance instanceof static) )
		{
			throw new Exception('%s::setSingleton() 设置的单件实例必须为一个 %s 类型的对象',array($sClass,$sClass)) ;
		}

		self::$arrGlobalInstancs[$sClass] = $aInstance ;
	}

	/*static public function singleton ($bCreateNew=true)
	{
		// 从调用堆栈上找到 application
		if( !$aApp = self::findApplicationOnCallStack(debug_backtrace()) )
		{
			$aApp = Application::singleton(true) ;
		}
		
		$sClass = get_called_class() ;
		
		return $aApp->singletonInstance($sClass,$bCreateNew) ;
	}
	
	static public function setSingleton (self $aInstance)
	{
		$sClass = get_called_class() ;
		
		if( !($aInstance instanceof static) )
		{
			throw new Exception('%s::setSingleton() 设置的单件实例必须为一个 %s 类型的对象',array($sClass,$sClass)) ;
		}

		// 从调用堆栈上找到 application
		if( !$aApp = self::findApplicationOnCallStack(debug_backtrace()) )
		{
			$aApp = Application::singleton(true) ;
		}

		$aApp->setSingletonInstance($sClass,$aInstance) ;
	}*/
	
	static public function findApplicationOnCallStack(array $arrCallStack)
	{
		foreach($arrCallStack as $arrFunc)
		{
			if( empty($arrFunc['object']) )
			{
				continue ;
			}

			if( ($arrFunc['object'] instanceof Object) and $aApp=$arrFunc['object']->application(false) )
			{
				return $aApp ;
			}
		}
	}
	
	static public function flyweight($sKey/* ... */)
	{		
		$sClass = get_called_class() ;
		
		if( !isset(self::$arrFlyweightInstancs[$sClass]) )
		{
			self::$arrFlyweightInstancs[$sClass] = array() ;
		}
		
		$arrArgs = func_get_args() ;
		$sKey = implode(',', $arrArgs) ;
		
		if( empty(self::$arrFlyweightInstancs[$sClass][$sKey]) )
		{
			self::$arrFlyweightInstancs[$sClass][$sKey] = Factory::createNewObject($sClass,null,$arrArgs) ;
		}
		
		return self::$arrFlyweightInstancs[$sClass][$sKey] ;
	}
		
	static private $arrGlobalInstancs = array() ;
	static private $arrFlyweightInstancs = array() ;
	
	private $aApplication ;
}
?>