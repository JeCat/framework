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
        foreach($aModel->dataIterator() as $alias => $data){
            $column = $aPrototype->getColumnByAlias($alias);
            $aSqlInsert->setData($column,$data);
            $aModel->removeChange($alias);
        }
        $aDB->execute($aSqlInsert->makeStatement());
        foreach($aModel->prototype()->associationIterator() as $aAssociation){
            if( $aAssociation->isType(Association::hasAndBelongsTo) ){
                $aBridgeInsert = StatementFactory::singleton()->createInsert($aAssociation->bridgeTable()->name());
                $toBridgeKeys = $aAssociation->toBridgeKeys();
                $fromBridgeKeys = $aAssociation->fromBridgeKeys();
                $fromKeys = $aAssociation->fromKeys();
                $toKeys = $aAssociation->toKeys();
                $n = count($toKeys);
                for($i=0;$i<$n;++$i){
                    $aBridgeInsert->setData($toBridgeKeys[$i],$aModel->data($fromKeys[$i]));
                    $aBridgeInsert->setData($fromBridgeKeys[$i],$aModel->child($aAssociation->name())->data($toKeys[$i]));
                }
                $aDB->execute($aBridgeInsert->makeStatement());
            }
        }
    }
}

?>
