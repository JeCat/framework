<?php
namespace jc\ui\xhtml ;

use jc\util\String;
use jc\ui\ICompiler;
use jc\io\IOutputStream;
use jc\lang\Exception;
use jc\lang\Assert;
use jc\pattern\composite\IContainedable;
use jc\ui\Object ;

class ObjectBase extends Object implements IObject
{
	const LOCATE_IN = 1 ;
	const LOCATE_OUT = 2 ;
	const LOCATE_FRONT = 3 ;
	const LOCATE_BEHIND = 4 ;

	public function __construct($nPosition,$nEndPosition,$nLine,$sSource)
	{
		$this->setPosition($nPosition) ;
		$this->setEndPosition($nEndPosition) ;
		$this->setLine($nLine) ;
		$this->setSource($sSource) ;
		
		parent::__construct() ;
	}
	
	public function position() 
	{
		return $this->nPosition ;
	}
	public function setPosition($nPosition)
	{
		$this->nPosition = $nPosition ;
	}
	
	public function endPosition()
	{
		return $this->nEndPosition ;
	}
	public function setEndPosition($nEndPosition)
	{
		$this->nEndPosition = $nEndPosition ;
	}
	
	public function line()
	{
		return $this->nLine ;
	}
	public function setLine($nLine) 
	{
		$this->nLine = $nLine ;
	}

	public function source()
	{
		return $this->sSource ;
	}
	public function setSource($sSource)
	{
		$this->sSource = $sSource ;
	}
	

	public function add($aChild,$bAdoptRelative=true)
	{
		Assert::type(__NAMESPACE__.'\\IObject', $aChild) ;
		
		parent::add($aChild) ;
		
		if($bAdoptRelative)
		{
			$aChild->setParent($this) ;
		}
	}
	
	static public function getLine(String $aSource,$nObjectPos,$nFindStart=0)
	{
		$nFindLen = $nObjectPos-$nFindStart+1 ;
		
		if( $aSource->length() < $nFindStart+$nFindLen )
		{
			throw new Exception(__METHOD__."() 超过字符范围(源数据长度:%d, 对象位置: %d,find from:%d,find %d)",array(
					$aSource->length()
					, $nObjectPos
					, $nFindStart
					, $nFindLen
			)) ;
		}
		
		return $aSource->substrCount("\n",$nFindStart,$nFindLen) + 1;
	}
	
	private $nPosition = -1 ;
	
	private $nEndPosition = -1 ;
	
	private $nLine ;
	
	private $sSource ;
}

?>