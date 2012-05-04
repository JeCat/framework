<?php
namespace org\jecat\framework\locale ;

use org\jecat\framework\lang\Object ;

class LanguagePackageFolders extends Object
{
	public function registerFolder($sFolderPath)
	{
		$this->arrFolders[] = $sFolderPath ;
	}
	public function clearFolders()
	{
		$this->arrFolders = null ;
	}
	
	public function packageIterator($sLanguage=null,$sCountry=null,$sLibName=null)
	{
		if( empty($this->arrFolders) )
		{
			return new \EmptyIterator() ;
		}
		else
		{
			$aIterator = new \AppendIterator() ;
			foreach ($this->arrFolders as $sFolderPath)
			{
				$aIterator->append( new PackageIterator($sFolderPath,$sLanguage,$sCountry,$sLibName) ) ;
			}
			return $aIterator ;
		}
	}
	
		
	private $arrFolders ;

}
