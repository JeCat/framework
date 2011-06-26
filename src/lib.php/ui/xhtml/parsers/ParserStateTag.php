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
		self::setSingleton($this) ;
		
		$this->arrChangeToStates[] = ParserStateMacro::singleton() ;
		$this->arrChangeToStates[] = ParserStateAttribute::singleton() ;
	}

	public function active(IObject $aParent,String $aSource,&$nPosition)
	{
		$nLine = ObjectBase::getLine($aSource,$nPosition) ;
		$nStartPos = $nPosition ;
		
		// 标签名称
		$sTagName = '' ;
		$nSourceLen = $aSource->length() ; 
		while(1)
		{
			$nPosition ++ ;
			
			if( $nPosition>=$nSourceLen )
			{
				break ;
			}
			$sByte = $aSource->byte($nPosition) ;
			
			if( ($sTagName and $sByte=='/') or preg_match('|[\'"\\s>]|',$sByte) )
			{
				break ;
			}
			
			$sTagName.= $sByte ;
		}
		
		$nPosition-- ;
		
		if(!$sTagName)
		{
			throw new Exception("UI引擎在分析模板时遇到无效的xhtml节点：缺少节点名称(位置：%d行)",$nLine) ;
		}
		
		$aTag = new Tag($sTagName, null, 0, $nStartPos, 0, $nLine, '') ;
		
		// 尾标签
		if($sTagName[0]=='/')
		{
			if( !($aParent instanceof Node) )
			{
				throw new Exception("错误类型") ;
			}
			
			$aTag->setTagType(Tag::TYPE_TAIL) ;
			$aTag->setName(substr($sTagName,1)) ;
			$aParent->setTailTag($aTag) ;
		}
		
		// 头标签 或 单行标签
		else
		{
			$aTag->setTagType(Tag::TYPE_HEAD) ;
			
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
		$sNextByte = $aSource->byte($nPosition+1) ;
		
		if( $sByte=='<' and preg_match('|[/\w:_\-\.]|',$sNextByte) )
		{			
			return true ;
		}
		
		return false ;
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
		
		// 单行标签
		if( $sTagSource[ strlen($sTagSource)-2 ] == '/' )
		{
			if( $aObject->isTail() )
			{
				throw new Exception("UI引擎在分析模板时无法确定标签类型，不能同时是尾标签和单行标签。位置：%d行",$aObject->line()) ;
			}
			
			$aObject->setTagType(Tag::TYPE_SINGLE) ;
		}
		
		// 头标签/单行标签
		if($aObject->isHead())
		{
			// 处理属性
			$aAttrs = $aObject->attributes() ;
			$aAttrValIterator = $aAttrs->valueIterator() ;
			
			$arrAttrs = array() ;
			$arrRemoveVal = array() ;
			$aPrevAttrVal = null ;
			for($aAttrValIterator->rewind();$aVal=$aAttrValIterator->current();$aAttrValIterator->next())
			{
				if( $aVal->source() == '=' )
				{
					// 属性名
					if( !$aPrevAttrVal )
					{
						throw new Exception("UI引擎在分析模板时遇到空属性名称。位置：%d行",$aVal->line()) ;
					}
					$aAttrName = $aPrevAttrVal ;
					if( $aAttrName->quoteType() )
					{
						throw new Exception("UI引擎在分析模板时遇到无效的节点属性名称，属性名称不能使用引号。位置：%d行",$aAttrName->line()) ;
					}
					$sAttrName = $aAttrName->source() ;	
					
					// 属性值
					$aAttrValIterator->next() ;
					$aAttrVal = $aAttrValIterator->current() ;
					if(!$aAttrVal)
					{
						continue ;
						throw new Exception("UI引擎在分析模板时遇到错误：属性名:%s没有对应的属性值。位置：%d行",array($sAttrName,$aVal->line())) ;
					}
					
					$arrRemoveVal[] = $aVal ;
					$arrRemoveVal[] = $aAttrName ;
					$arrRemoveVal[] = $aAttrVal ;
					
					$aAttrVal->setName($sAttrName) ;
					$arrAttrs[] = $aAttrVal ;
				}
				
				$aPrevAttrVal = $aAttrValIterator->current() ;
			}
			
			foreach($arrRemoveVal as $aAttrVal)
			{
				$aAttrs->remove($aAttrVal) ;
			}
			foreach($arrAttrs as $aAttrVal)
			{
				$aAttrs->add($aAttrVal) ;
			}
			
			
			if( $aObject->isSingle() )
			{
				// 单行标签节点结束
				return $aNode->parent() ;
			}
			else 
			{
				return $aNode ;
			}
		}
		
		// 尾标签
		else if($aObject->isTail())
		{
			if( $aNode->tagName() != $aObject->name() )
			{
				throw new Exception("遇到不匹配的XHTML节点，头标签:%s(line:%d), 尾标签:%s(line:%d)", array(
						$aNode->tagName() ,
						$aNode->line() ,
						$aObject->name() ,
						$aObject->line() ,
				)) ;
			}
			
			// 节点结束
			return $aNode->parent() ;
		}
		
		// 意外
		else 
		{
			throw new Exception("遇到无效的xhtml标签。源文：%s",$sTagSource) ;
		}
	}
}

?>