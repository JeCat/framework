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
namespace org\jecat\framework\lang\aop ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\aop\jointpoint\JointPoint;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\pattern\composite\Container;
use org\jecat\framework\pattern\composite\NamedObject;

class Pointcut extends NamedObject implements \Serializable
{
	static public function createFromToken(FunctionDefine $aFunctionDefine)
	{
		if( !$aClassDefine=$aFunctionDefine->belongsClass() )
		{
			throw new Exception("传入的 \$aFunctionDefine 参数无效，必须是一个类方法的定义Token") ;
		}
				
		try
		{
			$aMethodRef = new \ReflectionMethod($aClassDefine->fullName(),$aFunctionDefine->name()) ;
		}
		catch(\Exception $e)
		{
			throw new Exception("无法找到 Pointcut %s::%s 的定义",array(
					$aClassDefine->fullName()
					, $aFunctionDefine->name()
				),$e) ;
		}
		
		// 检查参数（不能有非可缺省的参数）
		if( $aMethodRef->getNumberOfRequiredParameters() )
		{
			throw new Exception("Pointcut %s::%s() 的定义无效：要求了非可缺省的参数; 请检查该Pointcut的参数表。",array(
					$aClassDefine->fullName()
					, $aFunctionDefine->name()
				)) ;
		}
		
		// 声明为静态方法
		if( $aMethodRef->isStatic() )
		{
			if( !$aMethodRef->isPublic() )
			{
				throw new Exception("Pointcut %s::%s() 声明为 static 类型，static 类型的 Pointcut 必须为 public。",array(
					$aClassDefine->fullName()
					, $aFunctionDefine->name()
				)) ;
			}
			
			$arrJointPoints = call_user_func(array($aClassDefine->fullName(), $aFunctionDefine->name())) ;
		}
		
		// 声明为普通方法
		else 
		{
			try
			{
				$aClassRef = new \ReflectionClass($aClassDefine->fullName()) ;
			}
			catch(\Exception $e)
			{
				throw new Exception("无法找到 Aspect %s 的定义，无法定义Pointcut：%s::%s",array(
						$aClassDefine->fullName()
						, $aClassDefine->fullName()
						, $aFunctionDefine->name()
					),$e) ;
			}
			
			// 检查该类的构造函数的参数
			if( $aConstructor=$aClassRef->getConstructor() and $aConstructor->getNumberOfRequiredParameters() )
			{
				throw new Exception("由于 Pointcut %s::%s() 没有被申明为 static ，则所属的Aspect(%s)的构造函数不能要求非可省的参数。将 Pointcut %s::%s() 声明为 static，或者取消Aspect(%s)的构造函数所要求的非可省参数。",array(
						$aClassDefine->fullName()
						, $aFunctionDefine->name()
						, $aClassDefine->fullName()
						, $aClassDefine->fullName()
						, $aFunctionDefine->name()
						, $aClassDefine->fullName()
					)) ;
			}
			
			$aMethodRef->setAccessible(true) ;
			$arrJointPoints = $aMethodRef->invokeArgs($aClassRef->newInstanceArgs(),array()) ;
		}
		
		$aPointcut = new self($aFunctionDefine->name()) ;
		$aPointcut->sDefineMethod = $aFunctionDefine->name() ;
		
		if( is_array($arrJointPoints) )
		{
			foreach($arrJointPoints as $aJointPoint)
			{
				if( $aJointPoint instanceof JointPoint )
				{
					$aPointcut->jointPoints()->add($aJointPoint) ;
					$aJointPoint->setPointcut($aPointcut) ;
				}
				
				else 
				{
					throw new Exception("Pointcut %s::%s 的定义中，申明了无效的 JointPoint: %s",array(
							$aClassDefine->fullName()
							, $aFunctionDefine->name()
							, var_export($aJointPoint,true)
					) ) ;
				}
			}
		}
		
		return $aPointcut ;
	}

	/**
	 * @return org\jecat\framework\pattern\composite\IContainer
	 */
	public function jointPoints()
	{
		if( !$this->aJointPoints )
		{
			$this->aJointPoints = new Container('org\\jecat\\framework\\lang\\aop\\jointpoint\\JointPoint') ;
		}
		
		return $this->aJointPoints ;
	}
	
	/**
	 * @return org\jecat\framework\pattern\composite\IContainer
	 */
	public function advices()
	{
		if( !$this->aAdvices )
		{
			$this->aAdvices = new Container('org\\jecat\\framework\\lang\\aop\\Advice') ;
		}
		
		return $this->aAdvices ;
	}
	
	public function serialize ()
	{
		$arrData = array(
			'sDefineMethod' =>& $this->sDefineMethod ,
			'aJointPoints' => $this->aJointPoints ,
		) ;
		return serialize( $arrData ) ;
	}
	
	/**
	 * @param serialized
	 */
	public function unserialize ($serialized)
	{
		$arrData = unserialize($serialized) ;
		$this->sDefineMethod =& $arrData['sDefineMethod'] ;
		$this->aJointPoints = $arrData['aJointPoints'] ;
		if($this->aJointPoints)
		{
			foreach($this->aJointPoints->iterator() as $aJointPoints)
			{
				$aJointPoints->setPointcut($this) ;
			}
		}
	}
	
	public function setAspect(Aspect $aAspect)
	{
		$this->aDefineAspect = $aAspect ;
	}
	public function aspect()
	{
		return $this->aDefineAspect ;
	}
	
	private $aAdvices ;
	
	private $aJointPoints ;
	
	private $aDefineAspect ;
	
	private $sDefineMethod ;
}

