<?php
namespace org\jecat\framework\lang\oop ;

use org\jecat\framework\system\Application;
use org\jecat\framework\fs\IFile;
use org\jecat\framework\lang\Object;
use org\jecat\framework\lang\compile\ClassCompileException;
use org\jecat\framework\fs\FileSystem;
use org\jecat\framework\io\OutputStream;
use org\jecat\framework\io\InputStream;
use org\jecat\framework\lang\compile\CompilerFactory;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Assert;

class ClassLoader extends Object implements \Serializable
{
	const SEARCH_COMPILED = 1 ;		// 在编译文件中搜索类
	const SEARCH_SOURCE = 2 ;			// 在源文件中搜索类
	
	const AUTO_COMPILE = 7 ;			// 搜索时自动编译	
	const SEARCH_COMPILED_FIRST = 3 ;	// 搜索时编译文件优先：SEARCH_COMPILED | SEARCH_SOURCE
	const SEARCH_ALL = 3 ;				// 搜索编译文件和源文件：SEARCH_COMPILED | SEARCH_SOURCE
	const SEARCH_DEFAULT = 7 ;			// SEARCH_COMPILED_FIRST | AUTO_COMPILE
	
	public function __construct()
	{		
		spl_autoload_register( array($this,"load") ) ;
	}
	
	/**
	 * @return ClassLoader
	 */
	static public function singleton($bCreateNew=true,$createArgvs=null,$sClass=null)
	{
		return parent::singleton($bCreateNew,$createArgvs,$sClass) ;
	}
	
	/**
	 * @return Package
	 */
	public function addPackage($sNamespace,$sSourceFolder=null) 
	{
		$aFs = FileSystem::singleton() ;
		
		$aSourceFolder = null ;
		if( $sSourceFolder and !$aSourceFolder=$aFs->findFolder($sSourceFolder) )
		{
			throw new Exception(
					"注册 class package (%s)时，提供的class源文件目录不存在：%s"
					, array($sNamespace,$sSourceFolder)
			) ;
		}
		
		$aPackage = new Package($sNamespace,$aSourceFolder) ;
		$this->arrPackages[$sNamespace] = $aPackage ;
		return $aPackage ;
	}
	
	public function removePackage($aPackage){
		foreach($this->packageIterator() as $package){
			if($aPackage->folder()->path() === $package->folder()->path() ){
				unset( $this->arrPackages[$package->ns()] );
			}
		}
	}
		
	/**
	 * 自动加载类文件
	 */
	public function load($sClassName)
	{
		$fTime = microtime(true) ;
		
		// 从缓存的 classpath 中加载类
		if( $this->bEnableClassCache and isset($this->arrClassPathCache[$sClassName]) and is_file($this->arrClassPathCache[$sClassName]) )
		{
			if( is_file($this->arrClassPathCache[$sClassName]) )
			{
				include_once $this->arrClassPathCache[$sClassName] ;
		
				$this->fLoadTime+= microtime(true) - $fTime ;
				return ;
			}
			else
			{
				unset($this->arrClassPathCache[$sClassName]) ;
			}
		}
		
		// 搜索类
		try{
			if( $aClassFile=$this->searchClass($sClassName) )
			{
				$this->arrClassPathCache[$sClassName] = $aClassFile->url() ;
				
				$aClassFile->includeFile(false,true) ;
			}
		}
		catch (ClassCompileException $e)
		{
			echo $e->message(). " <br />\r\n" ;
			if( $aClassSource=$e->classSouce() )
			{
				echo "class source file :" . $aClassSource->url(). " <br />\r\n" ;
			}
			if( $aToken=$e->causeToken() )
			{
				echo "problem on line: " , $aToken->line() , ", position:", $aToken->position(), " <br />\r\n" ;
				echo "token source: “" , $aToken->sourceCode(), "” <br />\r\n" ;
			}
			echo "<pre>",$e->getTraceAsString(),"</pre>" ;
			exit() ;
		}
		
		$this->fLoadTime+= microtime(true) - $fTime ;
	}
	
