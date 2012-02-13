<?php

namespace org\jecat\framework\ui ;

use org\jecat\framework\locale\LocaleManager;
use org\jecat\framework\mvc\controller\Response;
use org\jecat\framework\mvc\controller\Request;
use org\jecat\framework\system\Application;
use org\jecat\framework\io\IInputStream;
use org\jecat\framework\fs\IFile;
use org\jecat\framework\resrc\ResourceManager;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\util\HashTable;
use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\util\DataSrc;
use org\jecat\framework\lang\Object as JcObject;
use org\jecat\framework\util\IHashTable;

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
	 * @return org\jecat\framework\fs\IFile
	 */
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
		$aCompiledFile = $this->sourceFileManager()->findCompiled($sSourceFile,$sNamespace) ;
		
		// 检查编译文件是否有效
		if( !$aCompiledFile or !$this->sourceFileManager()->isCompiledValid($aSourceFile,$aCompiledFile) )
		{
			if(!$aCompiledFile)
			{
				if( !$aCompiledFile = $this->sourceFileManager()->findCompiled($sSourceFile,$sNamespace,true) )
				{
					throw new Exception("UI引擎在编译模板文件时遇到了错误，无法创建编译文件：%s",$aSourceFile->url()) ;
				}
			}
			
			$aObjectContainer = new ObjectContainer($sSourceFile,$sNamespace) ;
			
			try{
				$this->compile($aSourceFile->openReader(),$aCompiledFile->openWriter(),$aObjectContainer) ;
			}
			catch (\Exception $e)
			{
				throw new Exception("UI引擎在编译模板文件时遇到了错误: %s",$aSourceFile->url(),$e) ;
			}
		}
		
		return $aCompiledFile ;
	}
	
	public function compile(IInputStream $aSourceInput,IOutputStream $aCompiledOutput,ObjectContainer $aObjectContainer=null,$bPHPTag=true)
	{
		if(!$aObjectContainer)
		{
			$aObjectContainer = new ObjectContainer() ;
		}
		
		// 解析
		$this->interpreters()->parse($aSourceInput,$aObjectContainer,$this) ;
		
		// 编译
		$this->compilers()->compile($aObjectContainer,$aCompiledOutput,$bPHPTag) ;
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
				$aDevice = Response::singleton()->printer() ;
			}
		}
		
		// 模板变量
		if( !$aVariables->has('theRequest') )
		{
			$aVariables->set('theRequest',Request::singleton()) ;
		}
		$aVariables->set('theDevice',$aDevice) ;		
		
		include $aCompiledFile->url()  ;
	}
	
	public function display($sSourceFile,$aVariables=null,IOutputStream $aDevice=null)
	{
		Assert::type( array('\\org\\jecat\\framework\\util\\IHashTable','array','null'), $aVariables ) ;
		
		if( is_array($aVariables) )
		{
			$aVariables = new HashTable($aVariables) ;
		}
		
		// compile
		$aCompiledFile = $this->compileSourceFile($sSourceFile) ;
		
		// render
		$this->render($aCompiledFile,$aVariables,$aDevice) ;
	}
	
	/**
	 * @return org\jecat\framework\locale\Locale
	 */
	public function locale()
	{
		return LocaleManager::singleton()->locale() ;
	}
	
	private $aSourceFileManager ;
	
	private $aCompilers ;
	
	private $aVariables ;
	
	private $aOutputStream ;

	private $aInterpreters ;
}

?>