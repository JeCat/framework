<?php
namespace jc\ui\xhtml\parsers\node ;

use jc\lang\Exception;

use jc\lang\Type;

use jc\util\match\RegExp;

use jc\ui\xhtml\ObjectBase;

use jc\ui\IObject;
use jc\ui\xhtml\Tag;
use jc\lang\Object;
use jc\util\String;

class TagState extends Object implements IParserState
{
	public function __construct()
	{
		$this->aRegextFindHeadTags = new RegExp("|^<([\\w:_\\-]+)([^>]*?)(/)?>$|s") ;
		$this->aRegextFindTailTags = new RegExp("|^</([\\w:_\\-]+)>$|s") ;
		$this->aRegextParseTagAttributes = new RegExp("|([\\w_\\.\\-]+)\\s*=\\s*([\"'])([^\"']+)\\2|s") ;
	}
	
	public function wakeup(Parser $aParser,String $aSource,$nProcIndex)
	{
		if(!$this->aUncompletedObject)
		{
			$this->aUncompletedObject = new Tag('unknow', null, Tag::TYPE_HEAD, $nProcIndex, 0, ObjectBase::getLine($aSource, $nProcIndex), '') ;
		}
		
		$aParser->setCurrentObject( $this->aUncompletedObject ) ;
	}
	
	public function process(String $aSource,$nProcIndex,Parser $aParser,IObject $aObjectContainer)
	{
		$sByte = $aSource->substr($nProcIndex,1) ;
		
		// 节点结束边界
		if( $sByte=='>' )
		{
			$this->aUncompletedObject = null ;
			
			// 切换状态
			$aParser->switchState(
				TextState::singleton()
				, $aSource, $nProcIndex
			) ;
		}
		
		// 属性开始边界
		else if( $sByte=='"' or $sByte=="'" )
		{
			// 切换状态
			$aParser->switchState(
				AttributeState::singleton()
				, $aSource, $nProcIndex
			) ;
		}
		
		return ++$nProcIndex ;
	}
	
	public function sleep(String $aSource,$nEndPosition,ObjectBase $aObject,IObject $aObjectContainer)
	{
		if($this->aUncompletedObject)
		{
			return ;
		}
		
		Type::check('jc\\ui\\xhtml\\Tag', $aObject) ;
		
		$sTagLen = $nEndPosition - $aObject->position() + 1 ;
		$sTagSource = $aSource->substr($aObject->position(),$sTagLen) ;

		$aObject->setSource($sTagSource) ;
		$aObject->setEndPosition($nEndPosition) ;
		
		// 头标签/单行标签
		if($aResult=$this->aRegextFindHeadTags->match($sTagSource)->result())
		{
			$aObject->setName($aResult->result(1)) ;			
			$aObject->setTagType( $aResult->result(3)=='/'?Tag::TYPE_SINGLE:Tag::TYPE_HEAD ) ;
			
			$sAttrs = $aResult->result(2) ; 
		}
		
		// 尾标签
		else if($aResult=$this->aRegextFindTailTags->match($sTagSource)->result())
		{
			$aObject->setName($aResult->result(1)) ;			
			$aObject->setTagType( Tag::TYPE_TAIL ) ;
		}
		
		else 
		{
			throw new Exception("遇到无效的xhtml标签。源文：%s",$sTagSource) ;
		}
		
		// 
		$aObjectContainer->add($aObject) ;
		
		$this->aUncompletedObject = null ;
	}
	
	private $aUncompletedObject ;
	
	private $aRegextFindHeadTags ;
	
	private $aRegextFindTailTags ;
	
	private $aRegextParseTagAttributes ;
}

?>