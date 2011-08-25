<?php
namespace jc\lang\oop ;

use jc\lang\compile\ClassCompileException;
use jc\lang\compile\Compiler;
use jc\lang\Exception;
use jc\fs\IFile;
use jc\fs\IFolder;

class Package 
{
	const SEARCH_COMPILED = 1 ;			// 在编译文件中搜索类
	const SEARCH_SOURCE = 2 ;			// 在源文件中搜索类
	
	const AUTO_COMPILE = 7 ;			// 搜索时自动编译	
	const SEARCH_COMPILED_FIRST = 3 ;	// 搜索时编译文件优先：SEARCH_COMPILED | SEARCH_SOURCE
	const SEARCH_DEFAULT = 7 ;			// SEARCH_COMPILED_FIRST | AUTO_COMPILE
	
		
	public function __construct($sNamespace,IFolder $aSourceFolder=null,IFolder $aCompiledFolder=null)
	{
		$this->setNamespace($sNamespace) ;
		
		$this->aSourceFolder = $aSourceFolder ;
		$this->aCompiledFolder = $aCompiledFolder ;
		
		// OOXX.php
		$this->addClassFilenameWrapper(function ($sClassName){ return "{$sClassName}.php" ; }) ;		
		
		// OOXX.class.php
		$this->addClassFilenameWrapper(function ($sClassName){ return "{$sClassName}.class.php" ; }) ;
		
		// class.OOXX.php
		$this->addClassFilenameWrapper(function ($sClassName){ return "class.{$sClassName}.php" ; }) ;
	}

	/**
	 * @return jc\fs\IFolder
	 */
	public function sourceFolder()
	{
		return $this->aSourceFolder ;
	}
	/**
	 * @return jc\fs\IFolder
	 */
	public function compiledFolder()
	{
		return $this->aCompiledFolder ;
	}

	/**
	 * (namespace 是 php 的关键词不能做为函数名)
	 */
	public function ns()
	{
		return substr($this->sNamespace,0,-1) ;
	}
	
	protected function setNamespace($sNamespace)
	{
		if( substr($sNamespace,0,1)=='\\' )
		{
			if( strlen($sNamespace)>1 )
			{
				$sNamespace = substr($sNamespace,1) ;
			}
			else 
			{
				$sNamespace = '' ;
			}
		}
		
		$this->sNamespace = $sNamespace.'\\' ;
		$this->nNamespaceLen = strlen($this->sNamespace) ;
	}

	public function searchClass($sClassFullName,$nFlag=self::SEARCH_DEFAULT)
	{
		list($sInnerFolderPath,$sClassName) = $this->parsePath($sClassFullName) ;
		if( $sInnerFolderPath===null and $sClassName===null )
		{
			return null ;
		}
	
		// 编译后的文件
		$aClassCompiled = null ;
		if($this->aCompiler)
		{
			$sClassCompiledInnerPath = 
			$sClassCompiledInnerPath = $sInnerFolderPath?
					($this->aCompiler->strategySignature() .'/'. $sInnerFolderPath):
					$this->aCompiler->strategySignature() ;
		}
		else 
		{
			$sClassCompiledInnerPath = $sInnerFolderPath ;
		}
		
		if( $this->aCompiledFolder and $nFlag&self::SEARCH_COMPILED )
		{
			$aClassCompiled = $this->searchClassFile( $this->aCompiledFolder, $sClassCompiledInnerPath, $sClassName ) ;
		}

		// 找到编译后的文件，且不要求自动编译，则直接返回
		if( $aClassCompiled and ($nFlag&self::AUTO_COMPILE)!=self::AUTO_COMPILE )
		{
			return $aClassCompiled ;
		}
		
		// 源文件
		$aClassSource = null ;
		if( $this->aSourceFolder and $nFlag&self::SEARCH_SOURCE )
		{
			$aClassSource = $this->searchClassFile($this->aSourceFolder,$sInnerFolderPath,$sClassName) ;
		}
		
		// 自动编译(找到源文件，且提供了编译目录)
		if( ($nFlag&self::AUTO_COMPILE)==self::AUTO_COMPILE and $aClassSource and $this->aCompiledFolder )
		{
			if( !$aClassCompiled or $aClassCompiled->modifyTime()<$aClassSource->modifyTime() or !$aClassCompiled->length() )
			{
				if( !$aClassCompiled )
				{
					if( $fnClassFilenameWraps = reset($this->arrClassFilenameWraps) )
					{
						$sClassCompilePath = $sClassCompiledInnerPath . '/' . call_user_func_array($fnClassFilenameWraps, array($sClassName)) ;
					}
					else 
					{
						$sClassCompilePath = $sClassCompiledInnerPath . '/' . $sClassName . '.php' ;
					}
					
					if( !$aClassCompiled=$this->aCompiledFolder->createFile($sClassCompilePath) )
					{
						throw new Exception(
							"无法在以下路径上创建类%s的编译文件：%s",array($sClassFullName,$sClassCompilePath)
						) ;
					}
				}
				
				// 编译文件
				try{
					$this->aCompiler->compile( $aClassSource->openReader(), $aClassCompiled->openWriter() ) ;
				}
				catch (ClassCompileException $e)
				{
					$e->setClassSouce($aClassSource) ;
					throw $e ;
				}
			}
		}
		
		// 返回
		if( $aClassCompiled and $nFlag&self::SEARCH_COMPILED )
		{
			return $aClassCompiled ;
		}
		else if( $aClassSource and $nFlag&self::SEARCH_SOURCE )
		{
			return $aClassSource ;
		}
		else 
		{
			return null ;
		}
	}
	
	/**
	 * @return js\fs\IFile
	 */
	private function searchClassFile(IFolder $aFolder,$sSubFolder,$sClassName)
	{
		foreach($this->arrClassFilenameWraps as $func)
		{
			$sClassFilename = call_user_func_array($func, array($sClassName)) ;
			$sClassFilePath = $sSubFolder? ($sSubFolder . '/' . $sClassFilename): $sClassFilename ;
			
			if( $aFile=$aFolder->findFile($sClassFilePath) and $aFile instanceof IFile )
			{
				return $aFile ;
			}
		}
		
		return null ;
	}
	
	private function parsePath($sClassName)
	{
		if( $this->nNamespaceLen===0 )
		{
			$sPath = $sClassName ;
		}
		
		else if( substr($sClassName,0,$this->nNamespaceLen)===$this->sNamespace )
		{
			$sPath = substr($sClassName,$this->nNamespaceLen) ;
		}
		
		else
		{
			return array(null,null) ;
		}
		
		$pos = strrpos($sPath,'\\') ;
		if( $pos!==false )
		{
			return array(
				str_replace('\\', '/', substr($sPath,0,$pos))
				, substr($sPath,$pos+1)
			) ;
		}
		else 
		{
			return array('',$sPath) ;
		}
	}

	public function addClassFilenameWrapper($func) 
	{
		$this->arrClassFilenameWraps[] = $func ;
	}

	public function setClassCompiler(Compiler $aCompiler)
	{
		$this->aCompiler = $aCompiler ;
	}
	/**
	 * @return Compiler
	 */
	public function classCompiler(Compiler $aCompiler)
	{
		return $this->aCompiler ;
	}
	
	private $sNamespace ;
	
	private $nNamespaceLen = 0 ;
	
	private $aSourceFolder ;
	
	private $aCompiledFolder ;
	
	private $aCompiler ;
	
	private $arrClassFilenameWraps = array() ;
}

?>