<?php
namespace jc\lang ;

use jc\system\AppFactory;

use jc\system\Application ;

/**
 * Object 是所有 JeCat 类的基类，它有以下作用：
 * 	1、为 JeCat框架本身 和 基于JeCat框架构建的应用项目 提供了统一的类型。（然而，有时候出于性能的考虑，或其他技术方面的原因，并没有让所有的JeCat框架类从Object派生）；
 *  2、自动设置 Application 对象，记录对象所属的 Application 对象；
 *  3、提供了单件(singleton)模式的实现。 Object 的派生类可以通过 YourClass::singleton() 取得一个该类的单件对象；
 *  4、提供了享员(flyweight)模式的实现。 Object 的派生类可以通过 YourClass::flyweight($arg1,$arg2,...) 取得一个该类的享员对象; flyweight() 方法的参数表，既是创建享员对象的参数，也是用于区分不同享员对象的值；
 *  
 * @author alee
 *
 */
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
		
		return $aInstance ;
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
	
	public function __sleep()
	{
		return $this->arrSerializeProperties ;
	}
	
	public function addPropertyForSerialize($sProperty,$sAccess='public',$sClass=null)
	{
		switch($sAccess)
		{
		case 'private' :
			if(!$sClass)
			{
				throw new Exception(
							"%s::addPropertyForSerialize() 的参数 \$sAccess 为 \"private\"时，\$sClass参数不能省略"
							, array(get_class($this))
				) ;
			}
			$sPropertyName = self::privatePropNameForSerialize($sProperty,$sClass) ;
			break ;
			
		case 'protected' :
			$sPropertyName = self::protectedPropNameForSerialize($sProperty) ;
			break ;
			
		case 'public' :
			$sPropertyName = $sProperty ;
			break ;
			
		default :
			throw new Exception(
						"%s::addPropertyForSerialize() 的参数 \$sAccess 无效（%s）"
						, array(get_class($this),$sAccess)
			) ;
		}
		
		$this->arrSerializeProperties[] = $sPropertyName ;
	}

	/**
	 * 在 __sleep() 魔术函数中，如果直接返回一个 private 属性的名称，则对 子类对象 serialize 操作时 无效。
	 * 此函数 返回一个 任何时候 都有效的  属性名称。
	 * 
	 * @access	public
	 * @param	$sPropertyName		string	属性名称
	 * @param	$sClassName			string	正在载入的 类名 或 接口名
	 * @static
	 * @return	void
	 */
	static public function privatePropNameForSerialize($sPropertyName,$sClassName) 
	{
		return "\0{$sClassName}\0{$sPropertyName}" ;
	}
	
	/**
	 * 在 __sleep() 魔术函数中，如果直接返回一个 protected 属性的名称，则对 子类对象 serialize 操作时 无效。
	 * 此函数 返回一个 任何时候 都有效的  属性名称。
	 *
	 * @access	public
	 * @param	$sPropertyName		string	属性名称
	 * @static
	 * @return	string
	 */
	static public function protectedPropNameForSerialize($sPropertyName) 
	{
		return "\0*\0{$sPropertyName}" ;
	}
		
	static private $arrGlobalInstancs = array() ;
	static private $arrFlyweightInstancs = array() ;
	
	private $arrSerializeProperties = array() ;
	
	private $aApplication ;
}
?>