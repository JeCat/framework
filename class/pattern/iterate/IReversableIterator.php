<?php
namespace org\jecat\framework\pattern\iterate ;

interface IReversableIterator extends \Iterator
{
	
	public function prev() ;
	
	public function last() ;
	
}

?>