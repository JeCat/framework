<?php
namespace jc\system ;

use jc\fs\FileSystem;

use jc\io\OutputStream;

use jc\io\InputStream;

use jc\compile\CompilerFactory;
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
		$aFs = $this->application()->fileSystem() ;
		if( !$aFs->exists($sCompiledFolder) )
		{
			if( !$aFs->createFolder($sCompiledFolder) )
			{
				throw new Exception(
						"注册 class package (%s)时，提供的class编译目录不存在，且无法自动创建。"
						, array($sNamespace,$sCompiledFolder)
				) ;
			}
		}
		
		$this->arrPackages[$sNamespace] = array(
				FileSystem::formatPath($sFolder)
				, FileSystem::formatPath($sCompiledFolder)
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
		if( $aClassPath=$this->searchClass($sClassFullName) )
		{
			$aClassPath->includeFile(false,true) ;
		}
	}
	
	public function searchClass($sClassFullName)
	{
		$nNamespaceEnd = strrpos($sClassFullName,"\\") ;
		$sFullNamespace = substr($sClassFullName,0,$nNamespaceEnd) ;
		$sClassName = substr($sClassFullName,$nNamespaceEnd+1) ;
		
		$aFs = $this->application()->fileSystem() ;
		
		// 逆向遍历所有注册过的包目录
		$arrPackageNames = array_keys($this->arrPackages) ;
		for( end($arrPackageNames); $sPackageName=current($arrPackageNames); prev($arrPackageNames) )
		{
			$nPackageNameLen = strlen($sPackageName) ;
			if( substr($sFullNamespace,0,$nPackageNameLen) == $sPackageName )
			{
				$sClassSubNamespace = str_replace("\\","/",substr($sFullNamespace,$nPackageNameLen)) ;
				
				// 提供了源文件目录
				$aClassSource = $sClassSourceFile = $sClassSourceFolder = null ;
				if($this->arrPackages[$sPackageName][0])
				{
					// 在源文件目录内搜索类
					$sClassSourceFolder = $this->arrPackages[$sPackageName][0].$sClassSubNamespace ;
					$aClassSource = $this->detectClassInFolder($aFs,$sClassSourceFolder,$sClassName,true) ;
				}
			
				// 使用编译文件
				// --------------
				if( $this->isEnableClassCompile() and !$this->skipForClassCompile($sClassFullName) )
				{
					// 在编译目录内搜索类
					$sClassCompiledFolder = $this->arrPackages[$sPackageName][1].'/'.$this->compiler()->strategySignature().$sClassSubNamespace ;
					$aClassCompiled = $this->detectClassInFolder($aFs,$sClassCompiledFolder,$sClassName) ;
					
					// 找到了源文件
					if( $aClassSource )
					{
						if( !$aClassCompiled )
						{
							$aClassCompiled = $aFs->findFile($sClassCompiledFolder.'/'.$sClassName) ;
						}
						
						// 存在编译目录但没有的编译文件，或编译文件过期，需要重新编译
						if( !$aClassCompiled->exists() or $aClassSource->modifyTime()>$aClassCompiled->modifyTime() or !$aClassCompiled->length() )
						{
							// 编译 class 文件
							if( !$aFs->isFolder($aClassCompiled->dirPath()) )
							{
								if( !$aFs->createFolder($aClassCompiled->dirPath()) )
								{
									throw new Exception("无法编译class，创建编译目录失败：%s",$aClassCompiled->dirPath()) ;
								}
							}

							$this->compiler()->compile( $aClassSource->openReader(), $aClassCompiled->openWriter() ) ;
						}
						
						return $aClassCompiled ;					
					}
					
					// 没有找到源文件，但是找到了编译文件， 直接使用编译文件
					else if( $aClassCompiled ) 
					{
						return $aClassCompiled ;
					}
				}
				
				// 跳过编译文件
				// --------------
				else 
				{
					if($aClassSource)
					{
						return $aClassSource ;
					}
				}
			}
		}
		
		return null ;
	}
	
	/**
	 * @return jc\fs\IFile
	 */
	private function detectClassInFolder(FileSystem $aFs,$sPackageFolder,$sClassName)
	{
		foreach($this->arrClassFilenameWraps as $func)
		{
			$sClassFilename = call_user_func_array($func, array($sClassName)) ;
			$sClassFilePath = $sPackageFolder . '/' . $sClassFilename ;
			
			if( $aClass = $aFs->findFile($sClassFilePath) )
			{
				return $aClass ;
			}
		}
		
		return null ;
	}
	
	public function namespaceFolder($sNamespace)
	{
		return isset($this->arrPackages[$sNamespace][0])? $this->arrPackages[$sNamespace][0]: null ;
	}
	
	/**
	 * @return \IIterator
	 */
	public function namespaceIterator()
	{
		return new \jc\pattern\iterate\ArrayIterator( array_keys($this->arrPackages) ) ;
	}
	
	public function compiler()
	{
		if(!$this->aCompiler)
		{
			$this->aCompiler = CompilerFactory::singleton()->create() ;
		}
		
		return $this->aCompiler ;
	}
	
	public function isEnableClassCompile()
	{
		return $this->bEnableClassCompile ; 
	}
	public function enableClassCompile($bEnble=true)
	{
		$this->bEnableClassCompile = $bEnble? true: false ;
	}
	
	public function skipForClassCompile($sClassFullName)
	{
		return preg_match($this->sSkipClassesForCompile,$sClassFullName) ;
	}
	
	private $arrPackages = array() ;
	
	private $arrClassFilenameWraps = array() ;

	private $aCompiler = null ;
	
	private $bEnableClassCompile = false ;
	
	private $sSkipClassesForCompile = '`^jc\\\\(util|io|system|compile|pattern\\\\iterate|pattern\\\\composite|)\\\\`' ;
}

?>