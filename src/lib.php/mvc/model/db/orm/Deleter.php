<?php

namespace jc\mvc\model\db\orm;

use jc\lang\Exception;
use jc\lang\Object;
use jc\db\DB;
use jc\mvc\model\db\IModel ;
use jc\db\sql\Delete as SqlDelete ;

class Deleter extends Object{
    public function execute(DB $aDB, IModel $aModel){
        $aPrototype = $aModel->prototype();
        $sTableName = $aPrototype ->tableName();
        $aSqlDelete = new SqlDelete($sTableName);
        $aCriteria = clone $aPrototype->criteria();
        $aRestriction = $aCriteria->restriction();
        $aSqlDelete ->setCriteria($aCriteria);
        $keys = $aPrototype->keys();
        foreach($keys as $k){
            $aRestriction->eq($k,$aModel->data($k));
        }
        $aDB->execute($aSqlDelete->makeStatement());
        $arrColumns = $aPrototype->columns();
        foreach($arrColumns as $column){
            $aModel->setChanged($column);
        }
        foreach($aModel->childIterator() as $aChildModel){
            self::singleton()->execute($aDB, $aChildModel);
        }
    }
}

?>
