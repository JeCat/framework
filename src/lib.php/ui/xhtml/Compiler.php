<?php

namespace jc\ui\xhtml ;


use jc\fs\File;
use jc\lang\Type;
use jc\ui\xhtml\nodes\TagLibrary;
use jc\ui\IObject;
use jc\ui\CompilerBase;
use jc\util\match\RegExp;

class Compiler extends CompilerBase
{
	public function __construct()
	{
		$this->aTagLibrary = new TagLibrary() ;
		$this->aExpressionCompiler = new ExpressionCompiler() ;
		
	}

	public function tagLibrary()
	{
		return $this->aTagLibrary ;
	}
	public function setTagLibrary(TagLibrary $aTagLibrary)
	{
		$this->aTagLibrary = $aTagLibrary ;
	}
	
	/**
	 * @return RegExp
	 */
	public function regexpFindTag()
	{
		if(!$this->aRegexpFindTag)
		{
			$this->aRegexpFindTag = new RegExp("//") ;
		}
				
		return $this->aRegexpFindTag ;
	}

	
	
	/**
	 * @return INode
	 */
	protected function buildObject(\DOMNode $aSrcEle,IObject $aParent=null)
	{	
		$aNode = $this->createObjectFromDomNode($aSrcEle) ;	
	
		if($aParent)
		{
			$aNode->setParent($aParent) ;
		}
		
		// 子节点
		if($aSrcEle->childNodes)
		{			
			foreach($aSrcEle->childNodes as $aSrcChildEle)
			{
				switch (get_class($aSrcChildEle))
				{
				case 'DOMText' :
					$sText = $aSrcChildEle->wholeText ;
					//if(!empty($sText))
					//{
						$aText = new Text($sText) ;
						$aText->setParent($aNode) ;
						$aNode->addChild( $aText ) ; 
					//}
					break ;
				case 'DOMComment' :
					$aSrcChildEle->data ; 
					break ;
					
				case 'DOMCdataSection' :
					$aSrcChildEle->data ;
					break ;
					
				case 'DOMDocumentType' :
					if($aSrcChildEle->internalSubset)
					{
						$sString = $aSrcChildEle->internalSubset."\r\n" ;
						$aText = new Text($sString,false) ;
						$aText->setParent($aNode) ;
						$aNode->addChild( $aText ) ;
					}
					break ;
					
				default:
					// echo "\$aSrcChildEle:",Type::reflectType($aSrcChildEle),"\r\n" ;
					$aNode->addChild( $this->buildObject($aSrcChildEle,$aNode) ) ;
					break ;
				}
			}
		}
		
		return $aNode ;
	}
	
	/**
	 * @return jc\ui\IObject
	 */
	protected function createObjectFromDomNode(\DOMNode $aSrcEle)
	{
		$sClassName = $this->tagLibrary()->getClassName($aSrcEle->nodeName) ;
		$aNode = new $sClassName($aSrcEle->nodeName) ;
		
		if( $aNode instanceof INode )
		{
			$aNode->setSingle( $this->tagLibrary()->isSingle($aSrcEle->nodeName) ) ;
		
			// 属性
			$aNodeAttr = new Attributes() ;
			if($aSrcEle->attributes)
			{
				foreach($aSrcEle->attributes as $aAttrEle)
				{
					$aNodeAttr->set($aAttrEle->name,$aAttrEle->value) ;
				}
			}
			$aNode->setAttributes($aNodeAttr) ;
			
			// single tag
			$aNode->setSingle(
				$this->tagLibrary()->isSingle($aSrcEle->nodeName)
			) ;
			
			// inline diplay node
			$aNode->setInline(
				$this->tagLibrary()->isInline($aSrcEle->nodeName)
			) ;
			
			// multiline
			$aNode->setMultiLine(
				$this->tagLibrary()->isMultiLine($aSrcEle->nodeName)
			) ;
		}
		
		return $aNode ;
	} 

	/**
	 * @return jc\ui\ICompiled
	 */
	public function loadCompiled($sCompiledPath)
	{
		return new Compiled($sCompiledPath) ;
	}
	
	public function expression($sSource)
	{
		return $this->aExpressionCompiler->compile($sSource) ;
	}
	
	private $aTagLibrary ;
	
	private $aExpressionCompiler ;
	
}

?>