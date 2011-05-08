<?php

namespace jc\ui\xhtml ;

use jc\lang\Type;
use jc\ui\xhtml\nodes\TagLibrary;
use jc\ui\IObject;
use jc\ui\ICompiler;
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
	
	public function compile(IOutputStream $aDev)
	{
		$this->aHeadTag->compile($aDev) ;
		
		$this->compileChildren($aDev) ;
		
		if($this->aTailTag)
		{
			$this->aTailTag->compile($aDev) ;
		}	
	}
	
	/**
	 * @return Tag
	 */
	public function headTag()
	{
		return $this->aHeadTag ;
	}
	/**
	 * @return Tag
	 */
	public function tailTag()
	{
		return $this->aTailTag ;
	}
	/**
	 * @return Attributes
	 */
	public function attributes()
	{
		return $this->headTag()->attributes() ;
	}
	
	public function childrenIterator($nType=null)
	{
		$aIter = new \MultipleIterator(\MultipleIterator::MIT_NEED_ALL) ;
		
		// 属性
		$aIter->attachIterator($this->aHeadTag->attributes()->valueIterator()) ;
		
		// children
		$aIter->attachIterator(parent::childrenIterator(nType)) ;
		
		return $aIter ;
	}
	
	private $aHeadTag ;
	
	private $aTailTag ;	
	
}

?>