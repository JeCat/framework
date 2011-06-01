<?php
namespace jc\ui\xhtml\parsers ;

use jc\ui\xhtml\Node;
use jc\ui\xhtml\Attributes;
use jc\ui\xhtml\Text;
use jc\lang\Exception;
use jc\lang\Assert;
use jc\util\match\RegExp;
use jc\ui\xhtml\ObjectBase;
use jc\ui\xhtml\IObject;
use jc\ui\xhtml\Tag;
use jc\lang\Object;
use jc\util\String;

class ParserStateTag extends ParserState 
{
	public function __construct()
	{
		parent::__construct() ;
		
		$this->aRegextFindHeadTags = new RegExp("|^<([\\w:_\\-]+)([^>]*?)(/)?>$|s") ;
		$this->aRegextFindTailTags = new RegExp("|^</([\\w:_\\-]+)>$|s") ;
		$this->aRegextParseTagAttributes = new RegExp("|([\\w_\\.\\-]+)\\s*=\\s*([\"'])([^\"']+)\\2|s") ;
		
		$this->arrChangeToStates[] = ParserStateAttribute::singleton() ;
	}

	public function active(IObject $aParent,String $aSource,$nPosition)
	{
		$aTag = new Tag('unknow', null, 0, $nPosition, 0, ObjectBase::getLine($aSource,$nPosition), '') ;
		
		// 尾标签
		if($aSource->byte($nPosition+1)=='/')
		{
			if( !($aParent instanceof Node) )
			{
				throw new Exception("错误类型") ;
			}
			
			$aParent->setTailTag($aTag) ;
		}
		
		// 头标签 或 单行标签
		else
		{
			$aNode = new Node($aTag) ;
			$aParent->add($aNode) ;
		}
		
		return $aTag ;
	}
		
	public function examineEnd(String $aSource, &$nPosition,IObject $aObject)
	{
		return $aSource->byte($nPosition)=='>' ;
	}
	public function examineStart(String $aSource, &$nPosition,IObject $aObject)
	{
		$sByte = $aSource->byte($nPosition) ;
		
		if( $sByte=='<' and preg_match('|[/\w:_\-\.\!]|',$nPosition+1) )
		{
			// 排除html注释
			if( $aSource->substr($nPosition,4)=='<!--' )
			{
				return false ;
			}
			
			return true ;
		}
	}

	public function complete(IObject $aObject,String $aSource,$nPosition)
	{
		Assert::type("jc\\ui\\xhtml\\Tag", $aObject, 'aObject') ;

		$aNode = $aObject->parent() ;
		Assert::type("jc\\ui\\xhtml\\Node", $aNode) ;
	
		$sTagLen = $nPosition - $aObject->position() + 1 ;
		$sTagSource = $aSource->substr($aObject->position(),$sTagLen) ;

		$aObject->setSource($sTagSource) ;
		$aObject->setEndPosition($nPosition) ;
		
		// 头标签/单行标签
		if($aResult=$this->aRegextFindHeadTags->match($sTagSource)->result())
		{
			$aObject->setName($aResult->result(1)) ;
			$aObject->setTagType( $aResult->result(3)=='/'?Tag::TYPE_SINGLE:Tag::TYPE_HEAD ) ;
			
			// Attributes ////////
			$sAttrsSrc = $aResult->result(2) ; 
			$nAttrsStartPos = $aResult->position(2) ;
			
			$aAttrs = $aObject->attributes() ;
			$aAttrs->setSource($sAttrsSrc) ;
			
			if(trim($sAttrsSrc))
			{
				foreach($this->aRegextParseTagAttributes->match($sAttrsSrc) as $aAttrRes)
				{
					$nAttrPos = $aAttrRes->position(3) ; 
					$nAttrEndPos = $nAttrPos + $aAttrRes->length(3) - 1 ;
					$sAttrSource = $aAttrRes->result(3) ;
					
					$nAttrGlobalPos = $aObject->position()+$nAttrsStartPos+$nAttrPos ;
					$aAttrValue = $aAttrs->object($nAttrGlobalPos) ;
					$aAttrs->remove($nAttrGlobalPos) ;
					
					if(!$aAttrValue)
					{
						throw new Exception("发现无效的属性值") ;
					}
					
					$aAttrs->set($aAttrRes->result(1),$aAttrValue) ;
				}
			}
			
			// 单行标签
			if( $aObject->isSingle() )
			{
				// 节点结束
				return $aNode->parent() ;
			}
			else 
			{
				return $aNode ;
			}
		}
		
		// 尾标签
		else if($aResult=$this->aRegextFindTailTags->match($sTagSource)->result())
		{
			$aObject->setName($aResult->result(1)) ;
			$aObject->setTagType( Tag::TYPE_TAIL ) ;
			
			if( $aNode->tagName() != $aObject->name() )
			{
				throw new Exception("遇到不匹配的XHTML节点，头标签:%s(line:%d), 尾标签:%s(line:%d)", array(
						$aNode->tagName() ,
						$aNode->position() ,
						$aObject->name() ,
						$aObject->position() ,
				)) ;
			}
			
			// 节点结束
			return $aNode->parent() ;
		}
		
		else 
		{
			throw new Exception("遇到无效的xhtml标签。源文：%s",$sTagSource) ;
		}
		
		
	}
	
	
	private $aRegextFindHeadTags ;
	
	private $aRegextFindTailTags ;
	
	private $aRegextParseTagAttributes ;
}

?>