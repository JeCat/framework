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
namespace org\jecat\framework\mvc\model\db ;

use org\jecat\framework\pattern\iterate\IReversableIterator;

class ModelListIterator implements IReversableIterator
{
	public function __construct(ModelList $aModelList,array $arrModelIndexes=null,$bShareChild=false)
	{
		$this->aModelList = $aModelList ;
		if($arrModelIndexes===null)
		{
			$nModelCount = $this->aModelList->childrenCount() ;
			$this->arrModelIndexes = $nModelCount>0? range( 0, $nModelCount-1 ): array() ;
		}
		else
		{
			$this->arrModelIndexes = $arrModelIndexes ;
		}
		$this->bShareChild = $bShareChild ;
	}
	
	public function current ()
	{
		$nIndex = current($this->arrModelIndexes) ;
		return $this->aModelList->child($nIndex,$this->bShareChild) ;
	}
	
	public function next ()
	{
		next($this->arrModelIndexes) ;
	}
	
	public function key ()
	{
		return key($this->arrModelIndexes) ;
	}
	
	public function valid ()
	{
		return current($this->arrModelIndexes)!==false ;
	}
	
	public function rewind ()
	{
		reset($this->arrModelIndexes) ;
	}
	
	// IReversableIterator::prev
	public function prev(){
		prev($this->arrModelIndexes);
	}
	
	// IReversableIterator::last
	public function last(){
		end($this->arrModelIndexes);
	}
	
	public function setModelIndexes($arrModelIndexes)
	{
		$this->arrModelIndexes = $arrModelIndexes ;
		reset($this->arrModelIndexes) ;
	}
	
	private $arrModelIndexes ;
	
	private $bShareChild ;
}




