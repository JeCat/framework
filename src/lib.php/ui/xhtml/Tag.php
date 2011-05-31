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
	
	public function __construct($sName,Attributes $aAttrs,$nType,$nPosition,$nEndPosition,$nLine,$sSource)
	{
		$this->sName = $sName ;
		$this->nType = $nType ;
		$this->aAttrs = $aAttrs ;
		
		parent::__construct($nPosition, $nEndPosition, $nLine, $sSource) ;
	}

	public function name()
	{
		return $this->sName ;
	}
	/**
	 * @return Attributes
	 */
	public function attributes()
	{
		return $this->aAttrs ;
	}
	public function tagType()
	{
		return $this->nType ;
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
		
	private $sName ;
	private $aAttrs ;
	private $nType ;
}

?>