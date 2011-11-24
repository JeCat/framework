<?php
namespace jc\util\match ;

use jc\util\HashTable;

class ResultSet extends HashTable
{
	/**
	 * 返回第一项匹配结果
	 * 通常用户不需要遍历匹配结果集的情况
	 * 如果结果集为空返回 false ，可以用判断是否匹配
	 * 
	 * @return Result
	 */
	public function result()
	{
		if( !$this->count() )
		{
			return false ;
		}
		
		else 
		{
			return $this->rewind() ;
		}
	}

	public function content($nGrp=0) 
	{
		return $this->count()? $this->rewind()->content($nGrp): null ;
	}
	
	public function position($nGrp=0) 
	{
		return $this->count()? $this->rewind()->position($nGrp): null ;
	}
	
	public function length($nGrp=0)
	{
		return $this->count()? $this->rewind()->length($nGrp): null ;
	}
	
	public function add(Result $aResult)
	{
		parent::add($aResult) ;
	}
	
	
	
}

?>