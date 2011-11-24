<?php
namespace org\jecat\framework\mvc\view ;

use org\jecat\framework\ui\UI;

class Webpage extends View
{
	public function __construct($sName=null,UI $aUI=null)
	{
		if($sName)
		{
			$sName = 'theWebpage' ;
		}
		
		parent::__construct($sName,'org.jecat.framework:Webpage.template.html',$aUI) ;
	}

	public function title()
	{}
	
	public function setTitle($sTitle)
	{}
	
	public function keywords()
	{}
	
	public function setKeywords($sKeywords)
	{}
	
	public function description()
	{}
	
	public function setDescription($sDescription)
	{}
	
	public function contents()
	{
		return strval($this->sContents) ;
	}
	
	public function setContents($contents)
	{
		if( $contents instanceof IView )
		{
			$this->add($contents) ;
		}
		else
		{
			$this->sContents = $contents ;
		}
	}
	
	private $sContents = null ;
}
?>