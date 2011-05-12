<?php
namespace jc\ui\xhtml\parsers ;

use jc\util\match\RegExp;
use jc\ui\xhtml\Mark;
use jc\ui\xhtml\Text;
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
		foreach($aObjectContainer->childrenIterator() as $aChild)
		{
			if( ($aChild instanceof Text) and !$aChild->childrenCount() )
			{
				$this->parseMark($aChild) ;
			}
			
			else
			{
				$this->parse($aSource,$aChild,$sSourcePath) ;
			}
		}
	}
	
	protected function parseMark(Text $aChild)
	{
		foreach($this->aRegexpFoundExpression->match($aChild->source()) as $aRes)
		{
			$nPosition = $aChild->position()+$aRes->position() ;
			$nEndPosition = $nPosition + $aRes->length() - 1 ;
			$nLine = $aChild->line() + substr_count($aChild->source(),"\n",1,$aRes->position()) ;
			
			$aChild->addChild(
				new Mark($aRes->result(1), $nPosition, $nEndPosition, $nLine, $aRes->result())
			) ;
		}
	}
	  
	private $aRegexpFoundExpression ;
}

?>