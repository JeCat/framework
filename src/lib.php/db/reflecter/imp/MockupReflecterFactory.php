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
			$aReflecter->sPrimaryName = $this->arrMockupStruct[$sDBName][$sTable]['primaryName'];
			$aReflecter->nAutoIncrement = $this->arrMockupStruct[$sDBName][$sTable]['autoIncrement'];
			$aReflecter->sComment = $this->arrMockupStruct[$sDBName][$sTable]['comment'];
			$aReflecter->arrColumnNames = array_keys($this->arrMockupStruct[$sDBName][$sTable]['columns']);
		}
		
		return $aReflecter ;
	}
	
	public function createColumnReflecter($sTable, $sColumn, $sDBName = null)
	{
		if(!$sDBName)
		{
			$sDBName = $this->currentDBName() ;
		}
		
		$aReflecter = new MockupTableReflecter ( $this, $sTable, $sDBName );
		
	
		if( $sDBName and isset($this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn]) )
		{
			$aReflecter->bIsExist = true ;
			$aReflecter->bIsAllowNull = $this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn]['allowNull'] ;
			$aReflecter->nLength = $this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn]['length'] ;
			$aReflecter->sType = $this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn]['type'] ;
			$aReflecter->sDefaultValue = $this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn]['defaultValue'] ;
			$aReflecter->sComment = $this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn]['comment'] ;
			$aReflecter->bIsAutoIncrement = $this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn]['isAutoIncrement'] ;
			$aReflecter->arrMetainfo = $this->arrMockupStruct[$sDBName][$sTable] ;
			
			$aReflecter->bIsString = $this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn]['type'] === 'string' ? true:false;
			$aReflecter->bIsInt = $this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn]['type'] === 'int' ? true:false;
			$aReflecter->bIsBool = $this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn]['type'] === 'bool' ? true:false;
			$aReflecter->bIsFloat = $this->arrMockupStruct[$sDBName][$sTable]['columns'][$sColumn]['type'] === 'float' ? true:false;
		}
		
		return $aReflecter ;
	}
	
	public function createIndexReflecter($sTable, $sIndexName, $sDBName = null)
	{
		if(!$sDBName)
		{
			$sDBName = $this->currentDBName() ;
		}
		
		$aReflecter = new MySQLIndexReflecter ( $this, $sTable, $sIndexName , $sDBName);
	
		if( $sDBName and isset($this->arrMockupStruct[$sDBName][$sTable]['indexes'][$sIndexName]) )
		{
			$aReflecter->bIsExist = true ;
			$aReflecter->arrColumnNames = $this->arrMockupStruct[$sDBName][$sTable]['indexes'][$sIndexName]['columns'] ;
			$aReflecter->bIsPrimary = $this->arrMockupStruct[$sDBName][$sTable]['indexes'][$sIndexName]['isPrimary'] ;
			$aReflecter->bIsUnique = $this->arrMockupStruct[$sDBName][$sTable]['indexes'][$sIndexName]['isUnique'] ;
			$aReflecter->bIsFullText = $this->arrMockupStruct[$sDBName][$sTable]['indexes'][$sIndexName]['isFullText'] ;
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
