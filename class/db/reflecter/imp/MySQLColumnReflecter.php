<?php
namespace org\jecat\framework\db\reflecter\imp;

use org\jecat\framework\db\reflecter\AbStractColumnReflecter;

class MySQLColumnReflecter extends AbStractColumnReflecter
{
	function __construct($aDBReflecterFactory, $sTable, $sColumn, $sDBName = null)
	{
		$aDB = $aDBReflecterFactory->db();
		
		if( !$aResult=$aDB->query($this->makeColumnSql($sTable, $sColumn, $sDBName)) or $aResult->rowCount()==0 )
		{
			$this->bIsExist = false ;
			return ;
		}
		
		$arrColumnInfo = $aResult->fetch(\PDO::FETCH_ASSOC) ;
		$this->sType = strtoupper( $arrColumnInfo['Type'] ) ;
		if( preg_match('/^(\w+)\((\d+)\)$/',$this->sType,$arrRes) )
		{
			$this->sType = $arrRes[1] ;
			$this->nLength = (int)$arrRes[2] ;
		}
		
		$this->bIsInt = in_array ( $this->sType, self::$arrInt ) ;
		$this->bIsBool = in_array ( $this->sType, self::$arrBool ) ;
		$this->bIsFloat = in_array ( $this->sType, self::$arrFloat ) ;
		$this->bIsString = in_array ( $this->sType, self::$arrString ) ;
		
		if( $arrColumnInfo['Null'] !== 'NO' )
		{
			$this->bAllowNull = true;
		}
		
		$sDefaultValue = $arrColumnInfo['Default'] ;
		if ($sDefaultValue !== 'NULL')
		{
			$this->defaultValue = $sDefaultValue;
		}

		if ( $arrColumnInfo['Extra'] == 'auto_increment')
		{
			$this->bIsAutoIncrement = true;
		}
		
		$this->sName = $sColumn;
	}
	
	private function makeColumnSql($sTable, $sColumn, $sDBName)
	{
		return "SHOW columns FROM `" . $sDBName . "`.`" . $sTable . "` " . "WHERE `Field`='" . $sColumn . "'";
	}
	
	public function type()
	{
		return $this->sType;
	}
	
	public function isString()
	{
		return $this->bIsString;
	}
	
	public function isBool()
	{
		return $this->bIsBool;
	}
	
	public function isInts()
	{
		return $this->bIsInt;
	}
	
	public function isFloat()
	{
		return $this->bIsFloat;
	}
	
	public function length()
	{
		return $this->nLength;
	}
	
	public function allowNull()
	{
		return $this->bAllowNull;
	}
	
	public function defaultValue()
	{
		return $this->defaultValue;
	}
	
	public function comment()
	{
		return $this->sComment;
	}
	public function isAutoIncrement()
	{
		return $this->bIsAutoIncrement;
	}
	
	public function name()
	{
		return $this->sName;
	}
	
	public function isExist()
	{
		return $this->bIsExist;
	}
	
	private $sType;
	
	private $bIsString = false;
	
	private $bIsBool = false;
	
	private $bIsInt = false;
	
	private $bIsFloat = false;
	
	private $nLength = null;
	
	private $bAllowNull = false;
	
	private $defaultValue = null;
	
	private $sComment;
	
	private $bIsAutoIncrement = false;
	
	private static $arrInt = array ('INT', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'SERIAL', 'BIT' );
	private static $arrFloat = array ('DECIMAL', 'FLOAT', 'DOUBLE', 'REAL' );
	private static $arrString = array ('VARCHAR', 'TEXT', 'DATE', 'DATETIME', 'TIMESTAMP', 'TIME', 'YEAR', 'CHAR', 'LONGTEXT', 'TINYTEXT', 'MEDIUMTEXT', 'BINARY', 'VARBINARY', 'TINYBLOB', 'MEDIUMBLOB', 'BLOB', 'LONGBLOB', 'ENUM', 'SET' );
	private static $arrBool = array ('BIT', 'BOOLEAN' );
	
	private $sName;
	
	private $bIsExist = true;
}
?>