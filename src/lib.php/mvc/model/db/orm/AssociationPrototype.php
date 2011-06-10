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
	
	/**
	 * @return AssociationPrototype
	 */
	static function createFromCnf(array $arrCnf,ModelPrototype $aFromPrototype,$sType,$bCheckValid=true)
	{
		if( $bCheckValid )
		{
			$arrCnf = self::assertCnfValid($arrCnf,$sType,true) ;
		}
		
		$aAsso = new self(
			$sType
			, $arrCnf['prop']
			, $aFromPrototype
			, ModelPrototype::createFromCnf($arrCnf['model'])
			, $arrCnf['fromk'], $arrCnf['tok']
			// , $arrCnf['bfromk']?:null, $arrCnf['btok']?:null
		) ;
		
		if( $sType==self::hasAndBelongsMany )
		{
			$aAsso->setBridge($arrCnf['bridge'],$arrCnf['bfromk'],$arrCnf['btok']) ;			
		}
		
		return $aAsso ;
	}

	public function setBridge($sBridgeTable,$bridgeFromKeys=null,$bridgeToKeys=null)
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

	public function bridgeTableName()
	{
		return $this->sBridgeTable ;
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

	static public function assertCnfValid(array $arrAsso,$sType,$bNestingModel)
	{
		if( !in_array($sType, AssociationPrototype::allAssociationTypes()) )
		{
			throw new Exception("遇到无效的orm关联类型：%s；orm关联类型必须为：%s",array($sType,implode(", ", AssociationPrototype::allAssociationTypes()))) ;
		}

		// 必须属性
		if( empty($arrAsso['model']) )
		{
			throw new Exception("orm %s 关联缺少 name 属性",$sType) ;
		}
		
		// 递归检查  model 值
		if( $bNestingModel )
		{
			if( !is_array($arrAsso['model']) )
			{
				throw new Exception("orm %s 关联的 model属性要求是一个完整的 orm config",$sType) ;			
			}
			
			ModelPrototype::assertCnfValid($arrAsso['model']) ;
		}
		
		if($sType==AssociationPrototype::hasAndBelongsMany)
		{
			if( empty($arrAsso['bridge']) )
			{
				throw new Exception("orm %s(%s) 缺少 bridge 属性",array($sType,$arrAsso['model'])) ;
			}
			if( empty($arrAsso['bfromk']) )
			{
				throw new Exception("orm %s(%s) 缺少 bfromk 属性",array($sType,$arrAsso['model'])) ;
			}
			if( empty($arrAsso['btok']) )
			{
				throw new Exception("orm %s(%s) 缺少 btok 属性",array($sType,$arrAsso['model'])) ;
			}
		}
		
		// 可选属性
		if( empty($arrAsso['prop']) )
		{
			$arrAsso['prop'] = $arrAsso['model'] ;
		}

		// 统一格式
		foreach( array('fromk','tok','bfromk','btok') as $sName )
		{
			if(!empty($arrAsso[$sName]))
			{
				$arrAsso[$sName] = (array) $arrAsso[$sName] ;
			}
		}
		
		return $arrAsso ;
	}

	public function setFromPrototype($aFromPrototype)
	{
		$this->aFromPrototype = $aFromPrototype ;
	}
	public function setToPrototype($aToPrototype)
	{
		$this->aToPrototype = $aToPrototype ;
	}
	
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