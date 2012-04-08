<?php
namespace org\jecat\framework\ui\xhtml\compiler\node;

use org\jecat\framework\io\InputStreamCache;
use org\jecat\framework\lang\compile\JavascriptTranslaterFactory;
use org\jecat\framework\lang\compile\generators\translater\JavascriptTranslater;
use org\jecat\framework\io\OutputStreamBuffer;
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

?>