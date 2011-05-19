<?php
namespace jc\mvc\model\db\orm ;

use jc\lang\Exception;
use jc\lang\Object;

class RelationPrototype extends Object
{
	const hasOne = 'hasOne' ;
	const belongsTo = 'belongsTo' ;
	const hasMany = 'hasMany' ;
	const hasAndBelongsMany = 'hasAndBelongsMany' ;

	public function __construct($sType,$sModelProperty,ModelPrototype $aFromPrototype,ModelPrototype $aToPrototype,$fromKeys,$toKeys,$fromBridgeKeys=null,$toBridgeKeys=null)
	{
		if( !in_array($sType,self::$arrRelationTypes) )
		{
			throw new Exception(__METHOD__."()参数\$sType只能接受以下值：hasOne, belongsTo, hasMany, hasAndBelongsMany; 传入值为：%s",$sType) ;
		}
		
		$this->sType = $sType ;
		$this->sModelProperty = $sModelProperty ;
		$this->aFromPrototype = $aFromPrototype ;
		$this->aToPrototype = $aToPrototype ;
		$this->arrFromKeys = (array)$fromKeys ;
		$this->arrToKeys = (array)$toKeys ;
		$this->arrFromBridgeKeys = (array)$fromBridgeKeys ;
		$this->arrToBridgeKeys = (array)$toBridgeKeys ;
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
	
	public function fromBridgeKeys()
	{
		return $this->arrFromBridgeKeys ;
	}
	public function toBridgeBridgeKeys()
	{
		return $this->arrToKeys ;
	}
	
	static private $arrRelationTypes = array(
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
	private $arrFromBridgeKeys ;
	private $arrToBridgeKeys ;
	
}

?>