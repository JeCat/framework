<?php
namespace jc\mvc\model\db\orm ;

use jc\lang\Exception;
use jc\lang\Object;

class AssociationPrototype extends Object
{
	const hasOne = 'hasOne' ;
	const belongsTo = 'belongsTo' ;
	const hasMany = 'hasMany' ;
	const hasAndBelongsMany = 'hasAndBelongsMany' ;

	public function __construct($sType,$sModelProperty,ModelPrototype $aFromPrototype,ModelPrototype $aToPrototype,$fromKeys,$toKeys)
	{
		if( !in_array($sType,self::$arrAssociationTypes) )
		{
			throw new Exception(__METHOD__."()参数\$sType只能接受以下值：hasOne, belongsTo, hasMany, hasAndBelongsMany; 传入值为：%s",$sType) ;
		}
		
		$this->sType = $sType ;
		$this->sModelProperty = $sModelProperty ;
		$this->aFromPrototype = $aFromPrototype ;
		$this->aToPrototype = $aToPrototype ;
		$this->arrFromKeys = (array)$fromKeys ;
		$this->arrToKeys = (array)$toKeys ;
	}

	public function setBridge($sBridgeTable = $bridgeFromKeys=null,$bridgeToKeys=null)
	{
		$this->sBridgeTable = $sBridgeTable ;
		$this->arrBridgeFromKeys = (array)$bridgeFromKeys ;
		$this->arrBridgeToKeys = (array)$bridgeToKeys ;
	}
	
	public function type()
	{
		return $this->sType ;
	}

	public function modelProperty()
	{
		return $this->sModelProperty ;
	}

	public function fromPrototype()
	{
		return $this->aFromPrototype ;
	}
	public function toPrototype()
	{
		return $this->aToPrototype ;
	}
	public function fromKeys()
	{
		return $this->arrFromKeys ;
	}
	public function toKeys()
	{
		return $this->arrToKeys ;
	}
	
	public function bridgeFromKeys()
	{
		return $this->arrBridgeFromKeys ;
	}
	public function bridgeToKeys()
	{
		return $this->arrBridgeToKeys ;
	}
	
	static public function allAssociationTypes()
	{
		return self::$arrAssociationTypes ;
	}
	
	static private $arrAssociationTypes = array(
			self::hasOne
			, self::belongsTo
			, self::hasMany
			, self::hasAndBelongsMany
	) ;
	
	private $sType ;
	private $sModelProperty ;
	private $aFromPrototype ;
	private $aToPrototype ;
	
	private $arrFromKeys ;
	private $arrToKeys ;
	
	private $sBridgeTable ;
	private $arrBridgeFromKeys ;
	private $arrBridgeToKeys ;
	
}

?>