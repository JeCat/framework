<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.8
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/

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
				$arrArgvs = Type::toArray(
					call_user_func_array($arrFilter[0],$arrFilterFuncArgvs)
					, Type::toArray_normal
				) ;
				
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


