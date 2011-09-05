<?php
namespace jc\pattern\iterate ;

use jc\lang\Object;

class ReveseIterator extends Object implements IReversableIterator, \OuterIterator
{
	public function __construct (IReversableIterator $aOriginIterator)
	{
		$this->aOriginIterator = $aOriginIterator ;
	}

	public function rewind()
	{
		$this->getInnerIterator()->last() ;
	}
	
	public function next()
	{
		$this->getInnerIterator()->prev() ;
	}

	public function prev()
	{
		$this->getInnerIterator()->next() ;
	}

	public function key()
	{
		$this->getInnerIterator()->key() ;
	}

	public function valid()
	{
		$this->getInnerIterator()->valid() ;
	}
	
	public function last()
	{
		$this->getInnerIterator()->rewind() ;
	}

	/**
	 * @return IReversableIterator
	 */
	public function getInnerIterator ()
	{
		return $this->aOriginIterator ;
	}
	
	private $aOriginIterator ;
}

?>