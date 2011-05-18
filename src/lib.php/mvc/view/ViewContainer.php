<?php
namespace jc\mvc ;

class ViewContainer extends View
{
	public function addAcceptNames($names)
	{
		foreach((array)$Classes as $sName)
		{
			$sName = strval($sName) ;
			if( !in_array($sName,$this->arrAcceptNames) )
			{
				$this->arrAcceptNames[] = $sName ;
			}
		}
	}
	
	public function accept($object)
	{
		if( !$object instanceof jc\mvc\IView )
		{
			return false ;
		}
		
		if( parent::accept($object) )
		{
			return true ;
		}
		
		foreach($this->arrAcceptNames as $sName)
		{
			if( $object->hasName($sName) )
			{
				return true ;
			}
		}
	}
	
}

?>