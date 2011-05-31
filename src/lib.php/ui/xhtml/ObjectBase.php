<?php
namespace jc\ui\xhtml ;

use jc\util\String;
use jc\ui\ICompiler;
use jc\io\IOutputStream;
use jc\lang\Exception;
use jc\lang\Type;
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
		Type::assert(__NAMESPACE__."\\IObject",$aChild) ;

		parent::add($aChild) ;
		
		if($bAdoptRelative)
		{
			$aChild->setParent($this) ;
		}
	}

	
	/**
	 * 从相对parent的位置，转换到全局位置
	 */
	static public function globalLocate(ObjectBase $aParent,ObjectBase $aChild)
	{
		$aChild->setPosition(
			$aParent->position() + $aChild->position()
		) ;
		
		$aChild->setEndPosition(
			$aParent->position() + $aChild->endPosition()
		) ;
		
		$aChild->setLine(
			$aParent->line() + $aChild->line()
		) ;
	}
	
	static public function getLine($source,$nObjectPos,$nFindStart=0)
	{
		$nFindLen = $nObjectPos-$nFindStart+1 ;

		$sTextLen = ( $source instanceof String )? $source->length(): strlen($source) ;
		if( $sTextLen<$nFindStart+$nFindLen )
		{
			throw new Exception("计算对象所在行数时遇到了错误的参数：全文长度：%d,开始位置：%d,有效长度：%d",array($sTextLen,$nFindStart,$nFindLen)) ; 
		}
		
		if( $source instanceof String )
		{
			return $source->substrCount("\n",$nFindStart,$nFindLen) ;
		}
		else 
		{
			return substr_count($source,"\n",$nFindStart,$nFindLen) ;
		}
	}
	
	private $nPosition = -1 ;
	
	private $nEndPosition = -1 ;
	
	private $nLine ;
	
	private $sSource ;
}

?>