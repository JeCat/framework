<?php
namespace jc\ui\xhtml\parsers ;

use jc\lang\Exception;
use jc\ui\xhtml\ObjectBase;
use jc\ui\xhtml\Node;
use jc\ui\xhtml\Mark;
use jc\ui\xhtml\AttributeValue;
use jc\ui\xhtml\Tag;
use jc\ui\xhtml\Text;
use jc\ui\xhtml\IObject;
use jc\lang\Object as JcObject;
use jc\util\String;

abstract class ParserState extends JcObject
{
	public function active(IObject $aParent,String $aSource,$nPosition)
	{
		return $aParent ;
	}
	
	public function sleep(IObject $aObject,String $aSource,$nPosition)
	{
		return $aObject ;
	}
	
	public function wakeup(IObject $aParent,String $aSource,$nPosition)
	{
		return $aParent ;
	}

	public function complete(IObject $aObject,String $aSource,$nPosition)
	{
		return $aObject ;
	}
	
	abstract public function examineEnd(String $aSource, &$nPosition,IObject $aObject) ;
	
	abstract public function examineStart(String $aSource, &$nPosition,IObject $aObject) ;
	
	public function examineStateChange(String $aSource, &$nPosition, IObject $aCurrentObject)
	{
		if( $this->examineEnd($aSource, $nPosition, $aCurrentObject) )
		{
			return null ;
		}
		
		foreach ($this->arrChangeToStates as $aState)
		{
			if( $aState->examineStart($aSource,$nPosition,$aCurrentObject) )
			{
				return $aState ;
			}
		}
		
		return $this ;
	}
	
	/**
	 * @return ParserState
	 */
	static public function queryState(IObject $aObject)
	{		
		if( $aObject instanceof Tag )
		{
			return ParserStateTag::singleton() ;	
		}
		
		else if( $aObject instanceof AttributeValue )
		{
			return ParserStateAttribute::singleton() ;
		}
		
		else if( $aObject instanceof Node )
		{
			return ParserStateNode::singleton() ;
		}
		
		else if( $aObject instanceof Mark )
		{
			return ParserStateMark::singleton() ;
		}
	
		else if( $aObject instanceof Text )
		{
			return ParserStateText::singleton() ;
		}
		
		else if( $aObject instanceof ObjectBase )
		{
			return ParserStateDefault::singleton() ;
		}
		
		else
		{
			throw new Exception("!?") ;
		}
	} 
	
	protected $arrChangeToStates = array() ;
}

?>