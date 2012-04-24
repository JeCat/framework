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
namespace org\jecat\framework\db\reflecter\imp;

use org\jecat\framework\db\DB;
use org\jecat\framework\db\reflecter\AbstractReflecterFactory;

class MockupReflecterFactory extends AbstractReflecterFactory
{
	public function __construct(DB $aDB, array $arrMockupStruct)
	{
		parent::__construct($aDB) ;
		$this->arrMockupStruct = $arrMockupStruct ;
	}
	
	public function createDBReflecter($sDBName)
	{
		$aReflecter = new MockupDBReflecter ( $this, $sDBName );
			
		if( isset($this->arrMockupStruct[$sDBName]) )
		{
			$aReflecter->bIsExist = true ;
			$aReflecter->arrTableNames = array_keys($this->arrMockupStruct[$sDBName]) ;
		}
		
		return $aReflecter ;
	}
	
	public function createTableReflecter($sTable, $sDBName = null)
	{
		if(!$sDBName)
		{
			$sDBName = $this->currentDBName() ;
		}
		
		$aReflecter = new MockupTableReflecter ( $this, $sTable, $sDBName );
		
	
		if( $sDBName and isset($this->arrMockupStruct[$sDBName][$sTable]) )
		{
			$aReflecter->bIsExist = true ;
			$aReflecter->arrMetainfo = $this->arrMockupStruct[$sDBName][$sTable] ;
		}
		
		return $aReflecter ;
	}
	
	public function createColumnReflecter($sTable, $sColumn, $sDBName = null)
	{
		if(!$sDBName)
		{
			$sDBName = $this->currentDBName() ;
		}
		
		$aReflecter = new MockupColumnReflecter ( $this, $sTable, $sDBName );
		
		if( $sDBName and isset($this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn]) )
		{
			$aReflecter->bIsExist = true ;
			$aReflecter->arrMetainfo = $this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn] ;
			}
		
		return $aReflecter ;
	}
	
	public function createIndexReflecter($sTable, $sIndexName, $sDBName = null)
	{
		if(!$sDBName)
		{
			$sDBName = $this->currentDBName() ;
		}
		
		$aReflecter = new MockupIndexReflecter ( $this, $sTable, $sIndexName , $sDBName);
	
		if( $sDBName and isset($this->arrMockupStruct[$sDBName][$sTable]['indexes'][$sIndexName]) )
		{
			$aReflecter->bIsExist = true ;
			$aReflecter->arrMetainfo = $this->arrMockupStruct[$sDBName][$sTable]['indexes'][$sIndexName];
		}
		
		return $aReflecter ;
	}
	
	public function currentDBName()
	{
		if( !$this->sCurrentDBName )
		{
			$this->sCurrentDBName = key($this->arrMockupStruct) ;
		}
		return $this->sCurrentDBName ;
	}
	
	
//	array(
//	  	'db1' => array(
//	  		'table1' => array(
//	  			'primaryName' => 'index1',
//	  			'autoIncrement' => 0 ,
//	  			'comment' => 'xxx' ,
//	  
//	  			'columns' => array(
//	  				'column1' => array(
//	  					'type' => 'int' ,
//	  					'length' => 10 ,
//	  					'allowNull' => true ,
//	  					'defaultValue' => 0 ,
//	  					'comment' => 'xxxx' ,
//	  					'isAutoIncrement' => true ,
//	  				),
//	  				'column2' => array(
//	  					'type' => 'int' ,
//	  					'length' => 10 ,
//	  					'allowNull' => true ,
//	  					'defaultValue' => 0 ,
//	  					'comment' => 'xxxx' ,
//	  					'isAutoIncrement' => true ,
//	  				),
//	  				'column3' => array(
//	  					'type' => 'int' ,
//	  					'length' => 10 ,
//	  					'allowNull' => true ,
//	  					'defaultValue' => 0 ,
//	  					'comment' => 'xxxx' ,
//	  					'isAutoIncrement' => true ,
//	  				),
//	  			) ,
//	  
//	  			'indexes' => array(
//	  				'index1' => array(
//	  					'columns' => array('column1'),
//						'isPrimary' => true,
//						'isUnique' => true,
//						'isFullText' => false,
//	  				),
//	  				'index2' => array(
//	  					'columns' => array('column1','column2')
//						'isPrimary' => false,
//						'isUnique' => true,
//						'isFullText' => false,
//	  				),
//	  				'index3' => array(
//	  					'columns' => array('column2','column3')
//						'isPrimary' => false,
//						'isUnique' => true,
//						'isFullText' => false,
//	  				),
//	  			) ,
//	  		)
//	  	)
//	  );
	
	
	public $arrMockupStruct = array();
	
	public $sCurrentDBName ;
}

