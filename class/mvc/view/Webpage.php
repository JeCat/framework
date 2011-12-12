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
	{
		return $this->sTitle ;
	}
	
	public function setTitle($sTitle)
	{
		$this->sTitle = $sTitle ;
	}
	
	public function keywords()
	{
		return $this->sKeywords ;
	}
	
	public function setKeywords($sKeywords)
	{
		$this->sKeywords = $sKeywords ;
	}
	
	public function description()
	{
		return $this->sDescription ;
	}
	
	public function setDescription($sDescription)
	{
		$this->sDescription = $sDescription ;
	}
	
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
	
	protected function isRenderHtmlWrapper()
	{
		return false ;
	}
	
	private $sContents = null ;
	
	private $sTitle = null ;
	
	private $sDescription = null ;
	
	private $sKeywords = null ;
}
?>