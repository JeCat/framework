<?php
namespace jc\compile\object ;

use jc\pattern\composite\Composite;

class AbstractObject extends Composite
{
	public function __construct($sSource,$nPostion)
	{
		$this->sSource = $this->sTarget = $sSource ;
		$this->nPostion = $nPostion ;
	}
	
	public function __toString()
	{
		return $this->sourceCode() ;
	}

	public function sourceCode()
	{
		return $this->sSource ;
	}
	public function setSourceCode($sCode)
	{
		$this->sSource = $sCode ;
	}

	public function targetCode()
	{
		return $this->sTarget ;
	}
	public function setTargetCode($sCode)
	{
		$this->sTarget = $sCode ;
	}

	public function position()
	{
		return $this->nPostion ;
	}
	public function setPosition($nPostion)
	{
		$this->nPostion = $nPostion ;
	}
	public function length()
	{
		return strlen($this->nPostion) ;
	}
	public function endPosition()
	{
		return $this->position() + $this->length() - 1 ;
	}
	
	private $sSource ;
	
	private $sTarget ;
	
	private $nPostion ;
}

?>