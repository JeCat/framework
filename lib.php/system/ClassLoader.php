<?php
namespace jc\system ;

class ClassLoader
{
	public function __construct()
	{
		spl_autoload_register( array($this,"Autoload") ) ;
		
		// OOXX.php
		$this->addClassFilenameWrapper(function($sClassName){
			return "{$sClassName}.php" ;
		}) ;
		
		// class.OOXX.php
		$this->addClassFilenameWrapper(function($sClassName){
			return "class.{$sClassName}.php" ;
		}) ;
		
		// interface.OOXX.php
		$this->addClassFilenameWrapper(function($sClassName){
			return "{$sClassName}.php" ;
		}) ;
	}

	public function addPackage($sFolder,$sNamespace='') 
	{
		$this->arrPackages[$sNamespace] = realpath($sFolder) ;
	}
	
	public function addClassFilenameWrapper($func) 
	{
		$this->arrClassFilenameWraps[] = $func ;
	}
	
	
	public function autoload($sClassFullName)
	{
		$nNamespaceEnd = strrpos($sClassFullName,"\\") ;
		$sFullNamespace = substr($sClassFullName,0,$nNamespaceEnd) ;
		$sClassName = substr($sClassFullName,$nNamespaceEnd+1) ;
		
		$sNamespace = "" ;
		$arrNamespaces = $sFullNamespace? explode("\\", $sFullNamespace): array() ;
		while( $sNamespacePart=array_shift($arrNamespaces) )
		{
			$sNamespaceTemp = $sNamespace.($sNamespace?"\\":'').$sNamespacePart ;
			if( !isset($this->arrPackages[$sNamespaceTemp]) )
			{
				array_unshift($arrNamespaces, $sNamespacePart) ;
				break ;	
			}
			$sNamespace = $sNamespaceTemp ;
		}
				
		if(!isset($this->arrPackages[$sNamespace]))
		{
			return ;
		}
		
		$sSubNamespace = implode("/", $arrNamespaces)."/" ;
		
		foreach($this->arrClassFilenameWraps as $func)
		{
			$sClassFilePath = $this->arrPackages[$sNamespace]."/".$sSubNamespace.$func($sClassName) ;
			
			if( is_file($sClassFilePath) )
			{
				include $sClassFilePath ;
				return ;
			}
		}
	}
	
	
	private $arrPackages = array() ;
	
	private $arrClassFilenameWraps = array() ;
	
}

?>