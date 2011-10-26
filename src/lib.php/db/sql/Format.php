<?php
namespace jc\db\sql ;


class Format
{
	public function indent($sIndentChar="\t")
	{
		return str_repeat($sIndentChar,$this->nIndentLevel) ;
	}
	public function indentLevel()
	{
		return $this->nIndentLevel ;
	}
	public function setIndentLevel($nIndentLevel)
	{
		$this->nIndentLevel = nIndentLevel ;
	}
	public function indentForward()
	{
		$this->nIndentLevel ++ ;
	}
	public function indentBack()
	{
		if($this->nIndentLevel>1)
		{
			$this->nIndentLevel -- ;
		}
	}
	public function newline()
	{
		return "\r\n".str_repeat($sIndentChar,$this->nIndentLevel) ;
	}
	
	private $nIndentLevel = 0 ;
}


?>