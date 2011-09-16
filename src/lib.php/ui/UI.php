<?php

namespace jc\ui ;

use jc\io\IInputStream;

use jc\fs\IFile;
use jc\resrc\ResourceManager;
use jc\lang\Assert;
use jc\lang\Exception;
use jc\util\HashTable;
use jc\io\IOutputStream;
use jc\util\DataSrc;
use jc\lang\Object as JcObject;
use jc\util\IHashTable;

class UI extends JcObject
{
	public function __construct(IFactory $aFactory)
	{
		$this->setFactory($aFactory) ;
	}
	
	/**
	 * return IFactory
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
	 * return SourceFolderManager
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
	 * return CompilerManager
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
	 * return InterpreterManager
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
	 * return IOutputStream
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
	
	public function compileSourceFile($sSourceFile)
	{
		// 定位文件
		$aSrcMgr = $this->sourceFileManager() ;
		list($sNamespace,$sSourceFile) = $aSrcMgr->detectNamespace($sSourceFile) ;
		$aSourceFile = $aSrcMgr->find($sSourceFile,$sNamespace) ;
		if(!$aSourceFile)
		{
			throw new Exception("无法找到指定的源文件：%s:%s",array($sNamespace,$sSourceFile)) ;
		}
		$aCompiledFile = $this->sourceFileManager()->findCompiled($aSourceFile) ;
		
		// 检查编译文件是否有效
		if( !$this->sourceFileManager()->isCompiledValid($aSourceFile,$aCompiledFile) )
		{
			// 解析
			try{
				$aObjectContainer = $this->interpreters()->parse($aSourceFile->openReader()) ;
			}
			catch (\Exception $e)
			{
				throw new Exception("UI引擎在解析模板文件时遇到了错误: %s",$aSourceFile->url(),$e) ;
			}
			
			// 编译
			try{
				$this->compilers()->compile($aObjectContainer,$aCompiledFile->openWriter()) ;
			}
			catch (\Exception $e)
			{
				throw new Exception("UI引擎在编译模板文件时遇到了错误: %s",$aCompiledFile->url(),$e) ;
			}
		}
		
		return $aCompiledFile ;
	}
	
	public function render(IFile $aCompiledFile,IHashTable $aVariables=null,IOutputStream $aDevice=null)
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
				$aDevice = $this->application()->response()->printer() ;
			}
		}
		
		// 模板变量
		if( !$aVariables->has('theRequest') )
		{
			$aVariables->set('theRequest',$this->application()->request()) ;
		}
		$aVariables->set('theDevice',$aDevice) ;		
		
		include $aCompiledFile->url()  ;
	}
	
	public function display($sSourceFile,$aVariables=null,IOutputStream $aDevice=null)
	{
		Assert::type( array('\\jc\\util\\IHashTable','array','null'), $aVariables ) ;
		
		if( is_array($aVariables) )
		{
			$aVariables = new HashTable($aVariables) ;
		}
		
		// compile
		$aCompiledFile = $this->compileSourceFile($sSourceFile) ;
		
		// render
		$this->render($aCompiledFile,$aVariables,$aDevice) ;
	}
	
	private $aSourceFileManager ;
	
	private $aCompilers ;
	
	private $aVariables ;
	
	private $aOutputStream ;

	private $aInterpreters ;
}

?>