<?php
namespace org\jecat\framework\locale ;

class PackageIterator extends \FilterIterator
{
	public function __construct($sFolderPath,$sLanguage=null,$sCountry=null,$sLibName=null)
	{
		$this->sLanguage = $sLanguage ;
		$this->sCountry = $sCountry ;
		$this->sLibName = $sLibName ;

		parent::__construct(new \DirectoryIterator($sFolderPath)) ;
	}

	public function accept()
	{
		if( !$this->getInnerIterator()->isFile() )
		{
			return false ;
		}
		
		if( !preg_match('/^([a-z]{2})_([A-Z]{2})\\.(\w+)\\.spkg$/i',$this->getInnerIterator()->getFilename(),$arrMatch) )
		{
			return false ;
		}

		if( $this->sLanguage!==null and $arrMatch[1]!=$this->sLanguage )
		{
			return false ;
		}
		if( $this->sCountry!==null and $arrMatch[2]!=$this->sCountry )
		{
			return false ;
		}
		if( $this->sLibName!==null and $arrMatch[3]!=$this->sLibName )
		{
			return false ;
		}
		
		return true ;
	}
	
	public function current()
	{
		return $this->getInnerIterator()->getPath() . '/' . parent::current() ;
	}
	
	private $sLanguage ;
	private $sCountry ;
	private $sLibName ;
}