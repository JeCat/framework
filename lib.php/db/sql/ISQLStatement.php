<?php 

namespace jc\db\sql ;

interface ISQLStatement
{
	public function MakeSQL($bFormat=false) ;
	
	public function checkValid($bThrowException=true) ;
}


?>