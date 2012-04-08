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
namespace org\jecat\framework\ui\xhtml\compiler\macro;

use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\xhtml\compiler\MacroCompiler;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;

/**
 * @wiki /模板引擎/宏
 * @wiki 速查/模板引擎/宏
 * =={@ }==
 *
 *  {@ }中的内容被顺序的显示，每次只能显示一个,循环显示，直到循环结束
 * {|
 *  !使用方法
 *  !
 *  !说明
 *  !
 *  !
 *  |---
 *  |{@ 1,2}
 *  |
 *  |每次只显示一个数字
 *  |
 *  |
 *  |}
 *  [example php frameworktest template/test-template/macro/CycleMacroCase.html 2 6]
 */

class CycleMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject, TargetCodeOutputStream $aDev, CompilerManager $aCompilerManager)
	{
		$sSource = $aObject->source ();
		
		//如果开头是变量
		if (substr ( $sSource, 0, 1 ) === '$')
		{
			//分辨是定义还是调用
			if( $nEqual = stripos($sSource, '=') and strlen(substr($sSource, $nEqual)) > 0)
			{
				//这是定义
				$sObjName = '$' . substr($sSource, 1, $nEqual-1);
				$arrStrings = $this->getElementsBySource(substr($sSource, $nEqual+1));
				$sArrName = '$' . NodeCompiler::assignVariableName ( 'arrChangByLoopIndex' );
				$aDev->write ( "{$sArrName} = " . var_export ( $arrStrings, true ) . ";
								if(!isset({$sObjName}))
								{
									{$sObjName} = new org\\jecat\\framework\\ui\\xhtml\\compiler\\macro\\Cycle({$sArrName});
								}
								\$aVariables->set( '" . substr($sObjName,1) . "' , {$sObjName} ) ;
								");
			}else{
				//这是调用
				$sObjName = '$' . substr($sSource, 1);
				$aDev->write ( "
								if(isset({$sObjName}))
								{
									{$sObjName}->printArr(\$aDevice);
								}
								" );
				
			}
		}
		//如果开头不是变量，是基本用法
		else
		{
			$sArrName = '$' . NodeCompiler::assignVariableName ( 'arrChangByLoopIndex' );
			$sObjName = '$' . NodeCompiler::assignVariableName ( 'aStrChangByLoopIndex' );
			
			$aDev->write ( "{$sArrName} = " . var_export ( $this->getElementsBySource($sSource), true ) . ";
				if(!isset({$sObjName}))
				{
					{$sObjName} = new org\\jecat\\framework\\ui\\xhtml\\compiler\\macro\\Cycle({$sArrName});
				}
				{$sObjName}->printArr(\$aDevice);
				\$aVariables->set( '" . substr($sObjName,1) . "' ,{$sObjName} ) ;
			" );
		}
	}
	
	/**
	 * Enter description here ...
	 * @param string $sSource
	 * @return array 
	 */
	public function getElementsBySource($sSource)
	{
		$arrStrings = array ();
		$sTemp = '';
		
		//参数 分解成数组
		for($i = 0; $i < strlen ( $sSource ); $i ++)
		{
			if ($sSource [$i] == '\\')
			{
				//转义反斜线
				if ($sSource [$i + 1] == '\\')
				{
					$sTemp .= '\\';
					$i ++;
				}
				//转移逗号
				elseif ($sSource [$i + 1] == ',')
				{
					$sTemp .= ',';
					$i ++;
				
				//转义等号
				}
				continue;
			}
			
			if ($sSource [$i] == ',')
			{
				$arrStrings [] = $sTemp;
				$sTemp = '';
				continue;
			}
			
			$sTemp .= $sSource [$i];
		}
		
		$arrStrings [] = $sTemp;
		
		return $arrStrings;
	}
}

