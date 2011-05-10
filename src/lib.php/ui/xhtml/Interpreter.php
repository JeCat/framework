<?php
namespace jc\ui\xhtml ;

use jc\ui\IInterpreter;
use jc\lang\Object ;
use jc\util\match\RegExp;
use jc\util\String;
use jc\lang\Exception;
use jc\ui\xhtml\nodes\TagLibrary;

class Interpreter extends Object implements IInterpreter
{
	public function __construct()
	{
		$this->aQuotePreprocessor = new QuotePreprocessor() ;
		
		$this->aRegextFindHeadTags = new RegExp("|<([\\w:_\\-]+)([^>]*?)(/)?>|s") ;
		
		$this->aRegextFindTailTags = new RegExp("|</([\\w:_\\-]+)>|s") ;
		
		$this->aRegextParseTagAttributes = new RegExp("|([\\w_\\.\\-]+)\\s*=\\s*([\"'])([^\"']+)\\2|s") ;
	}	

	/**
	 * @return TagLibrary
	 */
	public function tagLibrary()
	{
		return $this->aTagLibrary ;
	}
	public function setTagLibrary(TagLibrary $aTagLibrary)
	{
		$this->aTagLibrary = $aTagLibrary ;
	}
	
	/**
	 * return IObject
	 */
	public function parse($sSourcePath) 
	{		
		$aSource = String::createFromFile($sSourcePath) ;
		
		// 统一换行符
		$aSource->replace("\r\n","\n") ;
		$aSource->replace("\r","\n") ;
		
		$this->aQuotePreprocessor->encode($aSource) ;
		
		// parse tags
		$arrTags = $this->parseTags($aSource) ;
		
		// parse text
		$arrTexts = $this->parseTexts($aSource,$arrTags) ;
		
		// build nodes
		$arrNodes = $this->buildNodes($arrTags,$sSourcePath) ;
		
		
		// merge nodes and texts
		$aRoot = new ObjectBase(0,$aSource->length()-1,0,'') ;
		foreach(array_merge($arrNodes,$arrTexts) as $aObject)
		{
			$aRoot->addChild($aObject) ;
		}
		
		return $aRoot ;
	}
	
	public function parseTags(String $aSource)
	{
		$arrTags = array() ;

		// head(single) tags
		foreach($this->aRegextFindHeadTags->match($aSource) as $aRes)
		{
			$aAttrs = new Attributes() ;
			
			$sAttrsSrc = $this->aQuotePreprocessor->decode($aRes->result(2)) ;
			$aAttrs->setSource($sAttrsSrc) ;
			
			$sAttributes = trim($sAttrsSrc) ;
			if($sAttributes)
			{
				foreach($this->aRegextParseTagAttributes->match($sAttributes) as $aAttrRes)
				{
					$aAttrs->set(
						$aAttrRes->result(1)
						, $this->aQuotePreprocessor->decode($aAttrRes->result(3))
					) ;
				}
			}

			$arrTags[ $aRes->position() ] = new Tag(
				$aRes->result(1)
				, $aAttrs 
				, ($aRes->result(3)=='/')? Tag::TYPE_SINGLE: Tag::TYPE_HEAD
				, $aRes->position()
				, $aRes->position() + $aRes->length() - 1
				, substr_count($aSource,"\n",0,$aRes->position()+1)
				, $this->aQuotePreprocessor->decode($aRes->result())
			) ;
		}
		
		// tail tags
		foreach($this->aRegextFindTailTags->match($aSource) as $aRes)
		{
			$arrTags[ $aRes->position() ] = new Tag(
				$aRes->result(1)
				, new Attributes() 
				, Tag::TYPE_TAIL
				, $aRes->position()
				, $aRes->position() + $aRes->length() - 1
				, substr_count($aSource,"\n",0,$aRes->position()+1)
				, $this->aQuotePreprocessor->decode($aRes->result())
			) ;
		}
		
		ksort($arrTags) ;
		
		return $arrTags ;
	}

