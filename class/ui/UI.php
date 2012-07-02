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

namespace org\jecat\framework\ui ;

use org\jecat\framework\locale\Locale;

use org\jecat\framework\mvc\controller\Response;
use org\jecat\framework\mvc\controller\Request;
use org\jecat\framework\io\IInputStream;
use org\jecat\framework\fs\File;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\util\HashTable;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\util\IHashTable;
use org\jecat\framework\lang\Object as JcObject;

class UI extends JcObject
{
	public function __construct(IFactory $aFactory)
	{
		$this->setFactory($aFactory) ;
	}
	
	/**
	 * @return IFactory
	 */
	public function factory()
	{
		return $this->aFactory ;
	}
	
	public function setFactory(IFactory $aFactory)
	{
		$this->aFactory = $aFactory ;
	}
	
	/**
	 * @return SourceFolderManager
	 */
	public function sourceFileManager()
	{
		if(!$this->aSourceFileManager)
		{
			$this->aSourceFileManager = $this->aFactory->createSourceFileManager() ;
		}
		return $this->aSourceFileManager ;
	}
	
	public function setSourceFileManager(SourceFileManager $aSrcMgr)
	{
		$this->aSourceFileManager = $aSrcMgr ;
	}
	
	/**
	 * @return CompilerManager
	 */
	public function compilers()
	{
		if(!$this->aCompilers)
		{
			$this->aCompilers = $this->aFactory->createCompilerManager() ;
		}
		return $this->aCompilers ;
	}
	
	public function setCompilers(CompilerManager $aCompilers)
	{
		$this->aCompilers = $aCompilers ;
	}
	
	/**
	 * @return InterpreterManager
	 */
	public function interpreters()
	{
		if(!$this->aInterpreters)
		{
			$this->aInterpreters = $this->aFactory->createInterpreterManager() ;
		}
		return $this->aInterpreters ;
	}
	
	public function setInterpreters(InterpreterManager $aInterpreters)
	{
		$this->aInterpreters = $aInterpreters ;
	}

	/**
	 * @return IOutputStream
	 */
	public function outputStream()
	{
		return $this->aOutputStream ;
	}
	
	public function setOutputStream(IOutputStream $aOutputStream)
	{
		$this->aOutputStream = $aOutputStream ;
	}
	
	/**
	 * @return IHashTable
	 */
	public function variables()
	{
		if(!$this->aVariables)
		{
			$this->aVariables = new HashTable() ;
		}
		return $this->aVariables ;
	}
	
	public function setVariables(IHashTable $aVariables)
	{
		$this->aVariables = $aVariables ;
	}
	
	/**
	 * @return org\jecat\framework\fs\File
	 */
	public function loadCompiled($sSourceFile)
	{
		// 模板指纹
		$sTemplateSignature = SourceFileManager::makeTemplateSignature($sSourceFile) ;

		if( !defined($sTemplateSignature) )
		{
			// 定位文件
			$aSrcMgr = $this->sourceFileManager() ;
			list($sNamespace,$sSourceFile) = $aSrcMgr->detectNamespace($sSourceFile) ;
		
			$aSourceFile = $aSrcMgr->find($sSourceFile,$sNamespace) ;
			if(!$aSourceFile)
			{
				throw new Exception("无法找到指定的源文件：%s:%s",array($sNamespace,$sSourceFile)) ;
			}
			$aCompiledFile = $this->sourceFileManager()->findCompiled($sSourceFile,$sNamespace) ;
			
			// 检查编译文件是否有效
			if( !$aCompiledFile or !$this->sourceFileManager()->isCompiledValid($aSourceFile,$aCompiledFile) )
			{
				if(!$aCompiledFile)
				{
					if( !$aCompiledFile = $this->sourceFileManager()->findCompiled($sSourceFile,$sNamespace,true) )
					{
						throw new Exception("UI引擎在编译模板文件时遇到了错误，无法创建编译文件：%s",$aSourceFile->path()) ;
					}
				}
				
				$aObjectContainer = new ObjectContainer($sSourceFile,$sNamespace,$sTemplateSignature) ;
				
				try{
					$this->compile($aSourceFile->openReader(),$aCompiledFile->openWriter(),$aObjectContainer) ;
				}
				catch (\Exception $e)
				{
					throw new Exception("UI引擎在编译模板文件时遇到了错误: %s",$aSourceFile->path(),$e) ;
				}
			}
			
			// 加载编译文件
			require_once $aCompiledFile->path() ;
		}
		
		return $sTemplateSignature ;
	}
	
	public function compile(IInputStream $aSourceInput,IOutputStream $aCompiledOutput,ObjectContainer $aObjectContainer,$bIntact=true)
	{
		// 解析
		$this->interpreters()->parse($aSourceInput,$aObjectContainer,$this) ;
		
		// 编译
		$aTargetCodeStream = new TargetCodeOutputStream($aObjectContainer->templateSignature(),$bIntact) ;
		
		$this->compilers()->compile($aObjectContainer,$aTargetCodeStream) ;

		$aCompiledOutput->write($aTargetCodeStream->bufferBytes(true)) ;
	}
	
	public function render($sTemplateSignature,IHashTable $aVariables=null,IOutputStream $aDevice=null,$sSubTemplate='render')
	{
		if(!$aVariables)
		{
			$aVariables = $this->variables() ;
		}
		if(!$aDevice)
		{
			$aDevice = $this->outputStream() ;
			if(!$aDevice)
			{
				$aDevice = Response::singleton()->printer() ;
			}
		}
		
		// 模板变量
		$aRequest = Request::singleton() ;
		if( !$aVariables->has('theRequest') )
		{
			$aVariables->set('theRequest',$aRequest) ;
		}
		if( !$aVariables->has('theParams') )
		{
			$aVariables->set('theParams',Request::singleton()) ;
		}
		$aVariables->set('theDevice',$aDevice) ;
		$aVariables->set('theUI',$this);
		
		$sFunc = $this->subtemplateFunctionName($sTemplateSignature,$sSubTemplate) ;
		if( function_exists($sFunc) )
		{
			return $sFunc($this,$aVariables,$aDevice) ;
		}
	}
	public function subtemplateFunctionName($sTemplateSignature,$sSubTemplate='render') 
	{
		return '_'.$sTemplateSignature.'_'.$sSubTemplate ;
	}
	
	public function display($sSourceFile,$aVariables=null,IOutputStream $aDevice=null)
	{
		Assert::type( array('\\org\\jecat\\framework\\util\\IHashTable','array','null'), $aVariables ) ;
		
		if( is_array($aVariables) )
		{
			$aVariables = new HashTable($aVariables) ;
		}
		
		// compile
		$sTemplateSignature = $this->loadCompiled($sSourceFile) ;
		
		// render
		$this->render($sTemplateSignature,$aVariables,$aDevice) ;
	}
	
	/**
	 * @return org\jecat\framework\locale\Locale
	 */
	public function locale()
	{
		return Locale::singleton() ;
	}
	
	
	private $aSourceFileManager ;
	
	private $aCompilers ;
	
	private $aVariables ;
	
	private $aOutputStream ;

	private $aInterpreters ;
}

