<?php
namespace jc\aop ;

class Advice
{
	const around = 'around' ;
	const before = 'before' ;
	const after = 'after' ;
	
	static private $arrTypes = array(
		self::around, self::before, self::after
	) ;
	
	public function __construct()
	{
		
	}
}

?>