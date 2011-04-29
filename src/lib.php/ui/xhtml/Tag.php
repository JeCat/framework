<?php
namespace jc\ui\xhtml ;

use jc\lang\Type;

use jc\lang\Object;

class Tag extends Object
{
	const TYPE_HEAD = 1 ;
	const TYPE_TAIL = 4 ;
	const TYPE_SINGLE = 3 ;
	
	public function __construct($sName,$aAttrs,$sSource,$nLine,$nPosition,$nType)
	{
		Type::check(array("jc\\ui\\xhtml\\Attributes","null"),$aAttrs) ;
		
		$this->sName = $sName ;
		$this->sSource = $sSource ;
		$this->nLine = $nLine ;
		$this->nPosition = $nPosition ;
		$this->nType = $nType ;
		$this->aAttrs = $aAttrs ;
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
	public function line()
	{
		return $this->nLine ;
	}
	public function position()
	{
		return $this->nPosition ;
	}
	public function source()
	{
		return $this->sSource ;
	}
	public function type()
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
	private $sSource ;
	private $nLine ;
	private $nPosition ;
	private $nType ;
}

?>