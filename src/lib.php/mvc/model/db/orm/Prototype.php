<?php

namespace jc\mvc\model\db\orm;

use jc\lang\Exception;

class Prototype{
    // static creator
    static public function create($sTableName,$keys=null,$columns = '*' , $aDB = null ){
        $aPrototype = new Prototype;
        $aPrototype->setTableName($sTableName);
        $aPrototype->setName($sTableName);
        if($keys !== null ){
            $aPrototype ->setKeys($keys );
        }else{
            $aPrototype->setKeys(self::reflectKeys($sTableName,$aDB));
        }
        if($columns === '*'){
            $aPrototype->addColumn(self::reflectAllColumnsInTable($sTableName,$aDB));
        }else{
            $aPrototype->addColumn($columns);
        }
        return $aPrototype;
    }
    
    // getter and setter
    public function name(){
        return $this->sName;
    }
    public function setName($sName){
        $this->sName = $sName;
    }
    public function keys(){
        return $this->arrKeys;
    }
    /*!
        \brief 设置键
        
        键可以为多个。本函数接受一个数组（多个键）或一个字符串（一个键）。
    */
    public function setKeys( $Keys ){
        if(!is_string($Keys)){
            $Keys = (array)$Keys;
        }
        if(is_array($Keys)){
            $this->arrKeys = $Keys;
        }else{
            throw new Exception('函数 Prototype::setKeys() 的参数 keys 既不是数组也不是字符串');
        }
    }
    public function tableName(){
        return $this->sTableName;
    }
    public function setTableName($sTableName){
        $this->sTableName = $sTableName;
    }
    public function cirteria(){
        return $this->aCriteria;
    }
    public function associateBy(){
        return $this->aAssociationBy;
    }
    
    // columns
    /*!
        \brief 添加列
        
        本函数接受一个数组（多个列）或一个字符串（一个列）。
    */
    public function addColumn($Column){
        if(is_string($Column)){
            $this->arrColumns[]=$Column;
        }else if(is_array($Column)){
            $this->arrColumns = array_merge($this->arrColumns,$Column);
        }
        return $this;
    }
    public function removeColumn($sColumn){
        $key=array_search($sColumn,$this->arrColumns,true);
        if($key!=false){
            unset($this->arrColumns[$key]);
        }
    }
    public function clearColumns(){
        $this->arrColumns=array();
        return $this;
    }
    public function columnIterator(){
        return new \ArrayIterator($this->arrColumns);
    }
    
    public function addColumnAlias($sColumn,$sAlias){
        $this->arrColumnAliases[$sAlias]=$sColumn;
    }
    public function removeColumnAlias($sAlias){
        unset($this->arrColumnAliases[$sAlias]);
    }
    public function clearColumnAliases(){
        $this->arrColumnAliases=array();
    }
    public function aliasColumnMapIterator(){
        return new \ArrayIterator($this->arrColumnAliases);
    }
    
    // association
    public function hasOne($sTableName,$fromKeys,$toKeys){
        return $this->addAssociation(Association::hasOne,$sTableName,$fromKeys,$toKeys);
    }
    public function hasMany($sTableName,$fromKeys,$toKeys){
        return $this->addAssociation(Association::hasMany,$sTableName,$fromKeys,$toKeys);
    }
    public function belongsTo($sTableName,$fromKeys,$toKeys){
        return $this->addAssociation(Association::belongsTo,$sTableName,$fromKeys,$toKeys);
    }
    public function hasAndBelongsTo($sTableName,$BridgeTable,$fromKeys,$toKeys,$toBridgeKeys,$fromBridgeKeys){
        return $this->addAssociation(Association::hasAndBelongsTo,$sTableName,$fromKeys,$toKeys,$BridgeTable,$toBridgeKeys,$fromBridgeKeys);
    }
    public function addAssociation($nType,$sTableName,$fromKeys,$toKeys,$BridgeTable=null,$toBridgeKeys=null,$fromBridgeKeys=null){
        $aAsso = new Association(
                $nType,
                $this,
                self::create($sTableName),
                $fromKeys,
                $toKeys,
                '',
                $BridgeTable,
                $toBridgeKeys,
                $fromBridgeKeys
            );
        $this->arrAssociations[] = $aAsso;
        return $aAsso -> toPrototype();
    }
    public function removeAssociation($aAssociation){
        $key=array_search($sColumn,$this->arrAssociations,true);
        if($key!=false){
            unset($this->arrAssociations[$key]);
        }
    }
    public function clearAssociations(){
        $this->arrAssociations=array();
    }
    public function associationIterator($nType=Association::total){
        $arrT = array();
        foreach($this->arrAssociations as $ass){
            if($ass->isType($nType)) $arrT[]=$ass;
        }
        return new \ArrayIterator($arrT);
    }
    
    // private constructor
    private function __construct(){}
    
    // static private reflecter
    /*!
        \brief 反射数据表的键。
        
        返回$aDB对象中$sTableName对应的表的键。
        如果$aDB为null，则会从系统中得到一个单件。
    */
    static private function reflectKeys($sTableName,$aDB){
        return array(
                'Prototype::reflectKeys',
                '函数未完成'
                );
    }
    /*!
        \brief 反射数据表中所有列。
        
        返回$aDB对象中$sTableName对应的表的所有列。
        如果$aDB为null，则会从系统中得到一个单件。
    */
    static private function reflectAllColumnsInTable($sTableName,$aDB){
        return array(
                'Prototype::reflectColumns',
                '函数未完成'
                );
    }
    
    
    // private data
    private $sName;// 如何不提供，用表名作名字。
    private $sTableName='';
    private $arrColumns = array();
    private $arrColumnAliases = array();
    private $arrKeys = array();
    private $aCriteria = null;
    
    private $arrAssociations =  array();
    private $aAssociationBy = null;
}
?>
