<?php
namespace jc\pattern\iterate ;

interface INonlinearIterator extends \SeekableIterator, IReversableIterator, \Iterator
{

	public function search ($element) ;
	
	public function searchKey ($key) ;

}

?>