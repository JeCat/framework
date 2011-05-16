<?php
namespace jc\ui\xhtml ;

use jc\ui\ICompiler;
use jc\io\IOutputStream;
use jc\lang\Exception;
use jc\lang\Type;
use jc\pattern\composite\IContainedable;
use jc\ui\Object ;

class ObjectBase extends Object
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
	
	/**
	 * 比较UI对象的位置
	 *
	 * @access	public
	 * @param	$aUIObject	JCAT_UIObject	用于比较的另外一个对象
	 * @return	self::LOCATE_IN, self::LOCATE_OUT, self::LOCATE_FRONT, self::LOCATE_BEHIND
	 */
	public function locate(ObjectBase $aUIObject)
	{
		// 前
		if( $aUIObject->endPosition() <= $this->position() )
		{
			return self::LOCATE_FRONT ;
		}
			
		// 后
		if( $aUIObject->position() >= $this->endPosition() )
		{
			return self::LOCATE_BEHIND ;
		}
		
		
		// 内
		if( $aUIObject->position() >= $this->position() )
		{
			return self::LOCATE_IN ;
		}
			
		// 外
		if( $aUIObject->position() <= $this->position() )
		{
			return self::LOCATE_OUT ;
		}
		
		// 不支持
		throw new Exception('不支持交叉UI对象。') ;
	}

	public function addChild(IContainedable $aChild,$bAdoptRelative=true)
	{
		Type::check(__NAMESPACE__."\\ObjectBase",$aChild) ;
		
		$arrNewList = array() ;
		$aUIObject = $aChild ;

		foreach(parent::iterator() as $aMyUIObject)
		{
			if($aUIObject)
			{				
				switch( $aMyUIObject->locate($aUIObject) )
				{
					// 对象在目标对象前
					case self::LOCATE_FRONT :
						$arrNewList[] = $aUIObject ;				// 插入到当前位置
						$arrNewList[] = $aMyUIObject ;
						$aUIObject = null ;
						break ;
					
					case self::LOCATE_BEHIND :
						$arrNewList[] = $aMyUIObject ;
						break ;
						
					case self::LOCATE_IN :
						$aMyUIObject->addChild($aUIObject) ;
						$arrNewList[] = $aMyUIObject ;
						$aUIObject = null ;
						break ;
						
					case self::LOCATE_OUT :

						$aUIObject->addChild($aMyUIObject) ;
						break ;
				}
			}
			else
			{
				$arrNewList[] = $aMyUIObject ;
			}
		}
		
		// 加入到最后
		if( $aUIObject )
		{
			$arrNewList[] = $aUIObject ;
		}

		// 设置新的清单
		$this->clear() ;
		foreach($arrNewList as $aObject)
		{
			parent::add($aObject) ;
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
	
	static public function getLine($sSource,$nObjectPos,$nFindStart=0)
	{
		return substr_count($sSource,"\n",$nFindStart,($nObjectPos+1)-$nFindStart+1) ;
	}
	
	private $nPosition = -1 ;
	
	private $nEndPosition = -1 ;
	
	private $nLine ;
	
	private $sSource ;
}

?>