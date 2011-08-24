<?php
namespace jc\mvc\model\db\orm ;

use jc\io\IOutputStream;
use jc\lang\Exception;
use jc\lang\Object;

class Association extends Object implements \Serializable
{
	const hasOne = 'hasOne' ;
	const belongsTo = 'belongsTo' ;
	const hasMany = 'hasMany' ;
	const hasAndBelongsToMany = 'hasAndBelongsToMany' ;

	public function __construct($sType,$sModelProperty,Prototype $aFromPrototype,Prototype $aToPrototype,$fromKeys,$toKeys)
	{
		if( !in_array($sType,self::$arrAssociationTypes) )
		{
			throw new Exception(__METHOD__."()参数\$sType只能接受以下值：hasOne, belongsTo, hasMany, hasAndBelongsToMany; 传入值为：%s",$sType) ;
		}
		
		$this->sType = $sType ;
		$this->sModelProperty = $sModelProperty ;
		$this->aFromPrototype = $aFromPrototype ;
		$this->aToPrototype = $aToPrototype ;
		$this->arrFromKeys = (array)$fromKeys ;
		$this->arrToKeys = (array)$toKeys ;
	}
	
	/**
	 * @return Association
	 */
	static function createFromCnf(array $arrCnf,Prototype $aFromPrototype,$sType,$bCheckValid=true,$bInFragment=false)
	{
		if( $bCheckValid )
		{
			$arrCnf = self::assertCnfValid($arrCnf,$sType,true) ;
		}
		
		$aToPrototype = Prototype::createFromCnf($arrCnf['prototype'],true,true,$bInFragment) ;
		
		$aAsso = new self(
			$sType
			, $arrCnf['prop']
			, $aFromPrototype
			, $aToPrototype
			, $arrCnf['fromk'], $arrCnf['tok']
		) ;
		
		if($bInFragment)
		{
			$aToPrototype->setAssociateBy($aAsso) ;
		}
		
		if( $sType==self::hasAndBelongsToMany )
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
	
	public function count()
	{
		return $this->isOneToOne()? 1: 30 ;
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
	
	public function isOneToOne()
	{
		return $this->sType==self::hasOne or $this->sType==self::belongsTo ;
	}
	
	static public function allAssociationTypes()
	{
		return self::$arrAssociationTypes ;
	}
	
	static private $arrAssociationTypes = array(
			self::hasOne
			, self::belongsTo
			, self::hasMany
			, self::hasAndBelongsToMany
	) ;

	static public function assertCnfValid(array $arrAsso,$sType,$bNestingPrototype)
	{
		if( !in_array($sType, Association::allAssociationTypes()) )
		{
			throw new Exception("遇到无效的orm关联类型：%s；orm关联类型必须为：%s",array($sType,implode(", ", Association::allAssociationTypes()))) ;
		}
		
		// 必须属性
		if( empty($arrAsso['prototype']) )
		{
			if( !empty($arrAsso['model']) )
			{
				$arrAsso['prototype'] = $arrAsso['model'] ;
			}
			else
			{
				throw new Exception("orm %s 关联缺少 prototype 属性",$sType) ;
			}
		}
		
		// 递归检查  prototype 值
		if( $bNestingPrototype )
		{
			if( !is_array($arrAsso['prototype']) )
			{
				throw new Exception("orm %s 关联的 prototype属性要求是一个完整的 orm config",$sType) ;			
			}
			
			Prototype::assertCnfValid($arrAsso['prototype']) ;
		}
		
		if($sType==Association::hasAndBelongsToMany)
		{
			if( empty($arrAsso['bridge']) )
			{
				throw new Exception("orm %s(%s) 缺少 bridge 属性",array($sType,$arrAsso['prototype'])) ;
			}
			if( empty($arrAsso['bfromk']) )
			{
				throw new Exception("orm %s(%s) 缺少 bfromk 属性",array($sType,$arrAsso['prototype'])) ;
			}
			if( empty($arrAsso['btok']) )
			{
				throw new Exception("orm %s(%s) 缺少 btok 属性",array($sType,$arrAsso['prototype'])) ;
			}
		}
		
		// 可选属性
		if( empty($arrAsso['prop']) )
		{
			$arrAsso['prop'] = $arrAsso['prototype'] ;
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

	public function serialize ()
	{
		foreach(array(
				'sType',
				'sModelProperty',
				'aFromPrototype',
				'aToPrototype',
				'arrFromKeys',
				'arrToKeys',
				'sBridgeTable',
				'arrBridgeFromKeys',
				'arrBridgeToKeys',
		) as $sPropName)
		{
			$arrData[$sPropName] =& $this->$sPropName ;
		}
		return serialize( $arrData ) ;
	}

	public function unserialize ($sSerialized)
	{
		$arrData = unserialize($sSerialized) ;
				
		foreach(array(
				'sType',
				'sModelProperty',
				'aFromPrototype',
				'aToPrototype',
				'arrFromKeys',
				'arrToKeys',
				'sBridgeTable',
				'arrBridgeFromKeys',
				'arrBridgeToKeys',
		) as $sPropName)
		{
			if( array_key_exists($sPropName, $arrData) )
			{
				$this->$sPropName =& $arrData[$sPropName] ;
			}
		}
	}
	
	// misc
	public function printStruct(IOutputStream $aOutput=null,$nDepth=0)
	{
		if(!$aOutput)
		{
			$aOutput = $this->application()->response()->printer() ;
		}
		
		$aOutput->write( "<pre>\r\n" ) ;
		
		$aOutput->write( str_repeat("\t", $nDepth)."[" ) ;
		$aOutput->write( $this->type()."] " ) ;
		$aOutput->write( $this->modelProperty()."\r\n" ) ;
		
		$this->toPrototype()->printStruct($aOutput,$nDepth) ;
		
		$aOutput->write( "</pre>" ) ;
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