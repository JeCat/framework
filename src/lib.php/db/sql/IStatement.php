<?php 

namespace jc\db\sql ;

interface IStatement
{
	public function makeStatement($bFormat=false) ;
	
	public function checkValid($bThrowException=true) ;
}


?>