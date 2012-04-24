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
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\xhtml\compiler\NodeCompiler;
use org\jecat\framework\ui\ObjectContainer;

class RenderJsCompiler extends NodeCompiler {
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager) 
	{
		$aObject instanceof \org\jecat\framework\ui\xhtml\Node ;
		if( !$sFuncName = $aObject->attributes()->string('function') )
		{
			throw new Exception("render:js 节点缺少function属性(line:%d)",$aObject->line()) ;
		}
		
		$aDev->output(<<<JSCODE
<script>
// 模板渲染函数
function {$sFuncName}(aVariables)
{
	// 输出缓存对像
	var aDevice = {
		_buffer: ''
		, write: function(data){
			this._buffer+= data ;
		}
	}
JSCODE
		) ;
		
		
		
		// 指定的模板文件
		if( $aObject->attributes()->string('template') )
		{
			
		}
		
		// 成对 <render:js> 标签之间的内容
		else if( $aObject->tailTag() )
		{
			$aChildCompiledBuff = new TargetCodeOutputStream() ;
			$aChildCompiledBuff->useHereDoc(false) ;
			$this->compileChildren($aObject,$aObjectContainer,$aChildCompiledBuff,$aCompilerManager) ;
			
			$aJsBuff = new OutputStreamBuffer() ;
			JavascriptTranslaterFactory::singleton()->create()->compile(new InputStreamCache('<?php '.$aChildCompiledBuff->bufferBytes(false)),$aJsBuff) ;
			$aDev->output($aJsBuff) ;
		}
		
		// 当前模板文件
		else
		{
			
		}
		
		
		
		$aDev->output(<<<JSCODE

	return aDevice._buffer ;
}
</script>			
JSCODE
				) ;
	}

}

