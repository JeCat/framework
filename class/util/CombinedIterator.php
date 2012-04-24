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
//  正在使用的这个版本是：0.7.1
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

use org\jecat\framework\lang\Object;

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
				$aIterator->rewind() ;
			}
			
		} while( $aIterator and !$aIterator->valid() ) ;
		
		return $aIterator ;
	}
	
	public function rewind()
	{
		$aIterator = reset($this->arrIterators) ;
		if(!$aIterator)
		{
			return null ;
		}
		
		if(!$aIterator->valid())
		{
			$aIterator = $this->nextIterator() ;
			if(!$aIterator)
			{
				return null ;
			}
		}
		
		return $aIterator->rewind() ;
	}
	public function valid()
	{
		return ($aIterator=current($this->arrIterators))? $aIterator->valid(): null ;
	}
	
	
	
	protected $arrIterators = array() ;
}


