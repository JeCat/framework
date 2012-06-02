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
namespace org\jecat\framework\db\reflecter\imp;

use org\jecat\framework\db\reflecter\AbstractReflecterFactory;
use org\jecat\framework\db\reflecter\AbStractTableReflecter;

class MySQLTableReflecter extends AbStractTableReflecter
{
	function __construct(AbstractReflecterFactory $aDBReflecterFactory, $sTable, $sDBName = null) 
	{
		$aDB = $aDBReflecterFactory->db();
		
		if( !$aResult=$aDB->query($this->makeGetColumnsSql($sTable,$sDBName)) or $aResult->rowCount()==0 )
		{
			$this->bIsExist = false;
			return ;
		}
		
		// 反射字段
		$arrColumnNames = $aResult->fetchAll(\PDO::FETCH_ASSOC) ;
		foreach ( $arrColumnNames as $arrColumn )
		{
			$this->arrColumnNames [] = $arrColumn['Field'] ;
		}
		
		if($aResult=$aDB->query($this->makeTableStatusSql($sTable)))
		{
			$arrRow = $aResult->fetch(\PDO::FETCH_ASSOC) ;
			$this->sComment = $arrRow['Comment'] ;
			$this->nAutoINcrement = $arrRow['Auto_increment'] ;
		}
		
		// 反射所有索引
		$this->arrIndexes = MySQLIndexReflecter::reflectTableIndexes($sTable,$sDBName,$aDB) ;
		
		// 主键
		if(isset($this->arrIndexes['PRIMARY']))
		{
			$this->sPrimaryName = reset($this->arrIndexes['PRIMARY']->columnNames()) ;
		}
		
		$this->sName = $sTable;
	}
	
	private function makeGetColumnsSql($sTable, $sDBName)
	{
		return "show columns from `" . $sDBName . "`.`" . $sTable . "`";
	}
	
	private function makeTableStatusSql($sTable)
	{
		return "show table status where name ='" . $sTable . "'";
	}
	
	public function primaryName()
	{
		return $this->sPrimaryName;
	}
	
	public function autoIncrement()
	{
		return $this->nAutoINcrement;
	}
	
	public function comment()
	{
		return $this->sComment;
	}
	
	public function columns()
	{
		return $this->arrColumnNames ;
	}
	
	public function name()
	{
		return $this->sName;
	}
	
	public function isExist()
	{
		return $this->bIsExist;
	}
	
	public function indexIterator()
	{
		return new \ArrayIterator($this->arrIndexes) ;
	}
	public function indexNameIterator()
	{
		return new \ArrayIterator(array_keys($this->arrIndexes)) ;
	}
	public function indexReflecter($sIndexName)
	{
		return $this->arrIndexes[$sIndexName] ;
	}
	
	private $sPrimaryName = null;
	
	private $nAutoINcrement;
	
	private $sComment;
	
	private $arrColumnNames;
	
	private $arrIndexes;
	
	private $sName;
	
	private $bIsExist=true;
}


