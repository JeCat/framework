<?php

namespace jc\ui\xhtml ;
use jc\lang\Type;
use jc\ui\ICompiler;

use jc\ui\xhtml\nodes\TagLibrary;

use jc\ui\IObject;

use jc\io\IOutputStream;
use jc\util\IDataSrc;
use jc\ui\Object;

class Node extends Object implements INode
{
	const FORMAT_NEWLINE = 1 ;
	const FORMAT_INDENT = 2 ;
	
	static public function type()
	{
		return __CLASS__ ;
	}
	
	public function __construct($sTagName,TagLibrary $aTagLib=null)
	{
		$this->sTagName = $sTagName ;
		$this->addChildTypes(__CLASS__) ;
		$this->addChildTypes(__NAMESPACE__.'\\Text') ;
		parent::__construct() ;
	}
	
	public function tagName()
	{
		return $this->sTagName ;
	}
	
	public function setTagName($sTagName)
	{
		$this->sTagName = $sTagName ;
	}

	/**
	 * return Attributes
	 */
	public function attributes()
	{
		if(!$this->aAttributes)
		{
			$this->aAttributes = new Attributes() ;
		}
		
		return $this->aAttributes ;
	}
	
	public function setAttributes(Attributes $aAttributes)
	{
		$this->aAttributes = $aAttributes ;
	}
	
	public function isSingle()
	{
		return $this->bSingle ;
	}
	
	public function setSingle($bSingle=true)
	{
		$this->bSingle = $bSingle? true: false ;
	}
	
	public function pre()
	{
		return $this->bPre ;
	}
	
	public function setPre($bPre=true)
	{
		$this->bPre = $bPre? true: false ;
	}
	
	public function compile(IOutputStream $aDev,ICompiler $aCompiler)
	{		
		$aDev->write("<") ;
		$aDev->write($this->tagName()) ;
		
		$this->aAttributes->compile($aDev) ;
		
		// 单行节点
		if( !$this->childrenCount() and $this->isSingle() )
		{
			$aDev->write(" />") ;
		}
		else 
		{
			$aDev->write(">") ;
			
			$nIdx = 0 ;
			foreach ($this->childrenIterator() as $aChildNode)
			{
				self::compileFormatForChild($aDev,$this,$aChildNode,$nIdx++) ;
				
				$aChildNode->compile($aDev,$aCompiler) ;
			}
			
			// 尾标签
			if($this->isMultiLine())
			{
				$aDev->write("\r\n") ;
				self::compileFormatIndent($aDev, $this) ;
			}
			$aDev->write("</") ;
			$aDev->write($this->tagName()) ;
			$aDev->write(">") ;
		}
		
	}
	
	
	/**
	 * 缩进
	 */
	static public function compileFormatForChild(IOutputStream $aDev,INode $aParent,IObject $aChild,$nChildIdx)
	{
		if( !($aChild instanceof INode) )
		{
			return ;
		}
		
		/*if( ($nChildIdx==0 and $aParent->isMultiLine() )		// block节点的第一个 INode child 
			or !$aChild->isInline() )							// 或者 block child
		{
			$aDev->write("\r\n") ;
			self::compileFormatIndent($aDev, $aChild) ;
		}*/
	}
	
	/**
	 * 缩进
	 */
	static public function compileFormatIndent(IOutputStream $aDev,INode $aNode)
	{
		//$aDev->write( str_repeat("\t",$aNode->depth()-1) ) ;		
	}

	public function isInline()
	{
		return $this->bInline ;
	}
	public function setInline($bInline=true)
	{
		$this->bInline = $bInline? true: false ;
	}
	
	
	public function isMultiLine()
	{
		return $this->bMultiLine ;
	}
	
	public function setMultiLine($bMultiLine=true)
	{
		$this->bMultiLine = $bMultiLine ;
	}
	
	private $sTagName ;
	
	private $bSingle = true ;
	
	private $bInline = true ;
	
	private $bPre = true ;
	
	private $bMultiLine = true ;
	
	private $aAttributes ;
}

?>