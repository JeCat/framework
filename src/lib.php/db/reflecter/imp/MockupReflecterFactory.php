<?php
namespace jc\db\reflecter\imp;

use jc\db\reflecter\AbstractReflecterFactory;

class MockupReflecterFactory extends AbstractReflecterFactory
{
	public function __construct(array $arrMockupStruct)
	{
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
?>
