<?php
namespace org\jecat\framework\ui\xhtml\weave ;

use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\ui\xhtml\Macro;
use org\jecat\framework\ui\xhtml\Text;
use org\jecat\framework\ui\xhtml\IObject;
use org\jecat\framework\lang\Exception;

class PatchSlotPathSegment 
{
	private public function __construct()
	{}
	
	/**
	 * @return ObjectPathSegment
	 */
	static public function parseSegment($sSegment)
	{
		$aObjectPathSegment = new self() ;
		
		if( strstr($sSegment,':')===false )
		{
			if(is_numeric($sSegment))
			{
				$aObjectPathSegment->nPos = intval($sSegment) ;
				$aObjectPathSegment->sObjectType = '*' ;
			}
		}
		else
		{
			list($aObjectPathSegment->sObjectType,$sPos) = explode(':',$sSegment,2) ;
			
			if( !is_numeric($sPos) )
			{
				throw new Exception("遇到无效的路径片段:%s，其中%s部分必须是一个数字",array($sSegment,$sPos)) ;
			}
			$aObjectPathSegment->nPos = intval($sPos) ;
		}
		
		$aObjectPathSegment->sObjectType = strtolower($aObjectPathSegment->sObjectType) ;
		
		return $aObjectPathSegment ;
	}
	
	public function __toString()
	{
		return "{$aObjectPathSegment->sObjectType}:{$aObjectPathSegment->nPos}" ;
	}
	
	/**
	 * @return org\jecat\framework\ui\xhtml\ObjectBase
	 */
	public function localObject(IObject $aParentObject)
	{
		$nPos = 0 ;
		
		foreach($aParentObject->iterator() as $aBrother)
		{
			if( $this->matchType($aBrother) and $nPos++==$this->nPos )
			{
				return $aBrother ;
			}
		}
		
		return null ;
	}
	
	public function matchType(IObject $aObject)
	{
		switch ($this->sObjectType)
		{
			case '*' :
				return true ;
			case '<text>' :
				return ($aObject instanceof Text) ;
			case '<macro>' :
				return ($aObject instanceof Macro) ;
			default:
				return ($aObject instanceof Node) and $aObject->tagName()==$this->sObjectType ;
		}
	}
	
	private $sObjectType ;
	
	private $nPos ;
}

?>