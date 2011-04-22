<?php

namespace jc\ui\xhtml ;


use jc\lang\Type;
use jc\lang\Exception;
use jc\ui\xhtml\nodes\TagLibrary;
use jc\ui\IObject;
use jc\ui\CompilerBase;

class Compiler extends CompilerBase
{
	public function __construct()
	{
		$this->aTagLibrary = new TagLibrary() ;
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
	 * @return IObject
	 */
	protected function buildObjectTree($sSourcePath)
	{
		$aDoc = new \DOMDocument() ;
		
		if(!$aDoc->loadHTMLFile($sSourcePath))
		{
			throw new Exception("文件不是有效的 XHtml格式：%s",$sSourcePath) ;
		}
		
		$aObjectTree = $this->buildObject($aDoc) ;
	}
	
	/**
	 * @return INode
	 */
	protected function buildObject(\DOMNode $aSrcEle)
	{		
		$sClassName = $this->tagLibrary()->getClassName($aSrcEle->nodeName) ;
		$aNode = new $sClassName($aSrcEle->nodeName) ;
		
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
		
		// 子节点
		if($aSrcEle->childNodes)
		{
			foreach($aSrcEle->childNodes as $aSrcChildEle)
			{
				
				switch (get_class($aSrcChildEle))
				{
				case 'DOMText' :
					$aNode->addChild( new Text($aSrcChildEle->wholeText) ) ; 
					break ;
				case 'DOMComment' :
					$aSrcChildEle->data ; 
					break ;
					
				case 'DOMCdataSection' :
					$aSrcChildEle->data ;
					break ;
					
				default:
					echo "\$aSrcChildEle:",Type::reflectType($aSrcChildEle),"\r\n" ;
					$aNode->addChild( $this->buildObject($aSrcChildEle) ) ;
					break ;
				}
			}
		}
		
		return $aNode ;
	}

	/**
	 * @return jc\ui\ICompiled
	 */
	function loadCompiled($sCompiledPath)
	{
		return new Compiled($sCompiledPath) ;
	}
	
	private $aTagLibrary ;
}

?>