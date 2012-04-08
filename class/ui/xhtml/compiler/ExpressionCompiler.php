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

namespace org\jecat\framework\ui\xhtml\compiler ;

use org\jecat\framework\ui\VariableDeclares;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;

class ExpressionCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write(self::compileExpression($aObject->source(),$aObjectContainer->variableDeclares())) ;
	}

	static public function compileExpression($sSource,VariableDeclares $aVarDeclares,$bForceEval=false,$bAloneLine=false)
	{
		$sSource = trim($sSource) ;
		if( !preg_match("/;\\s*/", $sSource) )
		{
			$sSource.= ';' ;
		}
		
		// 分解
		$arrTokens = token_get_all('<?php '.$sSource.'?>') ;
		array_shift($arrTokens) ;
		array_pop($arrTokens) ;
		
		$sLineCode = '' ;
		$arrLines = array() ;
		foreach($arrTokens as $arrOneTkn)
		{
			if( is_array($arrOneTkn) )
			{
				// 变量
				if($arrOneTkn[0]==T_VARIABLE)
				{
					// 变量名
					$sVarName = substr($arrOneTkn[1],1) ;
					$sVarNameNew = 'aVariables->'.$sVarName ;
					
					$sLineCode.= '$'.$sVarNameNew ;
				}
				else 
				{
					$sLineCode.= $arrOneTkn[1] ;
				}
			}
			// 行尾
			else if($arrOneTkn==';')
			{
				$sLineCode = trim($sLineCode) ;
				if( $sLineCode!=='' or $sLineCode!==null )
				{
					$arrLines[] = $sLineCode ;
					$sLineCode = '' ;
				}
			}
			else
			{
				$sLineCode.= $arrOneTkn ;
			}
		}
		
		$sCompiled = implode(";", $arrLines) ;
		
		if( count($arrLines)>1 or $bForceEval )
		{
			// return 最后一行
			$arrLines[] = 'return ' . array_pop($arrLines) ;
			
			// 为 eval 转义
			$sCompiled = addcslashes($sCompiled,'"\\') ;	
			$sCompiled = str_replace('$','\\$',$sCompiled) ;
			
			// 末尾加上 ;
			if( !preg_match("/;\\s*$/",$sCompiled) )
			{
				$sCompiled.= ';' ;
			}
			
			// 套上 eval 返回
			return "eval(\"" . $sCompiled . "\")" ;
		}
		else
		{
			if($bAloneLine)
			{
				if( !preg_match("/;\\s*$/",$sCompiled) )
				{
					$sCompiled.= ';' ;
				}
			}
			
			return $sCompiled ;
		}
	}
}
