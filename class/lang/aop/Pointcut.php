<?php
namespace org\jecat\framework\lang\aop ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\aop\jointpoint\JointPoint;
use org\jecat\framework\lang\compile\object\FunctionDefine;
use org\jecat\framework\pattern\composite\Container;
use org\jecat\framework\pattern\composite\NamedObject;
use org\jecat\framework\pattern\iterate\ArrayIterator;
use org\jecat\framework\lang\Object;

class Pointcut extends NamedObject
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
		
		if( is_array($arrJointPoints) )
		{
			foreach($arrJointPoints as $aJointPoint)
			{
				if( $aJointPoint instanceof JointPoint )
				{
					$aPointcut->jointPoints()->add($aJointPoint) ;
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
	
	private $aAdvices ;
	
	private $aJointPoints ;
}

?>