<?php
namespace jc\lang\oop ;

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
	public function __construct()
	{
		// 加载 classpath 缓存
		if( $aCache = $this->application()->fileSystem()->findFile("/classpath.php") )
		{
			$arrClassPath = $aCache->includeFile() ;
			if( is_array($arrClassPath) )
			{
				$this->arrClassPathCache = $arrClassPath ;
			}
		}
		
		
		spl_autoload_register( array($this,"load") ) ;
	}

	public function addPackage($sNamespaceOrPackage,$sSourceFolder=null,$sCompiledFolder=null) 
	{
		if( $sNamespaceOrPackage instanceof Package )
		{
			$this->arrPackages[$sNamespaceOrPackage->ns()] = $sNamespaceOrPackage ;
		}
		
		else 
		{
			$aFs = $this->application()->fileSystem() ;
			
			$aCompiledFolder = null ;
			if( $sCompiledFolder )
			{
				if( !$aCompiledFolder=$aFs->find($sCompiledFolder) or !$aCompiledFolder=$aFs->createFolder($sCompiledFolder) )
				{
					throw new Exception(
							"注册 class package (%s)时，提供的class编译目录不存在，且无法自动创建：%s"
							, array($sNamespaceOrPackage,$sCompiledFolder)
					) ;
				}
			}
			
			$aSourceFolder = null ;
			if( $sSourceFolder and !$aSourceFolder=$aFs->findFolder($sSourceFolder) )
			{
				throw new Exception(
						"注册 class package (%s)时，提供的class源文件目录不存在：%s"
						, array($sNamespaceOrPackage,$sSourceFolder)
				) ;
			}
			
			$this->arrPackages[$sNamespaceOrPackage] = new Package($sNamespaceOrPackage,$aSourceFolder,$aCompiledFolder) ;
			$this->arrPackages[$sNamespaceOrPackage]->setClassCompiler($this->compiler()) ;
		}
	}
	
	/**
	 * 自动加载类文件
	 */
	public function load($sClassFullName)
	{
		// 从缓存的 classpath 中加载类
		if( isset($this->arrClassPathCache[$sClassFullName]) and is_file($this->arrClassPathCache[$sClassFullName]) )
		{
			require $this->arrClassPathCache[$sClassFullName] ;
			return ;
		}
		
		// 搜索类
		try{
			if( $aClassFile=$this->searchClass($sClassFullName) )
			{
				$this->arrClassPathCache[$sClassFullName] = $aClassFile->url() ;
				
				require $this->arrClassPathCache[$sClassFullName] ;
				
				return ;
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
	}
	
	public function searchClass($sClassFullName,$nSearchFlag=null)
	{		
		if($nSearchFlag===null)
		{
			$nSearchFlag = Package::SEARCH_COMPILED_FIRST ;
			if( $this->isEnableClassCompile() and !$this->skipForClassCompile($sClassFullName) )
			{
				$nSearchFlag|= Package::AUTO_COMPILE ;
			}
		}
		
		for( end($this->arrPackages); $aPackage=current($this->arrPackages); prev($this->arrPackages) )
		{
			if( $aClassFile=$aPackage->searchClass($sClassFullName,$nSearchFlag) )
			{
				return $aClassFile ;
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
		echo 'load class time:',$this->nLoadTime ;
		
		$aCache = $this->application()->fileSystem()->createFile("/classpath.php") ;
		$aCache->openWriter()->write( '<?php return ' . var_export($this->arrClassPathCache,1) . ' ; ?>' ) ;
	}
	
	private $arrPackages = array() ;

	private $aCompiler = null ;
	
	private $bEnableClassCompile = false ;
	
	private $sSkipClassesForCompile = '`^jc\\\\(util|io|system|lang|pattern)\\\\`' ;
	
	private $nLoadTime = 0 ;
	
	private $arrClassPathCache = array() ;
}

?>