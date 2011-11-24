<?php

namespace org\jecat\framework\util ;

use org\jecat\framework\lang\Type;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\lang\Object;

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
		$arrOriArgvs = $arrArgvs = func_get_args() ;
		
		if( !$this->bWorking or empty($this->arrFilters) )
		{
			return $arrOriArgvs ;
		}
		
		foreach($this->arrFilters as &$arrFilter)
		{
			$arrFilterFuncArgvs = array_merge($arrArgvs,$arrFilter[1]) ;
			
			try{
				$arrArgvs = (array)call_user_func_array($arrFilter[0],$arrFilterFuncArgvs) ;
				
				if( ($miss=count($arrOriArgvs)-count($arrArgvs)) > 0 )
				{
					while($miss--)
					{
						$arrArgvs[] = null ;
					}
				}
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
			throw new Exception(__METHOD__."()的参数\$callback必须为回调函数类型，传入的类型为：%s",Type::reflectType($callback)) ;
		}
		
		if( !is_array($arrArgvs) )
		{
			$arrArgvs = array($arrArgvs) ;
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
	 * @return org\jecat\framework\pattern\iterate\INonlinearIterator
	 */
	public function iterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrFilters) ;
	}
	
	private $arrFilters = array() ;
	
	private $bWorking = true ;
}

?>