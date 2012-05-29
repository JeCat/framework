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
			$aModel->addRow($arrPrototype['xpath']) ;
			
			$this->insertRow($aModel, $arrPrototype, $arrDataRow, $bRecursively, $aDB) ;
		}
	}
	
	public function insertRow(Model $aModel,array & $arrPrototype,array & $arrDataRow,$bRecursively=true,DB $aDB=null)
	{
		// 字段/值
		$arrColumns = array() ;
		$arrValues = array() ;
		$arrNewRow = array() ;
			
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

			if( $arrPrototype['xpath'] )
			{
				$sDataNamePrefix = $arrPrototype['xpath'].'.' ;
			}
			else
			{
				$sDataNamePrefix = $arrPrototype['xpath'] ;
			}
			$sDataName = $sDataNamePrefix.$sDataName ;
		
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
		
		
		echo $sSql = "INSERT INTO `{$arrPrototype['table']}` (\r\n\t"
						. implode("\r\n\t, ", $arrColumns)
						. "\r\n) VALUES (\r\n\t"
						. implode("\r\n\t, ", $arrValues)
						. "\r\n) ;\r\n" ;
		
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
		
		// 处理关联表
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

