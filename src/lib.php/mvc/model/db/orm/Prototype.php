<?php

namespace jc\mvc\model\db\orm;

use jc\lang\Exception;
use jc\db\DB;
use jc\db\sql\StatementFactory;

class Prototype{
    // static creator
    static public function create($sTableName,$keys=null,$columns = '*' , $aDB = null ){
        $aPrototype = new Prototype;
        $aPrototype->setTableName($sTableName);
        $aPrototype->setName($sTableName);
        if($keys === null){
            $keys = self::reflectKeys($sTableName,$aDB);
        }
        if($keys !== null and $keys !== array()){
            $aPrototype->setKeys($keys);
        }
        if($columns === '*'){
            $columns = self::reflectAllColumnsInTable($sTableName,$aDB);
        }
        if($columns !== null and $columns !== array()){
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
    /**
     *  \brief 设置键
     *
     *   键可以为多个。本函数接受一个数组（多个键）或一个字符串（一个键）。
     */
    public function setKeys( $Keys ){
        if(is_string($Keys)){
            $Keys = array($Keys);
        }
        if(is_array($Keys)){
            if(empty($Keys)){
                throw new Exception('函数 Prototype::setKeys() 的参数 keys 为空');
            }else{
                $this->arrKeys = $Keys;
            }
        }else{
            throw new Exception('函数 Prototype::setKeys() 的参数 keys 既不是数组也不是字符串');
        }
        return $this;
    }
    public function tableName(){
        return $this->sTableName;
    }
    public function setTableName($sTableName){
        $this->sTableName = $sTableName;
    }
    public function criteria($bCreate=true){
        if( $this->aCriteria === null and $bCreate){
            $this->aCriteria = StatementFactory::singleton()->createCriteria();
        }
        return $this->aCriteria;
    }
    public function associateBy(){
        return $this->aAssociationBy;
    }
    
    // columns
    public function columns(){
        return $this->arrColumns;
    }
    /**
     *  \brief 添加列
     *
     *  本函数接受一个数组（多个列）或一个字符串（一个列）。
     */
    public function addColumn($Column){
        if(is_string($Column)){
            $this->arrColumns[]=$Column;
        }else if(is_array($Column)){
            $this->arrColumns = array_merge($this->arrColumns,$Column);
        }else{
            throw new Exception('函数 Prototype::addColumn() 的参数 Column 既不是数组也不是字符串');
        }
        return $this;
    }
    public function removeColumn($sColumn){
        $key=array_search($sColumn,$this->arrColumns,true);
        if($key!=false){
            unset($this->arrColumns[$key]);
        }
        return $this;
    }
    public function clearColumns(){
        $this->arrColumns=array();
        return $this;
    }
    public function columnIterator(){
        return new \ArrayIterator($this->arrColumns);
    }
    
    public function columnAliases(){
        return $this->arrColumnAliases;
    }
    public function getColumnByAlias($sAlias){
        if(isset($this->arrColumnAliases[$sAlias])){
            return $this->arrColumnAliases[$sAlias];
        }else{
            if(in_array($sAlias,$this->columns())){
                return $sAlias;
            }else{
                return '';
            }
        }
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
    public function associations(){
        return $this->arrAssociations;
    }
    public function hasOne($toTable,$fromKeys=null,$toKeys=null){
        return $this->addAssociation(Association::hasOne,$toTable,$fromKeys,$toKeys);
    }
    public function hasMany($toTable,$fromKeys=null,$toKeys=null){
        return $this->addAssociation(Association::hasMany,$toTable,$fromKeys,$toKeys);
    }
    public function belongsTo($toTable,$fromKeys=null,$toKeys=null){
        return $this->addAssociation(Association::belongsTo,$toTable,$fromKeys,$toKeys);
    }
    public function hasAndBelongsTo($toTable,$BridgeTable,$fromKeys=null,$toKeys=null,$toBridgeKeys=null,$fromBridgeKeys=null){
        return $this->addAssociation(Association::hasAndBelongsTo,$toTable,$fromKeys,$toKeys,$BridgeTable,$toBridgeKeys,$fromBridgeKeys);
    }
    /**
     *  \a $toTable 可以是一个字符串，也可以是一个Prototype对象，表示关联的表。
     */
    public function addAssociation($nType,$toTable,$fromKeys=null,$toKeys=null,$BridgeTable=null,$toBridgeKeys=null,$fromBridgeKeys=null){
        if(is_string($toTable)){
            $aToPrototype = self::create($toTable);
        }else if( $toTable instanceof Prototype){
            $aToPrototype = $toTable;
        }else{
            throw new Exception('函数 Prototype::addAssociation() 的参数 $toTable 既不是字符串也不是Prototype对象');
        }
        if($aToPrototype -> aAssociationBy !== null){
            throw new Exception('函数 Prototype::addAssociation() 的参数 $toTable 已经被关联，不能再添加其它关联');
        }
        $aAsso = new Association(
                $nType,
                $this,
                $aToPrototype,
                $fromKeys,
                $toKeys,
                '',
                $BridgeTable,
                $toBridgeKeys,
                $fromBridgeKeys
            );
        $this->arrAssociations[] = $aAsso;
        $aToPrototype -> aAssociationBy = $aAsso;
        return $aAsso -> toPrototype();
    }
    public function removeAssociation($aAssociation){
        $key=array_search($aAssociation,$this->arrAssociations,true);
        if($key!==false){
            unset($this->arrAssociations[$key]);
        }
        return $this;
    }
    public function clearAssociations(){
        $this->arrAssociations=array();
        return $this;
    }
    public function associationIterator($nType=Association::total){
        $arrT = array();
        foreach($this->arrAssociations as $ass){
            if($ass->isType($nType)) $arrT[]=$ass;
        }
        return new \ArrayIterator($arrT);
    }
    
    // done and check
    public function done(){
        $this->check();
        foreach($this->associationIterator() as $it){
            $it -> check();
        }
        foreach($this->associationIterator() as $it){
            $it -> toPrototype()->check();
        }
        if($this->associateBy() === null ){
            return null;
        }else{
            return $this->associateBy()->fromPrototype();
        }
    }
    private function check(){
        $keys = $this->keys();
        if(empty($keys)){
            throw new Exception('%s 的键为空',$this->name());
        }
    }
    // private constructor
    private function __construct(){}
    
    // static private reflecter
    /**
     *  反射数据表的键。
     *
     *  返回$aDB对象中$sTableName对应的表的键。
     *  如果$aDB为null，则会从系统中得到一个单件。
     */
    static private function reflectKeys($sTableName,$aDB){
        if($aDB === null){
            $aDB = DB::singleton();
        }
        $aIndexReflecter = $aDB->reflecterFactory()->createIndexReflecter($sTableName, 'PRIMARY','learnphp');
        $keys = $aIndexReflecter->columnNames();
        return $keys;
    }
    /**
     *  反射数据表中所有列。
     *
     *  返回$aDB对象中$sTableName对应的表的所有列。
     *  如果$aDB为null，则会从系统中得到一个单件。
     */
    static private function reflectAllColumnsInTable($sTableName,$aDB){
        if($aDB === null){
            $aDB = DB::singleton();
        }
        $columns = array();
        $aTableReflecter = $aDB->reflecterFactory()->createTableReflecter($sTableName,'learnphp');
        $aIter = $aTableReflecter->columnNameIterator();
        foreach($aIter as $v){
            $columns[] = $v;
        }
        return $columns;
    }
    
    // private data
    private $sName;// 如果不提供，用表名作名字。
    private $sTableName='';
    private $arrColumns = array();
    private $arrColumnAliases = array();
    private $arrKeys = array();
    private $aCriteria = null;
    private $arrAssociations =  array();
    private $aAssociationBy = null;
}
?>
