<?php
namespace org\jecat\framework\pattern\iterate ;

interface INonlinearIterator extends \SeekableIterator, IReversableIterator
{

	public function search ($element) ;
	
	public function searchKey ($key) ;

}

?>