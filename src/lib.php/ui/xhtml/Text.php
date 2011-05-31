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
		Type::assert(__NAMESPACE__.'\\ObjectBase', $aChild, 'aChild') ;
	}
}

?>