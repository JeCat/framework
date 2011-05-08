<?php
namespace jc\ui\xhtml\parsers ;

use jc\ui\IObject;
use jc\ui\xhtml\Attributes;
use jc\ui\xhtml\Tag;
use jc\ui\IInterpreter;
use jc\lang\Object;
use jc\util\String;
use jc\util\match\RegExp;

class TagParser extends Object implements IInterpreter
{
	public function __construct()
	{
		$this->aRegextFindHeadTags = new RegExp("|<([\\w:_\\-]+)([^>]*?)(/)?>|s") ;
		$this->aRegextFindTailTags = new RegExp("|</([\\w:_\\-]+)>|s") ;
		$this->aRegextParseTagAttributes = new RegExp("|([\\w_\\.\\-]+)\\s*=\\s*([\"'])([^\"']+)\\2|s") ;
	}
	
	public function parse(String $aSource,IObject $aObjectContainer,$sSourcePath)
	{
		// head(single) tags
		foreach($this->aRegextFindHeadTags->match($aSource) as $aRes)
		{
			$aAttrs = new Attributes() ;
			
			$sAttrsSrc = Preprocessor::singleton()->quoteDecode($aRes->result(2)) ;
			$aAttrs->setSource($sAttrsSrc) ;
			
			$sAttributes = trim($sAttrsSrc) ;
			if($sAttributes)
			{
				foreach($this->aRegextParseTagAttributes->match($sAttributes) as $aAttrRes)
				{
					$aAttrs->set(
						$aAttrRes->result(1)
						, Preprocessor::singleton()->quoteDecode($aAttrRes->result(3))
					) ;
				}
			}

			$aObjectContainer->addChild( new Tag(
				$aRes->result(1)
				, $aAttrs 
				, ($aRes->result(3)=='/')? Tag::TYPE_SINGLE: Tag::TYPE_HEAD
				, $aRes->position()
				, $aRes->position() + $aRes->length() - 1
				, substr_count($aSource,"\n",0,$aRes->position()+1)
				, Preprocessor::singleton()->quoteDecode($aRes->result())
			) ) ;
		}
		
		// tail tags
		foreach($this->aRegextFindTailTags->match($aSource) as $aRes)
		{
			$aObjectContainer->addChild( new Tag(
				$aRes->result(1)
				, new Attributes() 
				, Tag::TYPE_TAIL
				, $aRes->position()
				, $aRes->position() + $aRes->length() - 1
				, substr_count($aSource,"\n",0,$aRes->position()+1)
				, Preprocessor::singleton()->quoteDecode($aRes->result())
			) ) ;
		}
	}
	
	private $aRegextFindHeadTags ;
	
	private $aRegextFindTailTags ;
	
	private $aRegextParseTagAttributes ;
}

?>