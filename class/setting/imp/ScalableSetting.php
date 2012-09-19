<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.8
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\setting\imp;

use org\jecat\framework\setting\Setting;

class ScalableSetting extends Setting{
	public function __construct(Setting $aRealSetting){
		$this->aRealSetting = $aRealSetting;
		
		$this->readTightData();
	}
	
	static public function create(Setting $aRealSetting){
		return new self($aRealSetting);
	}
	
	public function valuePri($sKey,$defaultValue=null){
		$sKey = self::formatKey( $sKey );
		$arrExpKey = explode('/',$sKey) ;
		$arr = $this->arrTightData;
		foreach($arrExpKey as $sExpKey){
			if( !is_array( $arr ) ){
				return $defaultValue ;
			}
			if( !isset( $arr[$sExpKey] ) ){
				return $defaultValue ;
			}
			$arr = $arr[$sExpKey] ;
		}
		if( $arr === null ){
			return $defaultValue ;
		}
		if( $arr !== self::SoftLink){
			return $arr ;
		}
		$value = $this->aRealSetting->valuePri($sKey,$defaultValue);
		return $value;
	}
	
	public function setValuePri($sKey,$value){
		$sKey = self::formatKey( $sKey );
		if( self::isSimpleData( $value ) ){
			$arrExpKey = explode('/',$sKey) ;
			$arr = & $this->arrTightData;
			foreach($arrExpKey as $sExpKey){
				if( !is_array( $arr ) ){
					$arr = array() ;
				}
				if( !isset( $arr[$sExpKey] ) ){
					$arr[$sExpKey] = array();
				}
				$arr = &$arr[$sExpKey] ;
			}
			$arr = $value ;
			$this->saveTightData();
		}else{
			$this->setValuePri($sKey,self::SoftLink);
			return $this->aRealSetting->setValuePri($sKey,$value);
		}
	}
	
	public function hasValuePri($sKey){
		$sKey = self::formatKey( $sKey );
		$arrExpKey = explode('/',$sKey) ;
		$arr = $this->arrTightData;
		foreach($arrExpKey as $sExpKey){
			if( !is_array( $arr ) ){
				return false ;
			}
			if( !isset( $arr[$sExpKey] ) ){
				return false ;
			}
			$arr = $arr[$sExpKey] ;
		}
		if( $arr === null ){
			return $false ;
		}
		return true;
		return $this->aRealSetting->hasValuePri($sKey);
	}
	
	public function deleteValuePri($sKey){
		$sKey = self::formatKey( $sKey );
		
		$arrExpKey = explode('/',$sKey) ;
		$arr = & $this->arrTightData;
		foreach($arrExpKey as $sExpKey){
			if( !is_array( $arr ) ){
				return ;
			}
			if( !isset( $arr[$sExpKey] ) ){
				return ;
			}
			$arr = &$arr[$sExpKey] ;
		}
		$arr = null;
		$this->aRealSetting->deleteValue($sKey);
	}
	
	public function separate($sPath){
		return SeparatedSetting::create(
			$this,
			self::formatKey($sPath)
		);
	}
	
	private function saveTightData(){
		$this->aRealSetting->setValue(
			self::TightDataKey,
			$this->arrTightData
		);
	}
	
	private function readTightData(){
		$this->arrTightData = $this->aRealSetting->value(
			self::TightDataKey,
			null
		);
		if( null === $this->arrTightData ){
			$this->arrTightData = array();
			$arrOldTight = $this->aRealSetting->value(
				'tightdata',
				array()
			);
			foreach($arrOldTight as $sKey => $sValue){
				$this->setValue($sKey,$sValue);
			}
		}
	}
	
	static private function isSimpleData($value){
		if( is_int( $value)
				or is_string( $value)
				or is_bool( $value )
				or is_float( $value )
		){
			return true;
		}
		return false;
	}
	
	public function key($sPath,$bAutoCreate=false){
		return $this->aRealSetting->key($sPath,$bAutoCreate);
	}
	
	public function createKey($sPath){
		return $this->key($sPath,true) ;
	}
	
	public function hasKey($sPath){
		return $this->aRealSetting->hasKey($sPath);
	}
	
	public function deleteKey($sDelKey){
		$sKey = self::formatKey( $sDelKey );
		
		$arrExpKey = explode('/',$sKey) ;
		$sLastKey = array_pop($arrExpKey);
		$arr = & $this->arrTightData;
		foreach($arrExpKey as $sExpKey){
			if( !is_array( $arr ) ){
				return ;
			}
			if( !isset( $arr[$sExpKey] ) ){
				return ;
			}
			$arr = &$arr[$sExpKey] ;
		}
		unset($arr[$sLastKey]);
		$this->saveTightData();
		$this->aRealSetting->deleteKey($sKey);
		
	}
	
	const SoftLink = 'SOFT_LINK';
	const TightDataKey = '';
	private $aRealSetting = null;
	private $arrTightData = array();
}
