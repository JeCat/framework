<?php
namespace jc\pattern\iterate ;

use jc\lang\Object;

class ReveseIterator extends \IteratorIterator implements IReversableIterator, \OuterIterator
{
	public function __construct (IReversableIterator $aOriginIterator)
	{
		parent::__construct($aOriginIterator) ;
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
	
	public function last()
	{
		$this->getInnerIterator()->rewind() ;
	}
}

?>