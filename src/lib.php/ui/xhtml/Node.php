<?php

namespace jc\ui\xhtml ;

use jc\util\CombinedIterator;
use jc\lang\Type;
use jc\ui\xhtml\nodes\TagLibrary;
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
		$this->setHeadTag($aHeadTag) ;
		$this->setTailTag($aTailTag) ;
		
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
	
	
	/**
	 * @return Tag
	 */
	public function headTag()
	{
		return $this->aHeadTag ;
	}
	public function setHeadTag(Tag $aTag)
	{
		if($this->aHeadTag)
		{
			$this->aHeadTag->setParent(null) ;
		}
		
		$this->aHeadTag = $aTag ;
		$this->aHeadTag->setParent($this) ;
	}
	/**
	 * @return Tag
	 */
	public function tailTag()
	{
		return $this->aTailTag ;
	}
	public function setTailTag(Tag $aTag=null)
	{
		if($this->aTailTag)
		{
			$this->aTailTag->setParent(null) ;
		}
		
		$this->aTailTag = $aTag ;
		
		if($this->aTailTag)
		{
			$this->aTailTag->setParent($this) ;
		}
	}
	
	/**
	 * @return Attributes
	 */
	public function attributes()
	{
		return $this->headTag()->attributes() ;
	}
	
	public function iterator($nType=null)
	{
		return new CombinedIterator(
			$this->aHeadTag->attributes()->valueIterator()		// 属性
			, parent::iterator($nType)					// children
		) ;
	}
	
	public function childElementsIterator()
	{
		return parent::iterator() ;
	}
	
	private $aHeadTag ;
	
	private $aTailTag ;	
	
}

?>