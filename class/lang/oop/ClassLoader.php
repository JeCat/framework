<?php
namespace org\jecat\framework\lang\oop ;

use org\jecat\framework\fs\Folder;
use org\jecat\framework\system\Application;
use org\jecat\framework\fs\File;
use org\jecat\framework\lang\Object;
use org\jecat\framework\lang\compile\ClassCompileException;
use org\jecat\framework\io\OutputStream;
use org\jecat\framework\io\InputStream;
use org\jecat\framework\lang\compile\CompilerFactory;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Assert;




class ClassLoader extends Object implements \Serializable
{	
	/*
	const SEARCH_COMPILED = 1 ;		// 在编译文件中搜索类
	const SEARCH_SOURCE = 2 ;			// 在源文件中搜索类
	
	const AUTO_COMPILE = 7 ;			// 搜索时自动编译	
	const SEARCH_COMPILED_FIRST = 3 ;	// 搜索时编译文件优先：SEARCH_COMPILED | SEARCH_SOURCE
	const SEARCH_ALL = 3 ;				// 搜索编译文件和源文件：SEARCH_COMPILED | SEARCH_SOURCE
	const SEARCH_DEFAULT = 7 ;			// SEARCH_COMPILED_FIRST | AUTO_COMPILE
	*/
	
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
	public function addPackage($sNamespace,$folder=null,$nPriority=Package::source) 
	{
		if( $sNamespace instanceof Package )
		{
			$aPackage = $sNamespace ;
		}
		else if( $folder instanceof Folder )
		{
			$aPackage = new Package($sNamespace,$folder) ;
		}
		else
		{
			$aPackage = new Package($sNamespace,Package::findFolder($folder)) ;
		}
		
		// 增加一个优先级
		if(!array_key_exists($nPriority,$this->arrPackages))
		{
			$this->arrPackages[$nPriority] = array() ;
			
			// 保证优先级排序
			ksort($this->arrPackages) ;
		}

		$this->arrPackages[$nPriority][$aPackage->ns()] = $aPackage ;
		
		$this->sPackagesSignature = null ;
		
		return $aPackage ;
	}
	
	public function removePackage($aPackage)
	{
		foreach($this->arrPackages[$nPriority] as &$arrPackages)
		{
			$nIdx = array_search($aPackage,$arrPackages,true) ;
			if( $nIdx!==false )
			{
				unset($arrPackages[$nIdx]) ;
			}
		}
		
		$this->sPackagesSignature = null ;
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
		if( $sClassFile=$this->searchClass($sClassName) )
		{
			// $this->arrClassPathCache[$sClassName] = $sClassFile ;
			require ($sClassFile) ;
		}
		
		$this->fLoadTime+= microtime(true) - $fTime ;
	}
	
	public function searchClass($sClassName,$nPriority=Package::all)
	{		
		for(end($this->arrPackages); $arrPackages=&current($this->arrPackages); prev($this->arrPackages))
		{
			if( !(key($this->arrPackages) & $nPriority) )
			{
				continue ;
			}
			
			for(end($arrPackages); $aPackage=current($arrPackages); prev($arrPackages))
			{
				if($sFilepath=$aPackage->searchClass($sClassName))
				{
					return $sFilepath ;
				}
			}
		}
		
		return null ;
	}
		
	
	/**
	 * @return \Iterator
	 */
	public function namespaceIterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator(
			array_keys(
				call_user_func_array('array_merge',$this->arrPackages)
			)
		) ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function packageIterator()
	{
		$arrAllPackages = array() ;
		foreach($this->arrPackages as &$arrPackages)
		{
			$arrAllPackages = array_merge( $arrAllPackages, array_values($arrPackages) ) ;
		}
		return new \org\jecat\framework\pattern\iterate\ArrayIterator( $arrPackages ) ;
	}
	
	/**
	 * @return \Iterator
	 */
	public function classIterator($sNamespace=null)
	{
		return new ClassIterator( $this, $sNamespace ) ;
	}
	
	public function totalLoadTime()
	{
		return $this->fLoadTime ;
	}

	public function serialize()
	{
		// 计算 signature 
		$this->signature() ;
		
		$arrData = array(
			//'arrClassPathCache' => &$this->arrClassPathCache ,
			'arrPackages' => &$this->arrPackages ,
			'sPackagesSignature' => &$this->sPackagesSignature ,
		) ;
		
		return serialize($arrData) ;
	}

	public function unserialize($serialized)
	{
		$this->__construct() ;
		
		$arrData = unserialize($serialized) ;
		//$this->arrClassPathCache =& $arrData['arrClassPathCache'] ;
		$this->sPackagesSignature =& $arrData['sPackagesSignature'] ;
		$this->arrPackages =& $arrData['arrPackages'] ;
	}
	
	public function enableClassCache()
	{
		return $this->bEnableClassCache ;
	}
	public function setEnableClassCache($bEnable=true)
	{
		$this->bEnableClassCache = $bEnable? true: false ;
	}
	
	public function signature()
	{
		if(!$this->sPackagesSignature)
		{
			$sSignature = '' ;
			foreach($this->arrPackages as &$arrPackages)
			{
				foreach($arrPackages as $aPackage)
				{
					$sSignature.= $aPackage->signature() ;
				}
			}
			$this->sPackagesSignature = md5($sSignature) ;
		}
		
		return $this->sPackagesSignature ;
	}
	
	private $arrPackages = array() ;
	
	private $sPackagesSignature ;

	//private $aCompiler = null ;
	
	//private $bEnableClassCompile = false ;
	
	//private $sSkipClassesForCompile = '`^org\\\\jecat\\\\framework\\\\(util|io|system|lang|pattern)\\\\`' ;
	
	private $fLoadTime = 0 ;
	
	private $arrClassPathCache = array() ;
	private $bEnableClassCache = false ;
	
	//private $arrCompiledClasses ;
	
}

