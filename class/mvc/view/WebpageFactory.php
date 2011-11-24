<?php
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\ui\Object;

class WebpageFactory extends Object
{
	public function create()
	{
		$aWebpage = new Webpage() ;
		
		// todo ... ...
		
		return $aWebpage ;
	}
}

?>