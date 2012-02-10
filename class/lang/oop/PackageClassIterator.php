<?php
namespace org\jecat\framework\lang\oop ;

use org\jecat\framework\fs\FSIterator;

class PackageClassIterator extends \ArrayIterator
{
	public function __construct(Package $aPackage,$sSubNs=null)
	{
		if($sSubNs)
		{
			$aFolder = $aPackage->folder()->findFolder( str_replace('\\','/',$sSubNs) ) ;
		}
		else
		{
			$aFolder = $aPackage->folder() ;
		}
		
		$arrClasses = array() ;
		if($aFolder)
		{
			foreach($aFolder->iterator(FSIterator::CONTAIN_FILE|FSIterator::RECURSIVE_SEARCH) as $sSubPath)
			{
				$sSubPath = preg_replace('/(.+)\.php$/i','\\1',$sSubPath) ;
				$arrClasses[] = $aPackage->ns() . '\\' . str_replace('/','\\',$sSubPath) ;
			}
		}
			
		parent::__construct($arrClasses) ;
	}
}
