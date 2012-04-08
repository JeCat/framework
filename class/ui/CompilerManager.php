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
namespace org\jecat\framework\ui ;

use org\jecat\framework\io\OutputStreamBuffer;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\fs\File;
use org\jecat\framework\lang\Object as JcObject;

class CompilerManager extends JcObject
{
	public function add($sObjectClass,$sCompilerClass)
	{
		$this->arrCompilers[$sObjectClass] = $sCompilerClass ;
	}
	
	public function remove($sObjectClass)
	{
		unset($this->arrCompilers[$sObjectClass]) ;
	}
	
	public function clear()
	{
		$this->arrCompilers = array() ;
	}
	
	/**
	 * @return ICompiler
	 */
	public function compilerByName($sName)
	{
		return isset($this->arrCompilers[$sName])? $this->arrCompilers[$sName]: null ;
	}
	
	/**
	 * @return ICompiler
	 */
	public function compiler(IObject $aObject)
	{
		for(end($this->arrCompilers);$Compiler=current($this->arrCompilers);prev($this->arrCompilers))
		{
			$sObjectClass = key($this->arrCompilers) ;
			
			if( $aObject instanceof $sObjectClass )
			{
				return is_string($Compiler)?
						$this->arrCompilers[$sObjectClass]=new $Compiler(): $Compiler ;
			}
		}
	}
	
	/**
	 * @return ICompiled
	 */
	public function compile(ObjectContainer $aObjectContainer,IOutputStream $aCompiledOutput,$bPHPTag=true)
	{
		$aTargetCodeStream = new TargetCodeOutputStream ;
		$aTargetCodeStream->open($aCompiledOutput,$bPHPTag) ;
		
		// 变量声明 buffer
		$aBuffVarsDeclare = new OutputStreamBuffer() ;
		$aTargetCodeStream->write($aBuffVarsDeclare) ;
		
		// 编译正文
		foreach($aObjectContainer->iterator() as $aObject)
		{
			$aCompiler = $this->compiler($aObject) ;
			if($aCompiler)
			{
				$aCompiler->compile($aObject,$aObjectContainer,$aTargetCodeStream,$this) ;
			}
		}
		
		// 变量声明
		$aObjectContainer->variableDeclares()->make($aBuffVarsDeclare) ;

		$aTargetCodeStream->close($bPHPTag) ;
	}
	
	public function createCompiledFile(File $aCompiledFile)
	{
		$aCompiledsDir = $aCompiledFile->directory() ;
		if( !$aCompiledsDir->exists() )
		{
			if( !$aCompiledsDir->create() )
			{
				throw new Exception("无法创建编译文件目录：%s",$aCompiledsDir->path()) ;
			}
		}

		if( !$aCompiledFile->exists() )
		{
			$aCompiledFile->create() ;
		}
	}

	public function compileStrategySignture()
	{
		$seed = __CLASS__."\r\n" ;
		foreach($this->arrCompilers as $compiler)
		{
			$seed.= (is_object($compiler)? $compiler->compileStrategySignture(): $compiler)."\r\n" ;
		}
		return md5($seed) ;
	}
	
	private $arrCompilers = array() ;
}
