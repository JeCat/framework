<?php

namespace jc\ui\xhtml ;

use jc\lang\Type;
use jc\ui\ICompiler;
use jc\ui\xhtml\nodes\TagLibrary;
use jc\ui\IObject;
use jc\io\IOutputStream;
use jc\util\IDataSrc;

class Node extends ObjectBase
{	
	static public function type()
	{
		return __CLASS__ ;
	}
	
	public function __construct(Tag $aHeadTag, Tag $aTailTag=null)
	{
		$this->aHeadTag = $aHeadTag ;
		$this->aTailTag = $aTailTag ;
		
		$this->setPosition(
			$this->aHeadTag->position()
		) ;
		
		$this->setLine(
			$this->aHeadTag->line()
		) ;
		
		parent::__construct($this->position(),$this->endPosition(),$this->line(),'') ;
	}

	public function position()
	{
		return $this->aHeadTag->position() ;
	}

	public function endPosition()
	{
		return $this->aTailTag?
				$this->aTailTag->endPosition() :
				$this->aHeadTag->endPosition() ;
	}

	public function line()
	{
		return $this->aHeadTag->line() ;	
	}

	public function tagName()
	{
		return $this->aHeadTag->name() ;
	}
	
	public function compile(IOutputStream $aDev,ICompiler $aCompiler)
	{
		$this->aHeadTag->compile($aDev,$aCompiler) ;
		
		foreach ($this->childrenIterator() as $aChild)
		{
			$aChild->compile($aDev,$aCompiler) ;
		}
		
		if($this->aTailTag)
		{
			$this->aTailTag->compile($aDev,$aCompiler) ;
		}
		
		return ;		
	}
	
	
	private $aHeadTag ;
	
	private $aTailTag ;	
	
}

?>