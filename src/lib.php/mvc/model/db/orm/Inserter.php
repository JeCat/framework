<?php

namespace jc\mvc\model\db\orm;

use jc\lang\Object;
use jc\db\DB;
use jc\mvc\model\db\IModel ;
use jc\db\sql\StatementFactory ;

class Inserter extends Object{
    public function execute(DB $aDB, IModel $aModel){
        $aPrototype = $aModel->prototype();
        $sTableName = $aPrototype ->tableName();
        $aSqlInsert = StatementFactory::singleton()->createInsert($sTableName);
        $aCriteria = clone $aPrototype->criteria();
        $aRestriction = $aCriteria->restriction();
        $aSqlInsert ->setCriteria($aCriteria);
        $keys = $aPrototype->keys();
        foreach($keys as $k){
            $aRestriction->eq($k,$aModel->data($k));
        }
        $aDB->execute($aSqlInsert->makeStatement());
        $arrColumns = $aPrototype->columns();
        foreach($arrColumns as $column){
            $aModel->removeChanged($column);
        }
        foreach($aModel->prototype()->associationIterator() as $aAssociation){
            if( $aAssociation->isType(Association::hasAndBelongsTo) ){
                $aBridgeInsert = StatementFactory::singleton()->createInsert($aAssociation->bridgeTable()->name());
                $toBridgeKeys = $aAssociation->toBridgeKeys();
                $fromBridgeKeys = $aAssociation->fromBridgeKeys();
                $fromKeys = $aAssociation->fromKeys();
                $toKeys = $aAssociation->toKeys();
                $n = count($toKeys);
                for($i=0;$i<$n;++$i){
                    $aBridgeInsert->criteria()->restriction()->eq($toBridgeKeys[$i],$aModel->data($fromKeys[$i]));
                    $aBridgeInsert->criteria()->restriction()->eq($fromBridgeKeys[$i],$aModel->child($aAssociation->name())->data($toKeys[$i]));
                }
                $aDB->execute($aSqlDelete->makeStatement());
            }
        }
    }
}

?>
