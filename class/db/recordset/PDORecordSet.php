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
namespace org\jecat\framework\db\recordset ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class PDORecordSet extends Object implements IRecordSet
{
	public function __construct(\PDOStatement $aPDOStatement)
	{
		$this->nRowCnt = $aPDOStatement->rowCount() ;
		$this->aPDOStatement = $aPDOStatement ;
		
		parent::__construct() ;
	}

	public function rowCount()
	{
		return $this->nRowCnt ;
	}

	public function rewind()
	{
		$this->nRowIdx = 0 ;
	}
	
	public function seek($nRow)
	{
		if( $nRow>=$this->nRowCnt )
		{
			throw new Exception(__METHOD__.'() 参数 $nRow 值超出了数据集的范围(%d)。',$this->nRowCnt) ;
		}
		
		$this->nRowIdx = $nRow ;
	}
	
	public function next()
	{
		$this->nRowIdx ++ ;
	}
	
	public function valid()
	{
		return $this->nRowIdx<$this->nRowCnt ;
	}
	
	public function current()
	{
		return $this->row( IRecordSet::currentRow ) ;
	}
	
	public function row( $nRow=IRecordSet::currentRow )
	{
		$nRow = intval($nRow) ;
		
		if( $nRow==IRecordSet::currentRow )
		{
			$nRow = $this->nRowIdx ;
		}
		
		if( $nRow>=$this->nRowCnt )
		{
			return null ;
		}
	
		$nValidIndex = count($this->arrRecordSet)-1 ;
		while( $nValidIndex<$nRow )
		{
			$this->arrRecordSet[++$nValidIndex] = $this->aPDOStatement->fetch() ;
		}
		 
		return $this->arrRecordSet[$nRow] ;
	}
	
	public function field($sFieldName,$nRow=IRecordSet::currentRow)
	{
		if( $nRow==IRecordSet::currentRow )
		{
			$nRow = $this->nRowIdx ;
		}
		
		if( $nRow>=$this->nRowCnt )
		{
			return null ;
		}
		
		$this->row($nRow) ;
		
		return isset($this->arrRecordSet[$nRow][$sFieldName])?
					$this->arrRecordSet[$nRow][$sFieldName]: null ;
	}
	
	public function iterator() 
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrRecordSet) ;
	}

	public function fieldIterator()
	{
		$arrRow = $this->current() ;
		return new \org\jecat\framework\pattern\iterate\ArrayIterator( $arrRow===null? array(): array_keys($arrRow) ) ;
	}
	
	public function key ()
	{
		return $this->nRowIdx ;
	}
	
	private $arrRecordSet = array() ;
	
	private $nRowCnt = 0 ;
	
	private $nRowIdx = 0 ;
	
	private $aPDOStatement = null ;
}

