<?php
namespace jc\mvc\view ;

use jc\ui\UI;

class Webpage extends View
{
	public function __construct($sName=null,UI $aUI=null)
	{
		if($sName)
		{
			$sName = 'theWebpage' ;
		}
		
		parent::__construct($sName,'Webpage.template.html',$aUI) ;
	}

	public function title()
	{}
	
	public function setTitle()
	{}
	
	
}

?>