<?php
namespace jc\pattern\iterate ;

use jc\lang\Object;

class CallbackFilterIterator extends Object implements \OuterIterator
{
	public function __construct(\Iterator $aOriginIterator,$fnCallback=null)
	{
		if($fnCallback)
		{
			$this->addCallback($fnCallback) ;
		}
		
		$this->aOriginIterator = $aOriginIterator ;
	}

	public function rewind()
	{
		$this->aOriginIterator->rewind() ;
		
		if( !$this->accept() )
		{
			$this->next() ;
		}
	}
	
	public function next()
	{
		do{
			$this->aOriginIterator->next() ;
		} while( $this->aOriginIterator->valid() and !$this->accept() ) ;
	}

	public function valid()
	{
		if( !$this->accept() )
		{
			$this->next() ;
		}
		
		return $this->aOriginIterator->valid() ;
	}
	
	public function current()
	{
		if( !$this->accept() )
		{
			$this->next() ;
		}
		
		return $this->aOriginIterator->current() ;
	}


	public function key()
	{
		if( !$this->accept() )
		{
			$this->next() ;
		}
		
		return $this->aOriginIterator->key() ;
	}
	
	/**
	 * @return IReversableIterator
	 */
	public function getInnerIterator ()
	{
		return $this->aOriginIterator ;
	}
	
	public function accept ()
	{
		foreach($this->arrCallbacks as $fnCallback)
		{
			if( call_user_func_array($fnCallback,array($this->aOriginIterator))===false )
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
	
	private $aOriginIterator ;

}

?>