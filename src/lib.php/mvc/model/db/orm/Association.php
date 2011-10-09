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
    /**
     *  设置连桥表的原型、左连键和右连键
     *
     *  \a $BridgeTable，连桥表。接受字符串或Prototype对象，表示表名或原型。
     *  \a $toBridgeKeys 和 \a $fromBridgeKeys 接受字符串或数组。
     *
     *  \sa setToBridgeKeys() , setFromBridgeKeys() 
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
            if($this->aToPrototype === null){
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
        if($nType === self::total){
            return true;
        }else if($nType <self::oneToOne ){
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
        if( $this->arrFromKeys === array() ){
            if($this->fromPrototype()->keys() !== array() ){
                $this->setFromKeys( $this->fromPrototype()->keys());
            }else{
                throw new Exception('%s 的fromKeys为空数组并且fromPrototype的键也为空');
            }
        }
        return $this->arrFromKeys;
    }
    public function toKeys(){
        if( $this->arrToKeys === array() ){
            if($this->toPrototype()->keys() !== array() ){
                $this->setToKeys( $this->toPrototype()->keys());
            }else{
                throw new Exception('%s 的toKeys为空数组并且toPrototype的键也为空',$this->name());
            }
        }
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
    public function check(){
        if(in_array($this->type(),array( self::hasOne , 
                                         self::belongsTo ,
                                         self::hasMany ,
                                         self::hasAndBelongsTo))){
            if( ! ($this->fromPrototype() instanceof Prototype) ){
                throw new Exception('%s 的 fromPrototype 不正确。',$this->name());
            }
            if( ! ($this->toPrototype() instanceof Prototype) ){
                throw new Exception('%s 的 toPrototype 不正确。',$this->name());
            }
            if( $this->fromKeys() === array ()){
                throw new Exception('%s 的 fromKeys 为空数组',$this->name());
            }
            if( $this->toKeys() === array ()){
                throw new Exception('%s 的 toKeys 为空数组',$this->name());
            }
            if( count($this->toKeys()) !== count($this->fromKeys())){
                throw new Exception('%s 的 toKeys 与 fromKeys 数量不同：toKeys的数量为 %d , fromKeys的数量为 %d',
                            array($this->name(),count($this->toKeys()),count($this->fromKeys())));
            }
        }else{
            throw new Exception('%s 的 type 不正确 : %d',array($this->name(),$this->type()));
        }
        if($this->type() === self::hasAndBelongsTo ){
            if($this->bridgeTable() === null){
                throw new Exception('%s 的 类型为 hasAndBelongsTo 但 bridgeTable 为 null',$this->name());
            }
            if($this->toBridgeKeys() === array()){
                throw new Exception('%s 的 类型为 hasAndBelongsTo 但 toBridgeKeys 为空数组',$this->name());
            }
            if($this->fromBridgeKeys() === array()){
                throw new Exception('%s 的 类型为 hasAndBelongsTo 但 fromBridgeKeys 为空数组',$this->name());
            }
            if( count($this->toBridgeKeys()) !== count($this->fromBridgeKeys())){
                throw new Exception('%s 的 toBridgeKeys 与 fromBridgeKeys 数量不同：toBridgeKeys的数量为 %d , fromBridgeKeys的数量为 %d',
                            array($this->name(),count($this->toBridgeKeys()),count($this->fromBridgeKeys())));
            }
        }
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
