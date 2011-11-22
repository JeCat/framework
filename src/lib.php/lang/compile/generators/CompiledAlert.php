<?php
namespace jc\lang\compile\generators ;

use jc\lang\compile\ClassCompileException;

use jc\lang\compile\object\FunctionDefine;
use jc\lang\compile\object\TokenPool;
use jc\lang\compile\object\Token;
use jc\lang\compile\IGenerator;
use jc\lang\Object;

class CompiledAlert extends Object implements IGenerator
{
	public function generateTargetCode(TokenPool $aTokenPool, Token $aToken)
	{
		if( !($aToken instanceof FunctionDefine) )
		{
			return ;
		}
		
		if( !$aBodyToken=$aToken->bodyToken() )
		{
			return ;
		}
		
		// 函数开始
		$aBodyToken->setTargetCode(
			$aBodyToken->targetCode()
			. "\r\n\t// ---------------------------------------------------------------------------------"
			. "\r\n\t// ALERT: 此文件由 JeCat Class Compiler 自动生成和维护，请不要**直接编辑**此文件！"
			. "\r\n\t//   对此文件的任何改动，都会在下次生成时被新生成的文件覆盖。"
			. "\r\n"
		) ;
		
		// 函数结束
		$aBodyEndToken = $aBodyToken->theOther() ;
		if(!$aBodyEndToken)
		{
			throw new ClassCompileException(null,$aBodyToken,"函数 %s 的函数体没有闭合",$aToken->name()) ;
		}
		$aBodyEndToken->setTargetCode(
			  "\r\n\t// ALERT: 此文件由 JeCat Class Compiler 自动生成和维护，请不要**直接编辑**此文件！"
			. "\r\n\t//   对此文件的任何改动，都会在下次生成时被新生成的文件覆盖。"
			. "\r\n\t// ---------------------------------------------------------------------------------"
			. "\r\n\t".$aBodyEndToken->targetCode()
		) ;
	}
}

?>