<?php
namespace jc\pattern\iterate ;

class CallbackFilterIterator extends \FilterIterator
{

	public function __construct(\Iterator $iterator,$fnCallback=null)
	{
		if($fnCallback)
		{
			$this->addCallback($fnCallback) ;
		}
	}

	public function accept ()
	{
		foreach($this->arrCallbacks[] as $fnCallback)
		{
			if( call_user_func_array($fnCallback,array($this->getInnerIterator()))===false )
			{
				return false ;
			}
		}
		
		return true ;		
	}
	
	public function addCallback($fnCallback)
	{
		$this->arrCallbacks[] = $fnCallback ;
	}
	
	private $arrCallbacks = array() ;
}

?>