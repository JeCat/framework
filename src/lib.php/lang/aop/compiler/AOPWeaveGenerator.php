<?php
namespace jc\lang\aop\compiler ;

use jc\lang\compile\IGenerator;
use jc\lang\compile\object\TokenPool;
use jc\lang\aop\Advice;
use jc\lang\Object ;
use jc\lang\compile\object\Token;

abstract class AOPWeaveGenerator extends Object implements IGenerator
{
	/**
	 * 生成织入代码
	 */
	protected function generateAdviceDefine(Advice $aAdvice,$sArgvLst='')
	{
		$sCode = '' ;
		
		// static
		if( $aAdvice->isStatic() )
		{
			$sCode.= 'static ' ;
		}
		
		// public, protected, private
		$sCode.= $aAdvice->access() . ' ' ;
		
		// function and name
		$sCode.= 'function '. $aAdvice->generateWeavedFunctionName() . "({$sArgvLst})\r\n" ;
		
		// body
		$sCode.= "\t{\r\n" ;
		$sCode.= $aAdvice->source() ;
		$sCode.= "\r\n\t}" ;
		
		return new Token(T_STRING,"\r\n\r\n\t".$sCode) ;
	}
}

?>