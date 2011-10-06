<?php

namespace jc\mvc\model\db\orm;

use jc\lang\Exception;

class Association{
    const hasOne = 1;
    const belongsTo = 2;
    const hasMany = 3;
    const hasAndBelongsTo = 4;
    const oneToOne= 5;
    const total = 6;
    
    // public function
    public function __construct($nType,$aFromPrototype,$aToPrototype,$fromKeys= null,$toKeys=null,$sName = '',$BridgeTable=null,$toBridgeKeys=null,$fromBridgeKeys=null){
        $this->nType=$nType;
        $this->aFromPrototype = $aFromPrototype;
        $this->aToPrototype = $aToPrototype;
        $this->setName( $sName );
        if($fromKeys !== null ){
            $this->setFromKeys($fromKeys);
        }else{
            $this->setFromKeys( $this->aFromPrototype->keys() );
        }
        if($toKeys !== null){
            $this->setToKeys($toKeys);
        }else{
            $this->setToKeys( $this->aToPrototype->keys() );
        }
        if($this->type() === self::hasAndBelongsTo){
            $this->setBridge($BridgeTable,$toBridgeKeys,$fromBridgeKeys);
        }
    }
    public function setName($sName){
        if($sName === '' || $sName === null || !is_string($sName)){
            $this->sName = $this->aToPrototype->name();
        }else{
            $this->sName=$sName;
        }
    }
    public function setFromKeys($fromKeys){
        if(is_string($fromKeys)){
            $fromKeys = (array)$fromKeys;
        }
        if(is_array($fromKeys)){
            $this->arrFromKeys = $fromKeys;
        }else{
            throw new Exception('函数 Association::setFromKeys() 的参数 fromkeys 既不是数组也不是字符串');
        }
    }
    public function setToKeys($toKeys){
        if(is_string($toKeys)){
            $toKeys = (array)$toKeys;
        }
        if(is_array($toKeys)){
            $this->arrToKeys = $toKeys;
        }else{
            throw new Exception('函数 Association::setToKeys() 的参数 tokeys 既不是数组也不是字符串');
        }
    }
    /*!
        \brief 设置连桥表的原型、左连键和右连键
        
        \a $BridgeTable，连桥表。接受字符串或Prototype对象，表示表名或原型。
        \a $toBridgeKeys 和 \a $fromBridgeKeys 接受字符串或数组。
        
        \sa setToBridgeKeys() , setFromBridgeKeys() 
    */
    public function setBridge($BridgeTable,$toBridgeKeys,$fromBridgeKeys){
        if($this->nType != self::hasAndBelongsTo){
            throw new Exception('函数 Association::setBridge() 只有在 nType 是 hasAndBelongsTo时才可以被调用');
        }
        if($BridgeTable instanceof Prototype){
            $this->aBridgeTablePrototype=$BridgeTable;
        }else if(is_string($BridgeTable)){
            $this->aBridgeTablePrototype=Prototype::create($BridgeTable);
        }else{
            throw new Exception('函数 Association::setBridge() 的参数 BridgeTable 既不是Prototype对象也不是字符串');
        }
        if($toBridgeKeys === null ){
            if($this->aFromPrototype === null){
                throw new Exception('函数 Association::setBridge() 的参数 toBridgeKeys 是 null，并且无法反射得到 this->aFromPrototype 的键。原因： this->aFromPrototype 是 null');
            }else{
                $toBridgeKeys = $this->aFromPrototype ->keys();
                if($toBridgeKeys === null){
                    throw new Exception('函数 Association::setBridge() 的参数 toBridgeKeys 是 null，并且无法反射得到 this->aFromPrototype 的键。原因： this->aFromPrototype->keys() 的返回值是 null');
                }else{
                    $this->setToBridgeKeys($toBridgeKeys);
                }
            }
        }else{
            $this->setToBridgeKeys($toBridgeKeys);
        }
        if($fromBridgeKeys === null ){
            if($aToProfromtype === null){
                throw new Exception('函数 Association::setBridge() 的参数 fromBridgeKeys 是 null，并且无法反射得到 this->aToPrototype 的键。原因： this->aToPrototype 是 null');
            }else{
                $fromBridgeKeys = $this->aToPrototype ->keys();
                if($fromBridgeKeys === null){
                    throw new Exception('函数 Association::setBridge() 的参数 fromBridgeKeys 是 null，并且无法反射得到 this->aToPrototype 的键。原因： this->aToPrototype->keys() 的返回值是 null');
                }else{
                    $this->setFromBridgeKeys($fromBridgeKeys);
                }
            }
        }else{
            $this->setFromBridgeKeys($fromBridgeKeys);
        }
    }
    public function setToBridgeKeys($toBridgeKeys){
        if(is_string($toBridgeKeys)){
            $toBridgeKeys = (array)$toBridgeKeys;
        }
        if(is_array($toBridgeKeys)){
            $this->arrBridgeKeys = $toBridgeKeys;
        }else{
            throw new Exception('函数 Association::setToBridgeKeys() 的参数 toBridgeKeys 既不是数组也不是字符串');
        }
    }
    public function setFromBridgeKeys($fromBridgeKeys){
        if(is_string($fromBridgeKeys)){
            $fromBridgeKeys = (array)$fromBridgeKeys;
        }
        if(is_array($fromBridgeKeys)){
            $this->arrBridgeKeys = $fromBridgeKeys;
        }else{
            throw new Exception('函数 Association::setFromBridgeKeys() 的参数 fromBridgeKeys 既不是数组也不是字符串');
        }
    }

    public function isType($nType){
        if($nType <self::oneToOne ){
            return $this->nType === $nType;
        }else if($nType === self::oneToOne){
            return ( $this->nType === self::hasOne || $this->nType === self::belongsTo);
        }else{
            return false;
        }
    }
    public function name(){
        return $this->sName;
    }
    public function fromPrototype(){
        return $this->aFromPrototype;
    }
    public function toPrototype(){
        return $this->aToPrototype;
    }
    public function type(){
        return $this->nType;
    }
    public function fromKeys(){
        return $this->arrFromKeys;
    }
    public function toKeys(){
        return $this->arrToKeys;
    }
    public function bridgeTable(){
        return $this->aBridgeTablePrototype;
    }
    public function toBridgeKeys(){
        return $this->arrToBridgeKeys;
    }
    public function fromBridgeKeys(){
        return $this->arrFromBridgeKeys;
    }
    
    // private data
    private $sName = '';// 如果不提供，用$aToPrototype的名字作名字。
    private $aFromPrototype = null ;
    private $aToPrototype = null ;
    private $nType = 0 ;
    private $arrFromKeys = array();
    private $arrToKeys = array();
    private $aBridgeTablePrototype;
    private $arrToBridgeKeys = array();
    private $arrFromBridgeKeys = array();
}
?>
