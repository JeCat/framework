<?php
namespace jc\compile\interpreters\oop ;

use jc\pattern\iterate\INonlinearIterator;
use jc\compile\object\TokenPool;

interface ISyntaxPaser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState) ;
}

?>