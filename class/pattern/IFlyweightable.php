<?php
namespace org\jecat\framework\pattern ;

interface IFlyweightable
{
	static public function flyweight($keys,$bCreateNew=true,$sClassName=null) ;
}

?>