	protected function parseTexts($aSource,array $arrTags)
	{
		$arrTexts = array() ;
		
		$nIdx = 0 ;
		foreach($arrTags as $aTag)
		{
			$nLen = $aTag->position()-$nIdx ;
			if( $nLen )
			{
				$sTextSource = $this->aQuotePreprocessor->decode(substr($aSource,$nIdx,$nLen)) ;
				$nLine = substr_count($aSource,"\n",0,$nIdx+1) ;
				$nEndPosition = $aTag->position() - 1 ;
				
				$arrTexts[] = new Text($nIdx,$nEndPosition,$nLine,$sTextSource) ;
			}
			
			$nIdx = $aTag->endPosition() + 1 ;
		}
		
		// last pice
		if( $nIdx<$aSource->length()-1 )
		{
			$sTextSource = $this->aQuotePreprocessor->decode(substr($aSource,$nIdx)) ;
			$arrTexts[] = new Text($nIdx,$aSource->length()-1,substr_count($aSource,"\n",0,$nIdx+1),$sTextSource) ;
		}
		
		return $arrTexts ;
	}
	
	protected function buildNodes(array $arrTags,$sSourcePath)
	{
		$arrStack = array() ;
		$arrNodes = array() ;
		
		foreach($arrTags as $aTag)
		{
			if( $aTag->isSingle() )
			{
				$arrNodes[] = $this->createNode($aTag) ;
			}
			else if( $aTag->isHead() )
			{
				array_push($arrStack,$aTag) ;
			}
			else if( $aTag->isTail() )
			{
				if( !$aHeadTag=array_pop($arrStack) )
				{
					$sMessage = "UI模板错误，出现多余的闭合节点(%s)。\r\n" ;
					$sMessage.= "template file: %s\r\n" ;
					$sMessage.= "tag position: line %d" ;
					
					throw new Exception($sMessage,array(
						$aTag->name()
						, $sSourcePath
						, $aTag->line()	
					)) ;
				}
				
				if( $aHeadTag->name()!=$aTag->name() )
				{
					$sMessage = "UI模板错误，节点(%s)没有正确闭合，遇到不匹配的尾标签(%s)。\r\n" ;
					$sMessage.= "template file: %s\r\n" ;
					$sMessage.= "head tag position: line %d" ;
					$sMessage.= "tail tag position: line %d" ;
					
					throw new Exception($sMessage,array(
							$aHeadTag->name() 
							, $aTag->name()
							, $sSourcePath
							, $aHeadTag->line()
							, $aTag->line()							
					)) ;
				}
				
				$arrNodes[] = $this->createNode($aHeadTag,$aTag) ;
			}
		}
		
		if(!empty($arrStack))
		{
			$sMessage = "UI模板错误，出现多余的标签，无法将这些标签配对成节点：\r\n" ;
			$sMessage.= "%s\r\n" ;
			$sMessage.= "template file: %s\r\n" ;
			
			$arrTagNames = array() ;
			foreach($arrStack as $aTag)
			{
				$arrTagNames[] = "<" . $aTag->name() . "> (line " . $aTag->line() . ")" ;
			}
			$sTagNames = implode(", ", $arrTagNames) ;
			
			throw new Exception($sMessage,array( $sTagNames, $sSourcePath )) ;
		}
		
		return $arrNodes ;
	}
	
	protected function createNode(Tag $aHeadTag,Tag $aTailTag=null)
	{
		$sClassName = $this->aTagLibrary->getClassName($aHeadTag->name()) ;
		return new $sClassName($aHeadTag,$aTailTag) ;
	}
		
	public function preprocessor ()
	{
		return $this->aQuotePreprocessor ;
	}
	
	/**
	 * @return Node
	 */
	public function createNode(Tag $aHeadTag,Tag $aTailTag=null)
	{
		$sClassName = $this->tagLibrary()->getClassName( $aHeadTag->name() ) ;
		
		return new $sClassName($aHeadTag,$aTailTag) ;
	}
	
	private $aQuotePreprocessor ;
	
	private $aRegextFindHeadTags ;
	
	private $aRegextFindTailTags ;
	
	private $aRegextParseTagAttributes ;
	
	private $aTagLibrary ;
	
}

?>