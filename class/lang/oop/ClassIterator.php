<?php
namespace org\jecat\framework\lang\oop ;

use org\jecat\framework\fs\FSIterator;

class ClassIterator extends \ArrayIterator
{
	public function __construct(ClassLoader $aClassLoader)
	{
		$arrClasses = array() ;
		foreach($aClassLoader->packageIterator() as $aPackage)
		{
			foreach($aPackage->folder()->iterator(FSIterator::CONTAIN_FILE|FSIterator::RECURSIVE_SEARCH) as $sSubPath)
			{
				$sSubPath = preg_replace('/(.+)\.php$/i','\\1',$sSubPath) ;
				$sClass = $aPackage->ns() . '\\' . str_replace('/','\\',$sSubPath) ;
				
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