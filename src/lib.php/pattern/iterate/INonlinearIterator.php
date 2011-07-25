<?php
namespace jc\pattern\iterate ;

interface INonlinearIterator extends \SeekableIterator, IReversableIterator
{

	public function search ($element) ;
	
	public function searchKey ($key) ;

}

?>