<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\lang ;

use org\jecat\framework\system\Application;
use org\jecat\framework\util\HashTable;
use org\jecat\framework\pattern\IFlyweightable;
use org\jecat\framework\pattern\ISingletonable;

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
class Object implements IObject, ISingletonable, IFlyweightable
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
	 * @return org\jecat\framework\system\Application
	 */
	public function application($bDefaultGlobal=true)
	{
		return Application::singleton() ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return org\jecat\framework\system\Application
	 */
	public function setApplication(Application $aApp)
	{
		// nothing todo ... 
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
	 * 	如果 $sClassName 不是从 org\jecat\framework\lang\Object 继承的类，则可以
	 * 		\org\jecat\framework\lang\Object::create(null,null,'ooxx') ;
	 * 
	 * @return Object
	 */
	static public function createInstance($argvs=null,$sClassName=null)
	{
		if($argvs===null)
		{
			$argvs = array() ;
		}
		else if(!is_array($argvs))
		{
			$argvs = array($argvs) ;
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
		$aRefClass = new \ReflectionClass($sClassName) ;
		if($aRefClass->isAbstract())
		{
			throw new Exception("无法创建抽象类:%s的实例",$sClassName) ;
		}
		return $aRefClass->newInstanceArgs($argvs) ;
	}
	
	/**
	 * @return org\jecat\framework\lang\Object
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
				self::$arrGlobalInstancs[$sClass] = self::createInstance($createArgvs,$sClass) ;
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
			throw new Exception('%s::setSingleton() 设置的单件实例必须为一个 %s 类型的对象，传入的对象类型为:%s',array($sClass,$sClass,Type::reflectType($aInstance))) ;
		}

		self::$arrGlobalInstancs[$sClass] = $aInstance ;
	}
	
	/*
	 * @return org\jecat\framework\lang\Object
	 */
	static public function switchSingleton(self $aInstance=null,$sClass=null)
	{
		if(!$sClass)
		{
			$sClass = get_called_class() ;
		}
		
		
		$aOri = $sClass::singleton(false) ;
		
		$sClass::setSingleton($aInstance) ;
		
		return $aOri ;
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
		
		$aIns =& self::locationFlyweight($keys,true,$sClassName) ;
		
		return $aIns = $aInstance ;
	}
	
	static public function flyweight($keys,$bCreateNew=true,$sClassName=null)
	{
		if(!$sClassName)
		{
			$sClassName = get_called_class() ;
		}
		
		$aIns =& self::locationFlyweight($keys,$bCreateNew,$sClassName) ;
		
		if( !$aIns and $bCreateNew )
		{
			return $bCreateNew? ($aIns=self::createInstance($keys,$sClassName)): null ;
		}
		else
		{
			return $aIns ;
		}
	}
	
	static private function &locationFlyweight($keys,$bCreateNew,$sClassName)
	{
		if( !isset(self::$arrFlyweightInstancs[$sClassName]) )
		{
			self::$arrFlyweightInstancs[$sClassName] = array() ;
		}
		$arrPool =& self::$arrFlyweightInstancs[$sClassName] ;
		
		if( !is_array($keys) )
		{
			$keys = array($keys) ; // 如果 $keys == null , 就转换成 array(null)
		}
		$sLastKey = (string)array_pop($keys) ;
		
		foreach($keys as &$sKey)
		{
			$sKey = (string) $sKey ;
		
			if( empty($arrPool[$sKey]) )
			{
				if($bCreateNew)
				{
					$arrPool[$sKey] = array('others'=>array()) ;
				}
				else
				{
					$null = null ;
					return $null ;
				}
			}
		
			$arrPool =& $arrPool[$sKey]['others'] ;
		}	
		
		if( empty($arrPool[$sLastKey]['ins']) )
		{
			if($bCreateNew)
			{
				$arrPool[$sLastKey]['ins'] = null ;
			}
			else
			{
				$null = null ;
				return $null ;
			}
		}
		
		return $arrPool[$sLastKey]['ins'] ;
	}
	
	static public function shareObjectMemento()
	{
		return array(self::$arrGlobalInstancs,self::$arrFlyweightInstancs) ;
	}
	static public function setShareObjectMemento(array $arrMemento)
	{
		self::$arrGlobalInstancs = $arrMemento[0] ;
		self::$arrFlyweightInstancs = $arrMemento[1] ;
	}
	
	/**
	 * @return org\jecat\framework\util\IHashTable
	 */
	public function properties($bAutoCreate=true)
	{
		if( !$this->aProperties and $bAutoCreate )
		{
			$this->aProperties = new HashTable() ;
		}
		return $this->aProperties ;
	}
		
	static private $arrGlobalInstancs = array() ;
	static private $arrFlyweightInstancs = array() ;
	
	// private $aApplication ;
    
    private $aProperties ;
}
/**
 * @wiki /Jecat/Jecat简介
 * 
 * Jecat 是一个PHP开源框架.
 */

