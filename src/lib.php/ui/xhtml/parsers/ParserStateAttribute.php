<?php
namespace jc\ui\xhtml\parsers ;

use jc\ui\xhtml\AttributeValue;
use jc\ui\xhtml\IObject;
use jc\util\String;
use jc\lang\Type;
use jc\ui\xhtml\ObjectBase;
use jc\ui\xhtml\Tag;

class ParserStateAttribute extends ParserState
{
	public function __construct()
	{
		parent::__construct() ;
	}
	
	public function active(IObject $aParent,String $aSource,$nPosition)
	{
		Type::assert("jc\\ui\\xhtml\\Tag", $aParent, 'aParent') ;
		
		$aAttriVal = new AttributeValue( $aSource->substr($nPosition,1), $nPosition+1, ObjectBase::getLine($aSource, $nPosition+1) ) ;
		$aParent->attributes()->set($nPosition+1,$aAttriVal) ;
		$aAttriVal->setParent($aParent) ;
		
		return $aAttriVal ;
	}

	public function complete(IObject $aObject,String $aSource,$nPosition)
	{
		Type::assert("jc\\ui\\xhtml\\AttributeValue", $aObject, 'aObject') ;
		
		$sAttrTextPos = $aObject->position()+1 ;
		$sAttrTextLen = ($nPosition-1) - $sAttrTextPos + 1 ;
		$sAttrText = $aSource->substr( $sAttrTextPos, $sAttrTextLen ) ;
		
		$aObject->setPosition($sAttrTextPos) ;
		$aObject->setEndPosition($nPosition-1) ;
		$aObject->setSource($sAttrText) ;
		
		return $aObject->parent() ;
	}

	public function examineEnd(String $aSource, &$nPosition,IObject $aObject)
	{
		Type::assert("jc\\ui\\xhtml\\AttributeValue", $aObject, 'aObject') ;
		
		$sByte = $aSource->byte($nPosition) ;
		
		if($aObject->quoteType()==$sByte)
		{
			return true ;
		}
		else 
		{
			if($sByte=='\\')
			{
				$nPosition ++ ;
			}
			
			return false ;
		}
	}
	public function examineStart(String $aSource, &$nPosition,IObject $aCurrentObject)
	{
		$sByte = $aSource->byte($nPosition) ;
		return $sByte=='"' or $sByte=="'" ;
	}
	
}

?>