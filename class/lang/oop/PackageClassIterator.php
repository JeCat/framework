<?php
namespace org\jecat\framework\lang\oop ;

use org\jecat\framework\fs\FSIterator;

class PackageClassIterator extends \ArrayIterator
{
	public function __construct(Package $aPackage)
	{
		$arrClasses = array() ;
		foreach($aPackage->folder()->iterator(FSIterator::CONTAIN_FILE|FSIterator::RECURSIVE_SEARCH) as $sSubPath)
		{
			$sSubPath = preg_replace('/(.+)\.php$/i','\\1',$sSubPath) ;
			$arrClasses[] = $aPackage->ns() . '\\' . str_replace('/','\\',$sSubPath) ;
		}
		
		parent::__construct($arrClasses) ;
	}
}
