<?php

namespace jc\mvc\model\db\orm;

use jc\lang\Object;
use jc\db\DB;
use jc\mvc\model\db\IModel ;
use jc\db\sql\StatementFactory ;

class Deleter extends Object{
    public function execute(DB $aDB, IModel $aModel){
        $aPrototype = $aModel->prototype();
        $sTableName = $aPrototype ->tableName();
        $aSqlDelete = StatementFactory::singleton()->createDelete($sTableName);
        $aCriteria = clone $aPrototype->criteria();
        $aSqlDelete ->setCriteria($aCriteria);
        $aRestriction = $aCriteria->restriction();
        $arrKeys = $aPrototype->keys();
        $arrChanged = $Model->changed();
        foreach($aModel->dataIterator() as $alias => $data){
            $column = $aPrototype->getColumnByAlias($alias);
            if( in_array($column,$arrKeys)){
                if( in_array( $column,$arrChanged) ){
                    throw new Exception('jc\mvc\model\db\orm\Deleter : Key 有修改');
                }else{
                    $aRestriction->eq($column,$data);
                }
            }
        }
        $aDB->execute($aSqlDelete->makeStatement());
        $arrColumns = $aPrototype->columns();
        foreach($arrColumns as $column){
            $aModel->setChanged($column);
        }
        foreach($aModel->prototype()->associationIterator() as $aAssociation){
            if($aAssociation->isType(Association::hasAndBelongsTo)){
                $aBridgeDelete = StatementFactory::singleton()->createDelete($aAssociation->bridgeTable()->name());
                $toBridgeKeys = $aAssociation->toBridgeKeys();
                $fromBridgeKeys = $aAssociation->fromBridgeKeys();
                $fromKeys = $aAssociation->fromKeys();
                $toKeys = $aAssociation->toKeys();
                $n = count($toKeys);
                for($i=0;$i<$n;++$i){
                    $aBridgeDelete->criteria()->restriction()->eq($toBridgeKeys[$i],$aModel->data($fromKeys[$i]));
                    $aBridgeDelete->criteria()->restriction()->eq($fromBridgeKeys[$i],$aModel->child($aAssociation->name())->data($toKeys[$i]));
                }
                $aDB->execute($aBridgeDelete->makeStatement());
            }
        }
        /**
         *   @todo 桥表可能还需要受第三方条件的限制才能确定一行记录。
         */
    }
}

?>
