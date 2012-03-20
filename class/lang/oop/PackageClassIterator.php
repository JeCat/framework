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
				if( !preg_match('/(.+)\.php$/i',$sSubPath,$arrRes) )
				{
					continue ;
				}
				$sSubPath = $arrRes[1] ;
				
				if($sSubNs)
				{
					$sClass = $aPackage->ns() . '\\' . $sSubNs . '\\' . str_replace('/','\\',$sSubPath) ;
				}
				else
				{
					$sClass = $aPackage->ns() . '\\' . str_replace('/','\\',$sSubPath) ;
				}
				
				
				$arrClasses[] = $sClass ;
			}
		}
			
		parent::__construct($arrClasses) ;
	}
}
