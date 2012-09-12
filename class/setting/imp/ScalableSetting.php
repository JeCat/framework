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
	
	public function value($sKey,$defaultValue=null){
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
		$value = $this->aRealSetting->value($sKey,$defaultValue);
		if( self::isSimpleData($value) ){
			$this->aRealSetting->deleteValue($sKey);
			$this->setValue( $sKey , $value );
		}
		return $value;
	}
	
	public function setValue($sKey,$value){
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
			$this->setValue($sKey,self::SoftLink);
			return $this->aRealSetting->setValue($sKey,$value);
		}
	}
	
	public function hasValue($sKey){
		$sKey = self::formatKey( $sKey );
		if( isset ( $this->arrTightData[$sKey] ) ){
			return true;
		}
		return $this->aRealSetting->hasValue($sKey);
	}
	
	public function deleteValue($sKey){
		$sKey = self::formatKey( $sKey );
		if( isset( $this->arrTightData[$sKey] ) ){
			unset( $this->arrTightData[$sKey] );
			$this->saveTightData();
		}else{
			$this->aRealSetting->deleteValue($sKey);
		}
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
			array()
		);
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
		trigger_error('正在访问一个过时的方法：'.__METHOD__,E_USER_DEPRECATED ) ;
		return $this->aRealSetting->key($sPath,$bAutoCreate);
	}
	
	public function createKey($sPath){
		trigger_error('正在访问一个过时的方法：'.__METHOD__,E_USER_DEPRECATED ) ;
		return $this->key($sPath,true) ;
	}
	
	public function hasKey($sPath){
		trigger_error('正在访问一个过时的方法：'.__METHOD__,E_USER_DEPRECATED ) ;
		return $this->aRealSetting->hasKey($sPath);
	}
	
	public function deleteKey($sDelKey){
		$sDelKey = self::formatKey( $sDelKey );
		
		foreach($this->arrTightData as $sKey=>$value){
			if( substr( $sKey , 0 , strlen($sDelKey) ) == $sDelKey ){
				$this->deleteValue( $sKey );
			}
		}
		
		$this->aRealSetting->deleteKey($sDelKey);
	}
	
	const SoftLink = 'SOFT_LINK';
	const TightDataKey = '';
	private $aRealSetting = null;
	private $arrTightData = array();
}
