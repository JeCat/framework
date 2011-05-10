<?php
namespace jc\ui\xhtml\parsers ;

use jc\util\match\Result;

use jc\util\match\RegExp;

use jc\ui\xhtml\Attributes;

use jc\ui\xhtml\Text;

use jc\lang\Exception;
use jc\ui\xhtml\Node;
use jc\ui\xhtml\Tag;
use jc\ui\IObject;
use jc\ui\IInterpreter;
use jc\lang\Object;
use jc\util\String;

class NodeParser extends Object implements IInterpreter
{
	public function __construct()
	{
		$sMark = md5(__CLASS__) ;
		$this->aRegextFindQuote = new RegExp("/~\\*\\*{$sMark}\\{\\[(.+?)\\]\\}{$sMark}\\*\\*~/s") ;
		
		$this->aRegextFindHeadTags = new RegExp("|<([\\w:_\\-]+)([^>]*?)(/)?>|s") ;
		$this->aRegextFindTailTags = new RegExp("|</([\\w:_\\-]+)>|s") ;
		$this->aRegextParseTagAttributes = new RegExp("|([\\w_\\.\\-]+)\\s*=\\s*([\"'])([^\"']+)\\2|s") ;
	}
	
	/**
	 * return IObject
	 */
	public function parse(String $aSource,IObject $aObjectContainer,$sSourcePath)
	{
		// 前处理
		$this->preprocessor($aSource) ;
		
		// parse tags
		$arrTags = $this->parseTags($aSource) ;
		
		// parse text
		$arrTexts = $this->parseTexts($aSource,$arrTags) ;
		
		// build nodes
		$arrNodes = $this->buildNodes($arrTags,$sSourcePath) ;
		
		// merge nodes and texts
		foreach(array_merge($arrNodes,$arrTexts) as $aObject)
		{
			$aObjectContainer->addChild($aObject) ;
		}
	}
	
	public function preprocessor(String $aSource)
	{
		// 统一换行符
		$aSource->replace("\r\n","\n") ;
		$aSource->replace("\r","\n") ;
		$aSource->replace("\n","\r\n") ;
		
		 // 引号段编码
		self::quoteEncode($aSource) ;
	}

	public function parseTags(String $aSource)
	{
		$arrTags = array() ;

		// head(single) tags
		foreach($this->aRegextFindHeadTags->match($aSource) as $aRes)
		{
			$nTagPos = $aRes->position() ;
			$nTagEndPos = $nTagPos + $aRes->length() - 1 ;
			$nTagLine = substr_count($aSource,"\n",0,$aRes->position()+1) ;
			
			
			$aAttrs = new Attributes() ;
			
			$nAttrsStartPos = $aRes->position(2) ;
			$sAttrsSrc = self::quoteDecode($aRes->result(2)) ;
			$aAttrs->setSource($sAttrsSrc) ;
			$sAttributes = trim($sAttrsSrc) ;
			if($sAttributes)
			{
				foreach($this->aRegextParseTagAttributes->match($sAttributes) as $aAttrRes)
				{
					$nAttrPos = $aAttrRes->position(3) ; 
					$nAttrEndPos = $nAttrPos + $aAttrRes->length(3) - 1 ;
					$sAttrSource = $aAttrRes->result() ;

					$nAttrLine = $nTagLine + substr_count($sAttributes,"\n",1,$nAttrPos) ;
					
					$aAttrs->set(
						$aAttrRes->result(1)
						, new Text(
							$nAttrsStartPos+$nAttrPos, $nAttrsStartPos+$nAttrEndPos, $nAttrLine, self::quoteDecode($aAttrRes->result(3))
						)
					) ;
				}
			}

			$arrTags[ $aRes->position() ] = new Tag(
				$aRes->result(1)
				, $aAttrs 
				, ($aRes->result(3)=='/')? Tag::TYPE_SINGLE: Tag::TYPE_HEAD
				, $nTagPos
				, $nTagEndPos
				, $nTagLine
				, self::quoteDecode($aRes->result())
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
				, self::quoteDecode($aRes->result())
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
				$sTextSource = self::quoteDecode(substr($aSource,$nIdx,$nLen)) ;
				$nLine = substr_count($aSource,"\n",0,$nIdx+1) ;
				$nEndPosition = $aTag->position() - 1 ;
				
				$arrTexts[] = new Text($nIdx,$nEndPosition,$nLine,$sTextSource) ;
			}
			
			$nIdx = $aTag->endPosition() + 1 ;
		}
		
		// last pice
		if( $nIdx<$aSource->length()-1 )
		{
			$sTextSource = self::quoteDecode(substr($aSource,$nIdx)) ;
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
				$arrNodes[] = new Node($aTag) ;
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
				
				$arrNodes[] = new Node($aHeadTag,$aTag) ;
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
	
	static public function quoteEncode(String $aSource)
	{
		// token_get_all 会丢弃 <script> 后的内容，所以用 htmlspecialchars() 编码处理
		$aSource->set(htmlspecialchars($aSource,ENT_NOQUOTES)) ;
		
		$aSource->insert("<?php ",0) ;
		$aSource->append("?>") ;
		$arrTokens = token_get_all($aSource) ;
		array_shift($arrTokens) ;
		array_pop($arrTokens) ;
		$aSource->clear() ;
		
		
		foreach ($arrTokens as $oneToken)
		{
			if( is_string($oneToken) )
			{
				$aSource->append($oneToken) ;
			}
			else 
			{
				$sContent = $oneToken[1] ;
				
				// 
				if( isset($oneToken[0]) and $oneToken[0]==T_CONSTANT_ENCAPSED_STRING )
				{
					$sQuote = $sContent[0] ;
					$sEncoded = substr($sContent,1,strlen($sContent)-2) ;
					$sEncoded = base64_encode($sEncoded) ;
					$sMark = md5(__CLASS__) ;

					$aSource->append(
						sprintf("%s~**%s{[%s]}%s**~%s"
							, $sQuote
							, $sMark
							, $sEncoded
							, $sMark
							, $sQuote
						)
					) ;
				}
				else
				{
					$aSource->append($sContent) ;
				}
			}
		}
		
		$aSource->set(htmlspecialchars_decode($aSource)) ;		
	}
	
	public function quoteDecode($aSource)
	{
		return $this->aRegextFindQuote->callbackReplace($aSource,function(Result $aRes){
			return base64_decode($aRes->result(1)) ;
		}) ;
	}
	
	private $aRegextFindQuote ;
	
	private $aRegextFindHeadTags ;
	
	private $aRegextFindTailTags ;
	
	private $aRegextParseTagAttributes ;
}

?>