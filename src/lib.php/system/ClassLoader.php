<?php
namespace jc\system ;

use jc\compile\CompilerFactory;
use jc\fs\File;
use jc\lang\Exception;
use jc\lang\Assert;

class ClassLoader extends \jc\lang\Object
{
	public function __construct()
	{
		spl_autoload_register( array($this,"autoload") ) ;
		
		// OOXX.php
		$this->addClassFilenameWrapper(function ($sClassName){ return "{$sClassName}.php" ; }) ;		
		
		// OOXX.class.php
		$this->addClassFilenameWrapper(function ($sClassName){ return "{$sClassName}.class.php" ; }) ;
		
		// class.OOXX.php
		$this->addClassFilenameWrapper(function ($sClassName){ return "class.{$sClassName}.php" ; }) ;
	}

	public function addPackage($sNamespace,$sCompiledFolder,$sFolder=null) 
	{		
		if( !is_dir($sCompiledFolder) )
		{
			if( !mkdir($sCompiledFolder,0755,true) )
			{
				throw new Exception(
						"注册 class package (%s)时，提供的class编译目录不存在，且无法自动创建。"
						, array($sNamespace,$sCompiledFolder)
				) ;
			}
		}
		
		$this->arrPackages[$sNamespace] = array(
				$sFolder?realpath($sFolder):null
				, realpath($sCompiledFolder)
		) ;
	}
	
	public function addClassFilenameWrapper($func) 
	{
		$this->arrClassFilenameWraps[] = $func ;
	}
	
	/**
	 * 自动加载类文件
	 */
	public function autoload($sClassFullName)
	{
		/**
		 * 在系统维护的包中根据路径规则和命名规则搜索类文件
		 */
		if( $sClassPath=$this->searchClass($sClassFullName) )
		{
			require $sClassPath ;
		}
	}
	
	public function searchClass($sClassFullName)
	{
		$nNamespaceEnd = strrpos($sClassFullName,"\\") ;
		$sFullNamespace = substr($sClassFullName,0,$nNamespaceEnd) ;
		$sClassName = substr($sClassFullName,$nNamespaceEnd+1) ;
		
		// 逆向遍历所有注册过的包目录
		$arrPackageNames = array_keys($this->arrPackages) ;
		for( end($arrPackageNames); $sPackageName=current($arrPackageNames); prev($arrPackageNames) )
		{
			$nPackageNameLen = strlen($sPackageName) ;
			if( substr($sFullNamespace,0,$nPackageNameLen) == $sPackageName )
			{
				$sClassSubNamespace = str_replace("\\","/",substr($sFullNamespace,$nPackageNameLen)) ;
				
				// 提供了源文件目录
				$sClassSource = $sClassSourceFile = $sClassSourceFolder = null ;
				if($this->arrPackages[$sPackageName][0])
				{
					// 在源文件目录内搜索类
					$sClassSourceFolder = $this->arrPackages[$sPackageName][0].$sClassSubNamespace ;
					if( $sClassSourceFile = $this->detectClassInFolder($sClassSourceFolder,$sClassName,true) )
					{
						$sClassSource = $sClassSourceFolder . '/' . $sClassSourceFile ;
					}
				}
			
				// 在编译目录内搜索类
				$sClassCompiledFolder = $this->arrPackages[$sPackageName][1].'/'.$this->compiler()->strategySignature().$sClassSubNamespace ;
				$sClassCompiled = $this->detectClassInFolder($sClassCompiledFolder,$sClassName) ;
				
				// 找到了源文件
				if( $sClassSource )
				{
					// 存在编译目录但没有的编译文件，或编译文件过期，需要重新编译
					if( !$sClassCompiled or filemtime($sClassSource)>filemtime($sClassCompiled) )
					{
						$this->compileClass($sClassSource,$sClassCompiledFolder,$sClassSourceFile) ;
					}
					
					return $sClassCompiled? $sClassCompiled: $sClassCompiledFolder.'/'.$sClassSourceFile ;					
				}
				
				// 没有找到源文件，但是找到了编译文件， 直接使用编译文件
				else if( $sClassCompiled ) 
				{
					return $sClassCompiled ;
				}
			}
		}
		
		return null ;
	}
	
	private function detectClassInFolder($sPackageFolder,$sClassName,$bReturnClassFilename=false)
	{
		foreach($this->arrClassFilenameWraps as $func)
		{
			$sClassFilename = call_user_func_array($func, array($sClassName)) ;
			$sClassFilePath = $sPackageFolder . '/' . $sClassFilename ;
			
			if( is_file($sClassFilePath) )
			{
				return $bReturnClassFilename? $sClassFilename: $sClassFilePath ;
			}
		}
		
		return null ;
	}
	
	private function compileClass($sClassSource,$sClassCompiledFolder,$sClassFilename)
	{
		if( !is_dir($sClassCompiledFolder) )
		{
			if( !mkdir($sClassCompiledFolder,0775,true) )
			{
				throw new Exception("无法编译class，创建编译目录失败：%s",$sClassCompiledFolder) ;
			}
		}
		
		$aCompiler = $this->compiler() ; 
		
		if( $aCompiler->isCompiling() )
		{
			if( !copy($sClassSource,$sClassCompiledFolder.'/'.$sClassFilename) )
			{
				throw new Exception("无法创建class的编译文件：%s",$sClassCompiledFolder.'/'.$sClassFilename) ;
			}
		}
		
		else 
		{
			$aCompiler->setCompiling(true) ;
								
			$aSourceFile = new File($sClassSource) ;
			$aCompiledFile = new File($sClassCompiledFolder.'/'.$sClassFilename) ;
			
			$this->compiler()->compile( $aSourceFile->openReader(), $aCompiledFile->openWriter() ) ;
			
			$aCompiler->setCompiling(false) ;
		}
	}
	
	public function namespaceFolder($sNamespace)
	{
		return isset($this->arrPackages[$sNamespace])? $this->arrPackages[$sNamespace]: null ;
	}
	
	/**
	 * @return \IIterator
	 */
	public function namespaceIterator()
	{
		return new \ArrayIterator( array_keys($this->arrPackages) ) ;
	}
	
	public function compiler()
	{
		if(!$this->aCompiler)
		{
			$this->aCompiler = CompilerFactory::singleton()->create() ;
		}
		
		return $this->aCompiler ;
	}
	
	public function disableCompile($bDisable=true)
	{
		$this->bDisableCompile = $bDisable ;
	}
	
	private $arrPackages = array() ;
	
	private $arrClassFilenameWraps = array() ;

	private $aCompiler = null ;
	
	private $bDisableCompile = false ;
}

?>