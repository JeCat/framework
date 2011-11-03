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
	

	/**
	 * 创建一个对象，并设置该对象的 application 属性
	 * 如果没有提供参数 $sClassName ， 则使用延迟静态绑定的类型
	 * 这个静态方法有以下意义：
	 * 	1、php 中使用 new 运算符的表达式，不是一个普通的表达式，例如以下情况会造成语法错误： (new xxx())->ooo() ;
	 * 	可以使用类方法: xxx:create()->ooo() ;
	 *  2、创建一个对象时，简化 application 对象的设置
	 *  3、实现动态创建对象, 例如:
	 *  	$sClassName = 'ooxx' ;
	 *  	$aObject = $sClassName::create() ;
	 * 	如果 $sClassName 不是从 jc\lang\Object 继承的类，则可以
	 * 		\jc\lang\Object::create(null,null,'ooxx') ;
	 * 
	 * @return Object
	 */
	static public function createInstance($argvs=null,Application $aApp=null,$sClassName=null)
	{
		if($argvs===null)
		{
			$argvs = array() ;
		}
		else 
		{
			$argvs = (array) $argvs ;
		}
		
		if(!$sClassName)
		{
			$sClassName = get_called_class() ;
		}
		
		if( !class_exists($sClassName) )
		{
			throw new Exception("class无效：".$sClassName) ;
		}
		
		// create object
		if(empty($argvs))
		{
			$aObject = new $sClassName() ;
		}
		else 
		{
			$aRefClass = new \ReflectionClass($sClassName) ;
			$aObject = $aRefClass->newInstanceArgs($argvs) ;
		}
		
		// set application
		if( $aApp and $aObject instanceof IObject )
		{
			$aObject->setApplication($aApp) ;
		}
		
		return $aObject ;
	}
	
	/**
	 * @return jc\lang\Object
	 */
	static public function singleton ($bCreateNew=true,$createArgvs=null,$sClass=null)
	{
		if(!$sClass)
		{
			$sClass = get_called_class() ;
		}
					
		if( !isset(self::$arrGlobalInstancs[$sClass]) )
		{
			if($bCreateNew)
			{
				self::$arrGlobalInstancs[$sClass] = self::createInstance($createArgvs,null,$sClass) ;
			}
			else 
			{
				return null ;
			}
		}
		
		return self::$arrGlobalInstancs[$sClass] ;
	}
	
	static public function setSingleton (self $aInstance=null,$sClass=null)
	{
		if(!$sClass)
		{
			$sClass = get_called_class() ;
		}
		
		// 移除全局实例
		if(!$aInstance)
		{
			unset(self::$arrGlobalInstancs[$sClass]) ;
		}
		
		if( !($aInstance instanceof static) )
		{
			throw new Exception('%s::setSingleton() 设置的单件实例必须为一个 %s 类型的对象',array($sClass,$sClass)) ;
		}

		return self::$arrGlobalInstancs[$sClass] = $aInstance ;
	}

	// 以下是将 单件 和 享员 对象保存在所属的 application 中的实现
	// 这个方案由于要在自动判断对象所属的 application 对象，有明显的性能影响
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
	
	static public function setFlyweight($aInstance,$keys,$sClassName=null)
	{		
		if(!$sClassName)
		{
			$sClassName = get_called_class() ;
		}
		
		$keys = (array)$keys ;
		$sKey = implode(',', $keys) ;
		
		if( !isset(self::$arrFlyweightInstancs[$sClassName]) )
		{
			self::$arrFlyweightInstancs[$sClassName] = array() ;
		}
		
		return self::$arrFlyweightInstancs[$sClassName][$sKey] = $aInstance ;
	}
	
	static public function flyweight($keys,$sClassName=null)
	{
		if(!$sClassName)
		{
			$sClassName = get_called_class() ;
		}
		
		$sKey = self::genFlyweightKey($keys) ;
		
		if( !isset(self::$arrFlyweightInstancs[$sClassName]) )
		{
			self::$arrFlyweightInstancs[$sClassName] = array() ;
		}
		
		if( empty(self::$arrFlyweightInstancs[$sClassName][$sKey]) )
		{
			self::$arrFlyweightInstancs[$sClassName][$sKey] = self::createInstance($keys,null,$sClassName) ;
		}
		
		return self::$arrFlyweightInstancs[$sClassName][$sKey] ;
	}
	
	static private function genFlyweightKey(& $keys )
	{
		$keys = (array)$keys ;
		$sKey = '' ;
		
		$nLoopIdx = 0 ;
		foreach($keys as &$element)
		{
			if($nLoopIdx++)
			{
				$sKey.= ',' ;
			}
			
			if( is_array($element) )
			{
				$sKey.= self::genFlyweightKey($element) ;
			}
			else if( is_object($element) )
			{
				$sKey.= spl_object_hash($element) ;
			}
			else
			{
				$sKey.= strval($element) ;
			}
		}
		
		return $sKey ;		
	}
	
		
	static private $arrGlobalInstancs = array() ;
	static private $arrFlyweightInstancs = array() ;
	
	private $aApplication ;
}
?>