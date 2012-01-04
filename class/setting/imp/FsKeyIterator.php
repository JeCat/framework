<?php
namespace org\jecat\framework\setting\imp ;

use org\jecat\framework\fs\FileSystem;
use org\jecat\framework\fs\FSIterator;

class FsKeyIterator extends \IteratorIterator
{
	public function __construct(FsKey $aKey)
	{
		parent::__construct(
				$aKey->folder()->iterator(FSIterator::FOLDER|FSIterator::RETURN_FSO)
		) ;
		
		$this->valid() ;
	}
	
	public function current()
	{		
		return $this->aCurrentKey ;
	}
	
	public function valid()
	{
		if( !parent::valid() )
		{
			return false ;
		}
		
		if( $aKey = FsKey::createKey(parent::current()) )
		{
			$this->aCurrentKey = $aKey ;
			return true ;
		}
		else
		{
			return false ;
		}
	}
	
	private $aCurrentKey ;
}

?>
