<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.8
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\ui\xhtml\parsers ;

use org\jecat\framework\ui\xhtml\Node;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Assert;
use org\jecat\framework\ui\xhtml\ObjectBase;
use org\jecat\framework\ui\xhtml\IObject;
use org\jecat\framework\ui\xhtml\Tag;
use org\jecat\framework\util\String;

class ParserStateTag extends ParserState 
{
	public function __construct()
	{
		parent::__construct() ;
		self::setSingleton($this) ;
		
		$this->arrChangeToStates[__NAMESPACE__.'\\ParserStateMacro'] = ParserStateMacro::singleton() ;
		$this->arrChangeToStates[__NAMESPACE__.'\\ParserStateAttribute'] = ParserStateAttribute::singleton() ;
	}

	public function active(IObject $aParent,String $aSource,&$nPosition)
	{
		$nLine = ObjectBase::getLine($aSource,$nPosition) ;
		$nStartPos = $nPosition ;
		
		// 标签名称
		if( !$sTagName=$this->parseTagName($aSource,$nPosition) )
		{
			throw new Exception("UI引擎在分析模板时遇到无效的xhtml节点：缺少节点名称(位置：%d行)",$nLine) ;
		}
		
		$aTag = new Tag($sTagName, null, 0, $nStartPos, 0, $nLine, '') ;
		
		// 尾标签
		if($sTagName[0]=='/')
		{
			if( !($aParent instanceof Node) )
			{
				throw new Exception("错误类型 %s") ;
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
			$nPos=$nPosition ;
			$sTagName = strtolower($this->parseTagName($aSource,$nPos)) ;
			
			// 清理为标签前的 /
			if( strlen($sTagName)>=2 and $sTagName[0]=='/' )
			{
				$sTagName = substr($sTagName,1) ;
			}
			
			if( in_array($sTagName,$this->arrTagNames) )
			{
				return true ;
			}
		}
		
		return false ;
	}

	public function complete(IObject $aObject,String $aSource,$nPosition)
	{
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Tag", $aObject, 'aObject') ;

		$aNode = $aObject->parent() ;
		Assert::type("org\\jecat\\framework\\ui\\xhtml\\Node", $aNode) ;
	
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

					$aAttrVal->setName($sAttrName) ;

					// 移除 val name
					array_pop($arrAttrs) ;
					$arrAttrs[] = $aAttrVal ;
				}
				else
				{
					$arrAttrs[] = $aVal ;
				}
				
				$aPrevAttrVal = $aAttrValIterator->current() ;
			}
			
			$aAttrs->clear() ;
			foreach($arrAttrs as $aAttrVal)
			{
				$aAttrs->add($aAttrVal) ;
			}
			
			
			if( $aObject->isSingle() )
			{
				// 单行标签节点结束
				return ParserStateNode::singleton()->complete($aNode,$aSource,$nPosition) ;
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
			return ParserStateNode::singleton()->complete($aNode,$aSource,$nPosition) ;
		}
		
		// 意外
		else 
		{
			throw new Exception("遇到无效的xhtml标签。源文：%s",$sTagSource) ;
		}
	}
	
	public function parseTagName( String $aSource,&$nPosition )
	{
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
		
		return $sTagName ;
	}
	
	public function addTagNames($sName/*,...*/)
	{
		$arrNames = func_get_args() ;
		
		foreach($arrNames as $sName)
		{
			$sName = strtolower($sName) ;
			
			if( !in_array($sName,$this->arrTagNames) )
			{
				$this->arrTagNames[] = $sName ;
			}
		}
	}
	
	private $arrTagNames = array("a","abbr","acronym","address","applet","area","article","aside","audio","b","base","basefont","bdo","big","blockquote","body","br","button","canvas","caption","center","cite","code","col","colgroup","command","datalist","dd","del","details","dfn","dir","div","dl","dt","em","embed","fieldset","figcaption","figure","font","footer","form","frame","frameset","h1","h2","h3","h4","h5","h6","h7","h8","h9","h10","head","header","hgroup","hr","html","i","iframe","img","input","ins","keygen","kbd","label","legend","li","link","map","mark","menu","meta","meter","nav","noframes","noscript","object","ol","optgroup","option","output","p","param","pre","progress","q","rp","rt","ruby","s","samp","script","section","select","small","source","span","strike","strong","style","sub","summary","sup","table","tbody","td","textarea","tfoot","th","thead","time","title","tr","tt","u","ul","var","video") ;
}

