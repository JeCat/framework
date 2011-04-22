<?php

namespace jc\ui\xhtml ;

use jc\io\IOutputStream;
use jc\util\IDataSrc;
use jc\ui\Object;

class Node extends Object implements INode
{
	static public function type()
	{
		return __CLASS__ ;
	}
	
	public function __construct($sTagName)
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
	
	public function compile(IOutputStream $aDev)
	{
		$aDev->write( str_repeat("\t",$this->depth()) ) ;
		$aDev->write('<') ;
		$aDev->write($this->tagName()) ;
		
		$this->aAttributes->compile($aDev) ;
		
		// 单行节点
		if( !$this->childrenCount() and $this->isSingle() )
		{
			$aDev->write(" />\r\n") ;
		}
		else 
		{
			$aDev->write(">") ;
			
			if($this->isMultiLine())
			{
				$aDev->write("\r\n") ;
			}
			
			foreach ($this->childrenIterator() as $aChildNode)
			{
				$aChildNode->compile($aDev) ;
			}
		
			if($this->isMultiLine())
			{
				$aDev->write("\r\n") ;
			}
			
			$aDev->write("</") ;
			$aDev->write($this->tagName()) ;
			$aDev->write(">") ;
		}
		
	}

	public function isMultiLine()
	{
		return $this->bMultiLine ;
	}
	public function setMultiLine($bMultiLine=true)
	{
		$this->bMultiLine = $bMultiLine? true: false ;
	}
	
	
	private $sTagName ;
	
	private $bSingle = true ;
	
	private $bMultiLine = true ;
	
	private $bPre = true ;
	
	private $aAttributes ;
}

?>