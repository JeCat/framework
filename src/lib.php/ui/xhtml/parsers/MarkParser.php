<?php
namespace jc\ui\xhtml\parsers ;

use jc\ui\xhtml\ObjectBase;

use jc\util\match\RegExp;
use jc\ui\xhtml\Mark;
use jc\ui\xhtml\Text;
use jc\ui\xhtml\Node;
use jc\ui\IObject;
use jc\ui\IInterpreter;
use jc\lang\Object;
use jc\util\String;

class MarkParser extends Object implements IInterpreter
{
	public function __construct()
	{
		$this->aRegexpFoundExpression = new RegExp("/\\{([\\*=\\?])(.+)\\}/s") ;
	}
	
	public function parse(String $aSource,IObject $aObjectContainer,$sSourcePath)
	{
		foreach($aObjectContainer->iterator() as $aChild)
		{
			if( ($aChild instanceof Text) and !$aChild->count() )
			{
				$this->parseMark($aChild) ;
			}
			
			else
			{
				$this->parse($aSource,$aChild,$sSourcePath) ;
			}
		}
	}
	
	protected function parseMark(Text $aText)
	{
		foreach($this->aRegexpFoundExpression->match($aText->source()) as $aRes)
		{
			$aMark = new Mark(
					$aRes->result(1)
					, $aRes->position()
					, $aRes->position()+$aRes->length()-1
					, ObjectBase::getLine( $aText->source(), $aRes->position() )
					, $aRes->result(2)
			) ;
			
			ObjectBase::globalLocate($aText,$aMark) ;
			
			$aText->add($aMark) ;
		}
	}
	  
	private $aRegexpFoundExpression ;
}

?>