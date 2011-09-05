<?php
namespace jc\pattern\iterate ;

interface IReversableIterator extends \Iterator
{
	
	public function prev() ;
	
	public function last() ;
	
}

?>