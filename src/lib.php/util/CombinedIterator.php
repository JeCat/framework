<?php
namespace jc\util ;

use jc\lang\Object;

class CombinedIterator extends Object implements \Iterator 
{
	public function __construct(/* ... */)
	{
		foreach(func_get_args() as $aIter)
		{
			$this->addIterator($aIter) ;
		}
	}
	
	public function addIterator(\Iterator $aIterator)
	{
		$this->arrIterators[] = $aIterator ;
		$this->rewind() ;
	}
	public function removeIterator(\Iterator $aIterator)
	{
		for (end($this->arrIterators);current($this->arrIterators);prev($this->arrIterators))
		{
			if($this->arrIterators==$aIterator)
			{
				unset( $this->arrIterators[key($this->arrIterators)] ) ;
				$this->rewind() ;
				return ;
			}
		}
	}
	public function clearIterator()
	{
		$this->arrIterators = array() ;
		$this->rewind() ;
	}
	
	
	public function current()
	{
		return ($aIterator=current($this->arrIterators))? $aIterator->current(): null ;
	}
	public function key()
	{
		return ($aIterator=current($this->arrIterators))? $aIterator->key(): null ;
	}
	public function next()
	{
		$NextEl = null ;
		
		if( $aIterator=current($this->arrIterators) )
		{
			$NextEl = $aIterator->next() ;
			
			if( !$aIterator->valid() )
			{
				$aIterator = $this->nextIterator() ;
				
				if( $aIterator )
				{
					return $aIterator->current() ;
				}
			}
		}
		
		return $NextEl ;
	}
	
	protected function nextIterator()
	{
		do {
			
			if( $aIterator=next($this->arrIterators) )
			{
				reset($aIterator) ;
			}
			
		} while( $aIterator and !$aIterator->valid() ) ;
		
		return $aIterator ;
	}
	
	public function rewind()
	{
		$aIterator = reset($this->arrIterators) ;
		return $aIterator? $aIterator->rewind(): null ;
	}
	public function valid()
	{
		return ($aIterator=current($this->arrIterators))? $aIterator->valid(): null ;
	}
	
	
	
	protected $arrIterators = array() ;
}

?>