<?php
namespace jc\lang\oop ;

use jc\fs\IFile;

use jc\lang\Object;
use jc\lang\compile\ClassCompileException;
use jc\fs\FileSystem;
use jc\io\OutputStream;
use jc\io\InputStream;
use jc\lang\compile\CompilerFactory;
use jc\lang\Exception;
use jc\lang\Assert;

class ClassLoader extends Object
{
	const SEARCH_COMPILED = 1 ;		// 在编译文件中搜索类
	const SEARCH_SOURCE = 2 ;			// 在源文件中搜索类
	
	const AUTO_COMPILE = 7 ;			// 搜索时自动编译	
	const SEARCH_COMPILED_FIRST = 3 ;	// 搜索时编译文件优先：SEARCH_COMPILED | SEARCH_SOURCE
	const SEARCH_ALL = 3 ;				// 搜索编译文件和源文件：SEARCH_COMPILED | SEARCH_SOURCE
	const SEARCH_DEFAULT = 7 ;			// SEARCH_COMPILED_FIRST | AUTO_COMPILE
	
	public function __construct(IFile $aClasspathCache=null)
	{
		// 加载 classpath 缓存
		if( $aClasspathCache )
		{
			$arrClassPath = $aClasspathCache->includeFile() ;
			if( is_array($arrClassPath) )
			{
				$this->arrClassPathCache = $arrClassPath ;
			}
			
			$this->aClasspathCache = $aClasspathCache ;
		}
		
		
		spl_autoload_register( array($this,"load") ) ;
	}

	public function addPackage($sNamespace,$sSourceFolder=null,$sCompiledFolder=null) 
	{
		$aFs = $this->application()->fileSystem() ;
		
		$aCompiledFolder = null ;
		if( $sCompiledFolder )
		{
			$sCompiledFolder.= '/' . $this->compiler()->strategySignature() ;
			
			if( !$aCompiledFolder=$aFs->find($sCompiledFolder) and !$aCompiledFolder=$aFs->createFolder($sCompiledFolder) )
			{
				throw new Exception(
						"注册 class package (%s)时，提供的class编译目录不存在，且无法自动创建：%s"
						, array($sNamespace,$sCompiledFolder)
				) ;
			}
		}
		
		$aSourceFolder = null ;
		if( $sSourceFolder and !$aSourceFolder=$aFs->findFolder($sSourceFolder) )
		{
			throw new Exception(
					"注册 class package (%s)时，提供的class源文件目录不存在：%s"
					, array($sNamespace,$sSourceFolder)
			) ;
		}
		
		$this->arrPackages[$sNamespace] = array(
			new Package($sNamespace,$aSourceFolder) , 
			new Package($sNamespace,$aCompiledFolder) ,
		) ;
	}
	
	/**
	 * 自动加载类文件
	 */
	public function load($sClassName)
	{
		$fTime = microtime(true) ;
		
		// 从缓存的 classpath 中加载类
		/*if( isset($this->arrClassPathCache[$sClassName]) and is_file($this->arrClassPathCache[$sClassName]) )
		{
			require $this->arrClassPathCache[$sClassName] ;
			return ;
		}*/
		
		// 搜索类
		try{
			if( $aClassFile=$this->searchClass($sClassName) )
			{
				// $this->arrClassPathCache[$sClassName] = $aClassFile->url() ;
				
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
		
		for( end($this->arrPackages); list($aSrcPackage,$aCmpdPackage)=current($this->arrPackages); prev($this->arrPackages) )
		{
			if( !list($sInnerPath,$sShortClassName)=$aSrcPackage->parsePath($sClassName) )
			{
				continue ;
			}
			
			// 只搜索编译文件
			if( ($nSearchFlag&self::SEARCH_ALL)==self::SEARCH_COMPILED )
			{
				if( $aCmpdClassFile=$aCmpdPackage->searchClassEx($sInnerPath,$sShortClassName) )
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
				$aCmpdClassFile = $aCmpdPackage->searchClassEx($sInnerPath,$sShortClassName) ;
			
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
							$aCmpdClassFile = $aCmpdPackage->createClassFile($sInnerPath,$sShortClassName) ;
						}
						
						// 编译文件
						try{
							$this->aCompiler->compile( $aSrcClassFileReader=$aSrcClassFile->openReader(), $aCmpdClassFileReader=$aCmpdClassFile->openWriter() ) ;
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
	
	public function __destruct()
	{
		if( $this->aClasspathCache )
		{
			$this->aClasspathCache->openWriter()->write( '<?php return ' . var_export($this->arrClassPathCache,1) . ' ; ?>' ) ;
		}
	}
	
	public function totalLoadTime()
	{
		return $this->fLoadTime ;
	}
	
	private $arrPackages = array() ;

	private $aCompiler = null ;
	
	private $bEnableClassCompile = false ;
	
	private $sSkipClassesForCompile = '`^jc\\\\(util|io|system|lang|pattern)\\\\`' ;
	
	private $fLoadTime = 0 ;
	
	private $arrClassPathCache = array() ;
	private $aClasspathCache = array() ;
}

?>