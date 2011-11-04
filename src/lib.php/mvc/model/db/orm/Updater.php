<?php

namespace jc\mvc\model\db\orm;

use jc\lang\Object;
use jc\db\DB;
use jc\mvc\model\db\IModel ;
use jc\db\sql\StatementFactory ;

class Updater extends Object{
    public function execute(DB $aDB, IModel $aModel){
        $aPrototype = $aModel->prototype();
        $sTableName = $aPrototype ->tableName();
        $aSqlUpdate = StatementFactory::singleton()->createUpdate($sTableName);
        $aCriteria = clone $aPrototype->criteria();
        $aSqlUpdate ->setCriteria($aCriteria);
        $aRestriction = $aCriteria->restriction();
        $arrKeys = $aPrototype->keys();
        $arrChanged = $aModel->changed();
        if(empty($arrChanged)){// 如果没有列发生更改，不做任何操作
        	return;
    	}
        foreach($aModel->dataIterator() as $alias => $data){
            $column = $aPrototype->getColumnByAlias($alias);
            if( in_array($column,$arrKeys)){
                if( in_array( $column,$arrChanged) ){
                    throw new Exception('jc\mvc\model\db\orm\Updater : Key 有修改');
                }else{
                    $aRestriction->eq($column,$data);
                }
            }else{
                if( in_array( $column,$arrChanged) ){
                    $aSqlUpdate->setData($column,$data);
                    $aModel->removeChange($alias);
                }
            }
        }
        $aDB->execute($aSqlUpdate->makeStatement());
    }
}

?>
