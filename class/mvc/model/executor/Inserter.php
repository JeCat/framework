<?php
namespace org\jecat\framework\mvc\model\executor ;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\mvc\model\Prototype;
use org\jecat\framework\mvc\model\Model;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class Inserter extends Executor
{	
	public function execute(Model $aModel,array & $arrPrototype,array & $arrDataSheet,$bRecursively=true,DB $aDB=null)
	{
		foreach($arrDataSheet as &$arrDataRow)
		{
			$aModel->addRow(null,$arrPrototype['xpath']) ;
			
			$this->insertRow($aModel, $arrPrototype, $arrDataRow, $bRecursively, $aDB) ;
		}
	}
	
	public function insertRow(Model $aModel,array & $arrPrototype,array & $arrDataRow,$bRecursively=true,DB $aDB=null)
	{
	    
		// 字段/值
		$arrColumns = array() ;
		$arrValues = array() ;
		$arrNewRow = array() ;

		if( $arrPrototype['xpath'] )
		{
			$sDataNamePrefix = $arrPrototype['xpath'].'.' ;
		}
		else
		{
			$sDataNamePrefix = $arrPrototype['xpath'] ;
		}
		
		$sBeAssociatedPrefix = substr($arrPrototype['xpath'],0,-strlen($arrPrototype['name'])) ;
		
		foreach( $arrPrototype['columns'] as $sColumn )
		{
			if( strlen($sColumn)>=2 and substr($sColumn,0,2)==='e:' )
			{
				$sColumn = substr($sColumn,2) ;
				$bExpression = true ;
			}
			else
			{
				$bExpression = false ;
			}

			$sDataName = $sDataNamePrefix.$sColumn ;
		
			if( array_key_exists($sDataName,$arrDataRow) )
			{
				$arrColumns[] = '`'.$sColumn.'`' ;
		
				if($bExpression)
				{
					$arrValues[] = $arrDataRow[$sDataName] ;
				}
				else
				{
					$arrValues[] = self::escValue($arrDataRow[$sDataName]) ;
					$arrNewRow[$sDataName] = $arrDataRow[$sDataName] ;
				}
			}
		}

		
		// 设置根据外键，从左表拿到数据
		if( !empty($arrPrototype['assoc']) )
		{
			// 不在这里处理 多对多关联
			if($arrPrototype['assoc']!==Prototype::hasAndBelongsToMany)
			{
				foreach($arrPrototype['toKeys'] as $nIdx=>$sToKeys)
				{
					$value = $aModel->data( $sBeAssociatedPrefix.$arrPrototype['fromKeys'][$nIdx]) ;
					$arrValues[] = self::escValue($value) ;
					$arrNewRow[$sDataNamePrefix.$sToKeys] = $value;

					$arrColumns[] = '`'.$sToKeys.'`' ;
				}
			}
		}

		// 执行 insert
		if( !empty($arrColumns) )
		{
			$sSql = $this->makeSql($arrPrototype['table'],$arrColumns,$arrValues) ;
			
			$aDB->execute($sSql) ;
			
			// 新插入的主键值
			if( $arrPrototype['devicePrimaryKey'] )
			{
				$nNewPrimaryValue = $aDB->lastInsertId() ;
				if($nNewPrimaryValue!==null)
				{
					$arrNewRow[$sDataNamePrefix.$arrPrototype['devicePrimaryKey']] = $nNewPrimaryValue ;
				}
			}
	
			// 将新建的数据设置到系统中
			if(!empty($arrNewRow))
			{
				$aModel->setRow($arrNewRow,$arrPrototype['xpath']) ;
			}
		}
		
		// 自动写入桥接表（在这里处理多对多关联）
		// 根据左右两个表的值，插入中间表
		if( !empty($arrPrototype['assoc']) and $arrPrototype['assoc']===Prototype::hasAndBelongsToMany )
		{
			$this->insertBridgeRow($aModel,$arrPrototype,$sBeAssociatedPrefix,$sDataNamePrefix,$aDB) ;
		}
		
		// 处理下级关联
		if( $bRecursively and !empty($arrPrototype['associations']) )
		{
			foreach($arrPrototype['associations'] as & $arrAssoc)
			{
				// 单属关联
				if( $arrAssoc['assoc']&Prototype::oneToOne )
				{
					$this->insertRow($aModel,$arrAssoc,$arrDataRow,$bRecursively,$aDB) ;
				}
				
				// 多属关联
				else
				{
					if( !empty($arrDataRow[$arrAssoc['name']]) )
					{
						$this->execute($aModel,$arrAssoc,$arrDataRow[$arrAssoc['name']],$bRecursively,$aDB) ;
					}
				}
			}
		}
	}
	
	/**
	 * 根据左右两个表的值，插入中间表
	 */
	private function insertBridgeRow(Model $aModel,array & $arrAssoc,$sFromPrefix,$sToPrefix,$aDB)
	{
		// 字段/值
		$arrColumns = array() ;
		$arrValues = array() ;

		foreach($arrAssoc['fromKeys'] as $nIdx=>$sFromKey)
		{
			$arrValues[] = self::escValue( $aModel->data($sFromPrefix.$sFromKey) ) ;
			$arrColumns[] = $arrAssoc['toBridgeKeys'][$nIdx] ;
		}
		foreach($arrAssoc['toKeys'] as $nIdx=>$sToKey)
		{
			$arrValues[] = self::escValue( $aModel->data($sToPrefix.$sToKey) ) ;
			$arrColumns[] = $arrAssoc['fromBridgeKeys'][$nIdx] ;
		}

		echo $sSql = $this->makeSql($arrAssoc['bridge'],$arrColumns,$arrValues) ;
		
		$aDB->execute($sSql) ;
	}
	
	private function makeSql($sTable,&$arrColumns,&$arrValues)
	{
		return "INSERT INTO `{$sTable}` (\r\n\t"
				. implode("\r\n\t, ", $arrColumns)
				. "\r\n) VALUES (\r\n\t"
				. implode("\r\n\t, ", $arrValues)
				. "\r\n) ;\r\n" ;
	}
	
	static function escValue(& $value)
	{
		if( is_int($value) or is_float($value) or is_double($value) or is_bool($value) )
		{
			return $value ;
		}
		else if( is_string($value) )
		{
			return "'".addslashes($value)."'" ;
		}
		else if($value===null)
		{
			return 'NULL' ;
		}
	}
}

