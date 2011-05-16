<?php

namespace jc\ui\xhtml ;

use jc\lang\Exception;

use jc\lang\Type;

use jc\pattern\composite\IContainedable;

class Text extends ObjectBase
{
	public function source()
	{
		// 作为聚合对象时，拼接子对象
		if( $this->count() )
		{
			$sSource = '' ;
			foreach($this->childrenIterator() as $aChild)
			{
				$sSource.= $aChild->source() ;
			}
			return $sSource ;
		}
			
		else 
		{
			return parent::source() ;
		}
	}
	
	public function add($aChild,$bAdoptRelative=true)
	{
		Type::check(__NAMESPACE__.'\\ObjectBase', $aChild) ;
		
		if( $this->count() )
		{
			parent::add($aChild,$bAdoptRelative) ;
		}
		
		// 切割文本
		else 
		{
			if( $this->locate($aChild)!=parent::LOCATE_IN )
			{
				throw new Exception(__METHOD__."()传入的参数，无法满足UI对象的层属关系。") ;
			}
			
			$sSource = $this->source() ;
			
			// 之前的文本
			$sBeforeText = substr( $sSource, 0, $aChild->position()-$this->position() ) ;
			if( $sBeforeText )
			{
				parent::add( new Text($this->position(), $aChild->position()-1, $this->line(), $sBeforeText) ) ;
			}
			
			// UI对象
			parent::add( $aChild ) ;
		
			// 之后的文本
			$sAfterText = substr( $sSource, $aChild->endPosition()-$this->position()+1 ) ;
			if( $sAfterText )
			{
				$nAfterTextLine = $aChild->line()+substr_count($aChild->source(),"\n") ; // $aChild所在行数 + $aChild内的换行符出现次数
				parent::add( new Text($aChild->endPosition()+1, $this->endPosition(), $nAfterTextLine, $sAfterText) ) ;
			}
			
			// 清空自己的source ，仅仅作为一个聚合对象
			$this->setSource('') ;
		}
	}
}

?>