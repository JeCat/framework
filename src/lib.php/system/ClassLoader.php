<?php
namespace jc\system ;

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
		
		// 逆向遍历所有注册过的包目录
		for( end($this->arrPackages); $sPackageName=key($this->arrPackages); prev($this->arrPackages) )
		{
			$nPackageNameLen = strlen($sPackageName) ;
			if( substr($sFullNamespace,0,$nPackageNameLen) == $sPackageName )
			{
				$sPackageFolder = current($this->arrPackages) . str_replace("\\","/",substr($sFullNamespace,$nPackageNameLen)) ;
			
				foreach($this->arrClassFilenameWraps as $func)
				{
					$sClassFilePath = $sPackageFolder . '/' . call_user_func_array($func, array($sClassName)) ;
					
					if( is_file($sClassFilePath) )
					{
						include $sClassFilePath ;
						return ;
					}
				}
			}
		}
		
		return ;
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
	
	
	private $arrPackages = array() ;
	
	private $arrClassFilenameWraps = array() ;
	
}

?>