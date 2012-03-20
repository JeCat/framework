<?php
namespace org\jecat\framework\lang\oop ;

use org\jecat\framework\fs\FSIterator;

class ClassIterator extends \ArrayIterator
{
	public function __construct(ClassLoader $aClassLoader,$sNamespace=null,$nPriority=Package::all)
	{
		$arrClasses = array() ;
		foreach($aClassLoader->packageIterator($nPriority) as $aPackage)
		{
			if($sNamespace)
			{
				$sPackageNamespace = $aPackage->ns() ;
				if( $sNamespace == $sPackageNamespace )
				{
					$sSubNs = null ;
				}
				// 包的命名空间
				else if( strpos($sNamespace,$sPackageNamespace.'\\')===0 )
				{
					$sSubNs = substr($sNamespace,strlen($sPackageNamespace)+1) ;
				}
				else 
				{
					continue ;
				}
			}
			else
			{
				$sSubNs = null ;
			}
				
			
			foreach($aPackage->classIterator($sSubNs) as $sClass)
			{
				if( !in_array($sClass,$arrClasses) )
				{
					$arrClasses[] = $sClass ;
				}
			}
		}
		
		sort($arrClasses) ;
		parent::__construct($arrClasses) ;
	}

}

?>
