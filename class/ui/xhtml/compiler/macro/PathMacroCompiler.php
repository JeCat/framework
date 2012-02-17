<?php
namespace org\jecat\framework\ui\xhtml\compiler\macro ;

use org\jecat\framework\ui\xhtml\compiler\MacroCompiler ;
use org\jecat\framework\ui\TargetCodeOutputStream;
use org\jecat\framework\ui\CompilerManager;
use org\jecat\framework\ui\IObject;
use org\jecat\framework\ui\ObjectContainer;

/**
 * @wiki /模板引擎/宏
 *
 * {|
 *  !{/}
 *  !
 *  !{/ }可以在宏内使用*.url/*.uri，显示出url地址
 *  |---
 *  !使用方法
 *  !
 *  !作用
 *  !
 *  !
 *  |---
 *  |{/*.uri}
 *  |显示uri的路径
 *  |
 *  |
 *  |
 *  |---
 *  |{/*.url}
 *  |显示url的路径
 *  |
 *  |
 *  |---
 *  |{/*.url.scheme}
 *  |显示协议类型
 *  |
 *  |
 *  |
 *  |---
 *  |{/*.uri.host}
 *  |显示host
 *  |
 *  |
 *  |
 *  |---
 *  |{/*.uri.path}
 *  |显示path的路径
 *  |
 *  |
 *  |
 *  |---
 *  |{/*.uri.query}
 *  |显示query的路径
 *  |
 *  |
 *  |
 *  |}
 *  
 *  [^]注意，url和rui的不同[/^]
 */
/**
 * @author anubis
 * @example /模板引擎/宏/自定义标签:name[1]
 *
 *  通过{/ }标签编译器的代码演示如何编写一个标签编译器
 */

class PathMacroCompiler extends MacroCompiler
{
	public function compile(IObject $aObject,ObjectContainer $aObjectContainer,TargetCodeOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$sContents = trim($aObject->source()) ;
		
		if( $sContents=='*.uri' )
		{
			$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->uri()) ;" ) ;
		}
		else if( substr($sContents,0,5)=='*.url' )
		{
			$sPart = strlen($sContents)>5?
				substr($sContents,5): '' ;
				
			switch($sPart)
			{
			case '' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->url()) ;" ) ;
				break ;
				
			case '.scheme' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->urlScheme()) ;" ) ;
				break ;
				
			case '.host' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->urlHost()) ;" ) ;
				break ;
				
			case '.path' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->urlPath()) ;" ) ;
				break ;
				
			case '.query' :
				$aDev->write( "\$aDevice->write(\$aVariables->get('theRequest')->urlQuery()) ;" ) ;
				break ;
				
			default :
				break ;
			}
		}
	}
}

?>