<?php
namespace jc\compile\generators ;

use jc\compile\object\FunctionDefine;
use jc\compile\object\TokenPool;
use jc\compile\object\Token;
use jc\compile\IGenerator;
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
			. "\r\n\t\t// ---------------------------------------------------------------------------------"
			. "\r\n\t\t// ALERT: 此文件由 JeCat Class Compiler 自动生成和维护，请不要**直接编辑**此文件！"
			. "\r\n\t\t//   对此文件的任何改动，都会在下次生成时被新生成的文件覆盖。"
		) ;
		
		// 函数结束
		$aBodyEndToken = $aBodyToken->theOther() ;
		$aBodyEndToken->setTargetCode(
			          "// ALERT: 此文件由 JeCat Class Compiler 自动生成和维护，请不要**直接编辑**此文件！"
			. "\r\n\t\t//   对此文件的任何改动，都会在下次生成时被新生成的文件覆盖。"
			. "\r\n\t\t// ---------------------------------------------------------------------------------"
			. "\r\n\t\t".$aBodyEndToken->targetCode()
		) ;
	}
}

?>