<?php
namespace org\jecat\framework\ui\xhtml\parsers ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\ui\xhtml\ObjectBase;
use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\ui\xhtml\Macro;
use org\jecat\framework\ui\xhtml\AttributeValue;
use org\jecat\framework\ui\xhtml\Tag;
use org\jecat\framework\ui\xhtml\Text;
use org\jecat\framework\ui\xhtml\IObject;
use org\jecat\framework\lang\Object as JcObject;
use org\jecat\framework\util\String;

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
		
		else if( $aObject instanceof Macro )
		{
			return ParserStateMacro::singleton() ;
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