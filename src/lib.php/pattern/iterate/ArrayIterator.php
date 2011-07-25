<?php
namespace jc\pattern\iterate ;

use jc\lang\Object;

class ArrayIterator extends Object implements INonlinearIterator
{
	public function __construct(array &$array)
	{
		$this->arrKeys = array_keys($array) ;
		$this->arrElements = array_values($array) ;
		
		$this->nEndPosition = count($this->arrKeys)-1 ;
	}
	
	public function position()
	{
		return $this->nPosition ;
	}
	
	public function current ()
	{
		return isset($this->arrElements[$this->nPosition])?
				$this->arrElements[$this->nPosition]: null ;
	}

	public function next ()
	{
		if( $this->nPosition<=$this->nEndPosition )
		{
			$this->nPosition ++ ;
		}
	}

	public function key ()
	{
		return isset($this->arrKeys[$this->nPosition])?
				$this->arrKeys[$this->nPosition]: null ;
	}

	public function valid ()
	{
		return $this->nPosition>=0 and $this->nPosition<=$this->nEndPosition ;
	}

	public function rewind ()
	{
		$this->nPosition = 0 ;
	}
	
	public function prev()
	{
		if( $this->nPosition>=0 )
		{
			$this->nPosition -- ;
		}
	}
	
	public function last()
	{
		$this->nPosition = $this->nEndPosition ;
	}

	public function seek ($nPosition)
	{
		if($nPosition<0)
		{
			$nPosition = -1 ;
		}
		
		if( $nPosition>$this->nEndPosition )
		{
			$nPosition = $this->nEndPosition + 1 ;
		}
		
		$this->nPosition = $this->nEndPosition ;
	}
	
	public function search ($element)
	{
		return array_search($element,$this->arrElements,true) ;
	}
	
	public function searchKey ($key)
	{
		if( is_object($key) )
		{
			$key = strval($key) ;
		}
		return array_search($key,$this->arrKeys,true) ;
	}
	
	private $nPosition = 0 ;
	
	private $nEndPosition = 0 ;
	
	private $arrKeys ;
	
	private $arrElements ;
}

?>