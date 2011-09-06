<?php
namespace jc\lang\compile\interpreters\oop ;

use jc\pattern\iterate\INonlinearIterator;
use jc\lang\compile\object\TokenPool;

interface ISyntaxParser
{
	public function parse(TokenPool $aTokenPool,INonlinearIterator $aTokenPoolIter,State $aState) ;
}

?>