<?php
namespace jc\pattern\iterate ;

use jc\lang\Object;

class ReverseIterator extends Object implements IReversableIterator, \OuterIterator
{
	public function __construct (IReversableIterator $aOriginIterator)
	{
		$this->aOriginIterator = $aOriginIterator ;
	}

	public function rewind()
	{
		$this->aOriginIterator->last() ;
	}
	
	public function next()
	{
		$this->aOriginIterator->prev() ;
	}

	public function prev()
	{
		$this->aOriginIterator->next() ;
	}

	public function key()
	{
		$this->aOriginIterator->key() ;
	}

	public function valid()
	{
		return $this->aOriginIterator->valid() ;
	}
	
	public function last()
	{
		$this->aOriginIterator->rewind() ;
	}
	
	public function current()
	{
		return $this->aOriginIterator->current();
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