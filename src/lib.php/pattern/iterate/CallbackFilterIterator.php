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
		
		parent::__construct($iterator);
	}

	public function accept ()
	{
		foreach($this->arrCallbacks as $fnCallback)
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
		if(in_array($fnCallback, $this->arrCallbacks , true)){
			return;
		}
		$this->arrCallbacks[] = $fnCallback ;
	}
	
	public function removeCallback($fnCallback){
		unset($this->arrCallbacks[array_search($fnCallback, $this->arrCallbacks)]);
	}
	
	public function clearCallback(){
		$this->arrCallbacks = array();
	}
	
	private $arrCallbacks = array() ;
}

?>