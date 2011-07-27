<?php
namespace jc\aop ;

use jc\lang\Assert;
use jc\system\ClassLoader;
use jc\lang\Object;

class AOP extends Object
{

	public function addPointcut($fnAdvice,$sJointClass='*',$sJointMethod='*',$type='around')
	{
		Assert::mustbe(
			in_array($type,self::$arrTypes)
			, "AOP的 pointcut 类型必须为：%s"
			, implode(",", self::$arrTypes)
		) ;

		$this->arrPointcuts[$sJointClass][$sJointMethod][$type] = $fnAdvice ;
	}
	
	public function enable()
	{
		
	}

	public function disable()
	{
		
	}
	
	private $arrPointcuts = array() ;
}

?>