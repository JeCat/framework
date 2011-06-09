<?php
namespace jc\ui\xhtml ;

use jc\ui\ICompiler;
use jc\io\IOutputStream;
use jc\lang\Type;

class Tag extends ObjectBase
{
	const TYPE_HEAD = 1 ;
	const TYPE_TAIL = 4 ;
	const TYPE_SINGLE = 3 ;
	
	public function __construct($sName,Attributes $aAttrs=null,$nType,$nPosition,$nEndPosition,$nLine,$sSource)
	{
		$this->sName = $sName ;
		$this->nType = $nType ;
		$this->aAttrs = $aAttrs? $aAttrs: new Attributes() ;
		
		parent::__construct($nPosition, $nEndPosition, $nLine, $sSource) ;
	}

	public function name()
	{
		return $this->sName ;
	}
	public function setName($sName)
	{
		$this->sName = $sName ;
	}
	
	/**
	 * @return Attributes
	 */
	public function attributes()
	{
		return $this->aAttrs ;
	}
	/**
	 * @return Attributes
	 */
	public function setAttributes($aAttrs)
	{
		$this->aAttrs = $aAttrs ;
	}
	
	public function tagType()
	{
		return $this->nType ;
	}
	public function setTagType($nType)
	{
		$this->nType = $nType ;
	}
	
	public function isHead()
	{
		return ($this->nType&self::TYPE_HEAD)==self::TYPE_HEAD ;
	}
	public function isTail()
	{
		return ($this->nType&self::TYPE_TAIL)==self::TYPE_TAIL ;
	}
	public function isSingle()
	{
		return ($this->nType&self::TYPE_SINGLE)==self::TYPE_SINGLE ;
	}
	
	public function add($aChild,$bAdoptRelative=true)
	{
		if( $aChild instanceof Mark )
		{
			$aAttrVal = new AttributeValue(null, '', $aChild->position(), $aChild->line()) ;
			$aAttrVal->setEndPosition($aChild->endPosition()) ;
			$aAttrVal->add($aChild) ;
			
			$this->attributes()->add($aAttrVal) ;
			
			$aAttrVal->setParent($this) ;
			$aChild->setParent($this) ;
		}
	}
	
	private $sName ;
	private $aAttrs ;
	private $nType ;
}

?>