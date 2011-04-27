<?php

namespace jc\util ;

use jc\lang\Object;

class FilterMangeger extends Object implements IFilterMangeger
{	
	public function start() 
	{
		$this->bWorking = true ;
	}
	
	public function stop() 
	{
		$this->bWorking = false ;
	}
	
	public function isWorking()
	{
		return $this->bWorking ;
	}
	
	public function handle()
	{
		$arrArgvs = func_get_args() ;
		
		if( !$this->bWorking or empty($this->arrFilters) )
		{
			return $arrArgvs ;
		}
		
		foreach($this->arrFilters as &$arrFilter)
		{
			$arrFilterFuncArgvs = array_merge($arrArgvs,$arrFilter[1]) ;
			
			try{
				$arrArgvs = (array)call_user_func_array($arrFilter[0],$arrFilterFuncArgvs) ;
			}
			catch (StopFilterSignal $e)
			{
				return $e->returnVariables() ;
			}
		}
		
		return $arrArgvs ;
	}
	
	public function add($callback,$arrArgvs=array())
	{
		if( !is_callable($callback) )
		{
			return ;
		}
		
		array_unshift($this->arrFilters,array($callback,$arrArgvs)) ;
	}
	
	/**
	 * @return callback
	 */
	public function remove($callback)
	{
		foreach($this->arrFilters as $nIdx=>&$arrFilter)
		{
			if($arrFilter[0]==$callback)
			{
				unset($this->arrFilters[$nIdx]) ;
				return $callback ;
			}
		}
		
		return null ;
	}
	
	public function removeStackTop()
	{
		array_shift($this->arrFilters) ;
	}
	
	public function clear()
	{
		$this->arrFilters = array() ;
	}
		
	/**
	 * 
	 * @return \Iterator
	 */
	public function iterator()
	{
		return new \ArrayIterator($this->arrFilters) ;
	}
	
	private $arrFilters = array() ;
	
	private $bWorking = true ;
}

?>