	public function searchClass($sClassName,$nSearchFlag=null)
	{
		if($nSearchFlag===null)
		{
			$nSearchFlag = self::SEARCH_COMPILED_FIRST ;
			if( $this->isEnableClassCompile() and !$this->skipForClassCompile($sClassName) )
			{
				$nSearchFlag|= self::AUTO_COMPILE ;
			}
		}
		
		if( ($nSearchFlag&self::SEARCH_COMPILED)==self::SEARCH_COMPILED )
		{
			$aCmpdPackage = $this->compiler()->compiledPackage() ;
			$sFullInnerPath = dirname( str_replace('\\','/',$sClassName) ) ;
		}
		
		for( end($this->arrPackages); $aSrcPackage=current($this->arrPackages); prev($this->arrPackages) )
		{
			if( !list($sInnerPath,$sShortClassName)=$aSrcPackage->parsePath($sClassName) )
			{
				continue ;
			}
			
			// 只搜索编译文件
			if( ($nSearchFlag&self::SEARCH_ALL)==self::SEARCH_COMPILED )
			{
				if( $aCmpdClassFile=$aCmpdPackage->searchClassEx($sFullInnerPath,$sShortClassName) )
				{
					return $aCmpdClassFile ;
				}
			}
			
			// 只搜索源文件
			else if( ($nSearchFlag&self::SEARCH_ALL)==self::SEARCH_SOURCE )
			{
				if( $aSrcClassFile=$aSrcPackage->searchClassEx($sInnerPath,$sShortClassName) )
				{
					return $aSrcClassFile ;
				}
			}
			
			// 编译文件优先
			else if( ($nSearchFlag&self::SEARCH_COMPILED_FIRST)==self::SEARCH_COMPILED_FIRST )
			{
				// 编译后的文件
				$aCmpdClassFile = $aCmpdPackage->searchClassEx($sFullInnerPath,$sShortClassName) ;
			
				// 找到编译后的文件，且不要求自动编译，则直接返回
				if( $aCmpdClassFile and ($nSearchFlag&self::AUTO_COMPILE)!=self::AUTO_COMPILE )
				{
					return $aCmpdClassFile ;
				}
			
				// 源文件
				$aSrcClassFile = null ;
				if( $nSearchFlag&self::SEARCH_SOURCE )
				{
					$aSrcClassFile = $aSrcPackage->searchClassEx($sInnerPath,$sShortClassName) ;
				}
			
				// 确定是否需要自动编译（找到源文件，且提供了编译目录）
				if( ($nSearchFlag&self::AUTO_COMPILE)==self::AUTO_COMPILE and $aSrcClassFile and $aCmpdPackage->folder() )
				{
					// 确定编译文件是否失效（编译文件不存在，最后修改时间小于源文件，文件为空）
					if( !$aCmpdClassFile or $aCmpdClassFile->modifyTime()<$aSrcClassFile->modifyTime() or !$aCmpdClassFile->length() )
					{
						// 创建编译文件
						if( !$aCmpdClassFile )
						{
							$aCmpdClassFile = $aCmpdPackage->createClassFile($sFullInnerPath,$sShortClassName) ;
						}
						
						// 编译文件
						try{
							$this->aCompiler->compile( $aSrcClassFileReader=$aSrcClassFile->openReader(), $aCmpdClassFileReader=$aCmpdClassFile->openWriter() ) ;
							$this->arrCompiledClasses[] = $sClassName ;
						}
						catch (ClassCompileException $e)
						{
							$e->setClassSouce($aSrcClassFile) ;
							throw $e ;
						}
						
						$aSrcClassFileReader->close() ;
						$aCmpdClassFileReader->close() ;
					}
				}
				
				// 返回
				if( $aCmpdClassFile )
				{
					return $aCmpdClassFile ;
				}
				else if( $aSrcClassFile )
				{
					return $aSrcClassFile ;
				}
			}
			
		}
	}
		
	
	/**
	 * @return \Iterator
	 */
	public function namespaceIterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator( array_keys($this->arrPackages) ) ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function packageIterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator( $this->arrPackages ) ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function classIterator()
	{
		return new ClassIterator( $this ) ;
	}

	/**
	 * @return org\jecat\framework\lang\compile\Compiler
	 */
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
	
	public function totalLoadTime()
	{
		return $this->fLoadTime ;
	}

	public function serialize()
	{
		$arrData = array(
			'arrPackages' => array() ,
			'arrClassPathCache' => &$this->arrClassPathCache ,
		) ;
		
		foreach($this->arrPackages as $aPackage)
		{
			$arrData['arrPackages'][] = array(
				$aPackage->ns(), $aPackage->folder()->path()
			) ;
		}
		
		return serialize($arrData) ;
	}

	public function unserialize($serialized)
	{
		$this->__construct() ;
		
		$arrData = unserialize($serialized) ;
		$this->arrClassPathCache =& $arrData['arrClassPathCache'] ;
		foreach($arrData['arrPackages'] as $arrPackage)
		{
			$this->addPackage($arrPackage[0],$arrPackage[1]) ;
		}
	}
	
	public function enableClassCache()
	{
		return $this->bEnableClassCache ;
	}
	public function setEnableClassCache($bEnable=true)
	{
		$this->bEnableClassCache = $bEnable? true: false ;
	}
	
	public function compiledClasses()
	{
		return $this->arrCompiledClasses ;
	}
	
	private $arrPackages = array() ;

	private $aCompiler = null ;
	
	private $bEnableClassCompile = false ;
	
	private $sSkipClassesForCompile = '`^org\\\\jecat\\\\framework\\\\(util|io|system|lang|pattern)\\\\`' ;
	
	private $fLoadTime = 0 ;
	
	private $arrClassPathCache = array() ;
	private $bEnableClassCache = false ;
	
	private $arrCompiledClasses ;
	
}

?>
