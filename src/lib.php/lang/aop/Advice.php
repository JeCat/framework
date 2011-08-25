<?php
namespace jc\aop ;

use jc\pattern\composite\NamedObject;

class Advice extends NamedObject
{
	const around = 'around' ;
	const before = 'before' ;
	const after = 'after' ;
	
	static private $arrTypes = array(
		self::around, self::before, self::after
	) ;
	
	public function __construct($fnSource,$sPosition=self::after)
	{
		$this->fnSource = $fnSource ;
		$this->sPosition = $sPosition ;
	}
	
	private $fnSource ;
	
	private $sPosition ;
}

?>