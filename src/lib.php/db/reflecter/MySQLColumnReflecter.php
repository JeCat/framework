<?php
namespace jc\db\reflecter;

class MySQLColumnReflecter extends AbStractColumnReflecter
{
	function __construct($aDBReflecterFactory, $sTable, $sColumn, $sDBName = null)
	{
		$aDB = $aDBReflecterFactory->db();
		$aIterColumn = $aDB->query ( $this->makeColumnSql($sTable, $sColumn, $sDBName) );
		
		if($aIterColumn->rowCount() == 0)
		{
			$this->bIsExist = false;
			return ;
		}
		
		$sTypeAndLength = $aIterColumn->field ( 'Type', 0 );
		
		if ($nBracket = stripos ( $sTypeAndLength, '(' ))
		{
			$this->sType = substr ( $sTypeAndLength, 0, $nBracket - 1 );
			$this->nLength = ( int ) substr ( $sTypeAndLength, $nBracket + 1, strlen ( $sTypeAndLength ) - 1 );
		}
		else
		{
			$this->sType = $sTypeAndLength;
		}
		
		if (in_array ( $this->sType, $this->arrInt ))
		{
			$this->bIsInt = true;
		}
		if (in_array ( $this->sType, $this->arrBool ))
		{
			$this->bIsBool = true;
		}
		if (in_array ( $this->sType, $this->arrFloat ))
		{
			$this->bIsFloat = true;
		}
		if (in_array ( $this->sType, $this->arrString ))
		{
			$this->bIsString = true;
		}
		
		if ($aIterColumn->field ( 'Null', 0 ) !== 'NO')
		{
			$this->bAllowNull = true;
		}
		
		$sDefaultValue = $aIterColumn->field ( 'Default', 0 );
		if ($sDefaultValue !== 'NULL')
		{
			$this->defaultValue = $sDefaultValue;
		}
		
		if ($aIterColumn->field ( 'Extra', 0 ) !== 'auto_increment')
		{
			$this->bIsAutoIncrement = true;
		}
		
		$this->sName = $sColumn;
	
		//$this->sComment = $aIterColumn->field ( 'COLUMN_COMMENT', 0 );
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
		return $this->bISExist;
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
	
	private $arrInt = array ('INT', 'TINYINT', 'SMALLINT', 'MEDIUMINT', 'BIGINT', 'SERIAL', 'BIT' );
	private $arrFloat = array ('DECIMAL', 'FLOAT', 'DOUBLE', 'REAL' );
	private $arrString = array ('VARCHAR', 'TEXT', 'DATE', 'DATETIME', 'TIMESTAMP', 'TIME', 'YEAR', 'CHAR', 'LONGTEXT', 'TINYTEXT', 'MEDIUMTEXT', 'BINARY', 'VARBINARY', 'TINYBLOB', 'MEDIUMBLOB', 'BLOB', 'LONGBLOB', 'ENUM', 'SET' );
	private $arrBool = array ('BIT', 'BOOLEAN' );
	
	private $sName;
	
	private $bIsExist = true;
}
?>