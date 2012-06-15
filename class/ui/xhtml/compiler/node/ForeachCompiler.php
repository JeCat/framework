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
//  正在使用的这个版本是：0.8
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
/**
 * foreach
 * 
 * 成对写法:
 * <foreach exp>
 * [<foreach:else/>]
 * </foreach>
 * 
 * 单行写法:
 * <foreach exp />
 * 	[<foreach:else/>]
 * <foreach:end/>
 * 
 * 
 * for   exp 循环目标
 * key   text/exp 迭代元素的键名/下标,相当于php中foreach语法中的key
 * item  text/exp 迭代元素的变量名,相当于php中foreach语法中的value
 * Item.ref bool  是否按引用取得元素值,默认false
 * idx text/exp 迭代计数变量名,该变量记录当前循环次数
 * 
 * 
 * @author anubis
 *
 */
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /模板引擎/标签
 * @wiki 速查/模板引擎/标签
 * ==<foreach>
 * 
 *  可单行,循环控制语句，循环访问集合以获取所需信息，遍历信息的功能
 * {|
 *  !属性
 *  !
 *  !类型
 *  !默认值
 *  !说明
 *  |---
 *  |for
 *  |必须
 *  |expression
 *  |
 *  |循环目标
 *  |---
 *  |key
 *  |可选
 *  |text/expression
 *  |
 *  |迭代元素的键名/下标,相当于php中foreach语法中的key
 *  |---
 *  |item
 *  |可选
 *  |text/expression
 *  |
 *  |迭代元素的变量名,相当于php中foreach语法中的value
 *  |}
 *  [example php frameworktest template/test-template/node/ForeachElseCase.html 1 5]
 */

class ForeachCompiler extends NodeCompiler {
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) {
		
		if( !$aDev->hasDeclared('aStackForLoopIsEnableToRun') )
		{
			$aDev->declareVarible('aStackForLoopIsEnableToRun','new \\org\\jecat\\framework\\util\\Stack()') ;
		}
		
		Type::check("org\\jecat\\framework\\ui\\xhtml\\Node", $aObject );
		
		$aAttrs = $aObject->attributes();
		
		if( $aAttrs->has ( 'for' ) ){
			$aForUserExp = $aAttrs->expression ( 'for' );
		}else{
			throw new Exception("foreach tag can not run without 'for' attribute");
		}
		
		$sKeyUserName = $aAttrs->has ( 'key' ) ? $aAttrs->get ( 'key' ) : '' ;
		$sItemUserName = $aAttrs->has ( 'item' ) ? $aAttrs->get ( 'item' ) : '' ;
		$bItemRef = $aAttrs->has ( 'item.ref' ) ? $aAttrs->bool('item.ref') : false ;
		$sIdxUserName = $aAttrs->has ( 'idx' ) ? $aAttrs->get ( 'idx' ) : '' ;
		
		$sItemAutoName = NodeCompiler::assignVariableName ( '$__foreach_item_' ) ;
		$sKeyAutoName = NodeCompiler::assignVariableName ( '$__foreach_key_' ) ;
		$sIdxAutoName = NodeCompiler::assignVariableName ( '$__foreach_idx_' ) ;
		$sItemRef = $bItemRef? '&': '' ;
		
		
		$aDev->putCode ( "\r\n// foreach start ") ;
		$aDev->putCode ( "\$aStackForLoopIsEnableToRun->put(false);
{$sIdxAutoName} = -1;
foreach(",null,false) ;
		$aDev->putCode ($aForUserExp,null,false) ;
		$aDev->putCode (" as {$sKeyAutoName}=>{$sItemRef}{$sItemAutoName}){");

		$aDev->putCode ( "\$bLoopIsEnableToRun = & \$aStackForLoopIsEnableToRun->getRef();
	\$bLoopIsEnableToRun = true;
	{$sIdxAutoName}++;" );
		
		if( !empty($sKeyUserName) )
		{
			$aDev->putCode ( "		\$aVariables[{$sKeyUserName}]={$sKeyAutoName}; ");
		}
		if( !empty($sItemUserName) )
		{
			$aDev->putCode ( "		\$aVariables[{$sItemUserName}]={$sItemAutoName}; ");
		}
		if( !empty($sIdxUserName) )
		{
			$aDev->putCode ( "		\$aVariables[{$sIdxUserName}]={$sIdxAutoName}; ");
		}
		
		//是否是单行标签?
		if(!$aObject->headTag()->isSingle()){
			//循环体，可能会包含foreach:else标签
			$this->compileChildren($aObject,$aObjectContainer,$aDev,$aCompilerManager) ;
			$aDev->putCode("}\r\n") ; // end if   (如果foreach的内容包含foreach:else标签,则此处为foreach:else的end)
		}
	}
}

