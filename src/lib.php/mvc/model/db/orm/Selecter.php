<?php

namespace jc\mvc\model\db\orm;

use jc\lang\Object;
use jc\db\DB;
use jc\mvc\model\db\IModel ;
use jc\db\sql\StatementFactory ;
use jc\lang\Exception;

class Selecter extends Object{
    public function execute(DB $aDB, IModel $aModel){
        $aPrototype = $aModel->prototype();
        $sTableName = $aPrototype ->tableName();
        $aSqlSelect = StatementFactory::singleton()->createSelect($sTableName);
        $aCriteria = clone $aPrototype->criteria();
        $aSqlSelect ->setCriteria($aCriteria);
        $aRestriction = $aCriteria->restriction();
        $arrKeys = $aPrototype->keys();
        $arrChanged = $aModel->changed();
        /**
            array(
                    0 => array(
                            ['Prototype'] => $aPrototype,
                            ['Table alias'] => 'xxx',
                            ['Table object'] => $aTable ,
                            ['association'] =>array(
                                    0 =>array(
                                            ['object'] => $aAssociation,
                                            ['node no'] => 1
                                            ),
                                    1 => array(),
                                    ),
                            ['father node'] => -1
        */
        $arrPrototypeTree = array();
        $arrPrototypeTree[0] = array(
                            'Prototype' => $aPrototype,
                            'Table alias' => $aPrototype->name(),
                            'association' => array(),
                            'father node' => -1
                            );
        $arrPrototypeTree[0]['Table object'] = StatementFactory::singleton()->createTable(
                                                                    $arrPrototypeTree[0]['Prototype']->tableName(),
                                                                    $arrPrototypeTree[0]['Table alias']
                                                                            );
        $arrTravelQueue = array();
        $arrTravelQueue [] = 0;
        while( ! empty($arrTravelQueue)){
            $nTmp = array_shift ( $arrTravelQueue );
            $aTmpPrototype = $arrPrototypeTree[$nTmp]['Prototype'];
            $aTmpTableAlias = $arrPrototypeTree[$nTmp]['Table alias'];
            foreach($aTmpPrototype->associationIterator() as $aAssociation){
                $nNext = count($arrTravelQueue);
                $arrPrototypeTree[$nTmp]['association'][]=array(
                                                            'object' => $aAssociation,
                                                            'node no' => $nNext
                                                        );
                $arrPrototypeTree[$nNext] = array(
                                                'Prototype' => $aAssociation->toPrototype(),
                                                'Table alias' => $aTmpTableAlias.'.'.$aAssociation->name(),
                                                'association' => array(),
                                                'father node' => $nTmp
                                            );
                $arrPrototypeTree[$nNext]['Table object'] = StatementFactory::createTable(
                                                                    $arrPrototypeTree[$nNext]['Prototype']->tableName(),
                                                                    $arrPrototypeTree[$nNext]['Table alias']
                                                                            );
                $arrTravelQueue[]=$nNext;
            }
        }
        $arrHeadQueue = array(0);
        while( ! empty( $arrHeadQueue) ){
            $nHead = array_shift( $arrHeadQueue );
            $arrJoinQueue = array( $nHead );
            while( !empty( $arrJoinQueue ) ){
                $nJoin = array_shift( $arrJoinQueue );
                $aNowTable = $arrPrototypeTree[$nJoin]['Table object'];
                $arrAssociation = $arrPrototypeTree[$nJoin]['association'];
                foreach( $arrAssociation as $association ){
                    $nNode = $association['node no'];
                    $aTablesJoin = StatementFactory::createTablesJoin();
                    $aTablesJoin->addTable( $arrPrototypeTree[$nNode] ['Table object'] );
                    $aNowTable -> addJoin($aTablesJoin);
                    if( $association['object']->isType(Association::oneToOne)){
                        $arrJoinQueue[]=$nNode;
                    }else{
                        $arrHeadQueue[]=$nNode;
                    }
                }
            }
        }
        $arrHeadQueue = array(0);
        while( ! empty( $arrHeadQueue) ){
            $nHead = array_shift( $arrHeadQueue );
            $arrJoinQueue = array( $nHead );
            $aHeadPrototype = $arrPrototypeTree[$nHead]['Prototype'];
            $aSqlSelect -> clearColumn();
            $aSqlSelect -> criteria()->setLimitFrom(
                                                $aHeadPrototype->criteria()->limitFrom()
                                                    );
            $aSqlSelect -> criteria()->setLimitLen(
                                                $aHeadPrototype->criteria()->limitLen()
                                                    );
            $aSqlSelect -> criteria() -> setOrder(
                                                $aHeadPrototype->criteria()->orders()
                                                    );
            while( !empty( $arrJoinQueue ) ){
                $nJoin = array_shift( $arrJoinQueue );
                $strTableAlias = $arrPrototypeTree[$nJoin]['Table alias'];
                $aNowPrototype = $arrPrototypeTree[$nJoin]['Prototype'];
                foreach($aNowPrototype->columnIterator() as $column){
                    $aSqlSelect -> addColumn( $column , $strTableAlias.$column);
                }
                foreach( $arrAssociation as $association ){
                    $nNode = $association['node no'];
                    if( $association['object']->isType(Association::oneToOne)){
                        $arrJoinQueue[]=$nNode;
                    }else{
                        $arrHeadQueue[]=$nNode;
                    }
                }
            }
        }
        $aRecordSet = $aDB->query( $aSqlSelect->makeStatement() );
        foreach($aRecordSet as $a){
            var_dump($a);
        }
        $aModel->loadData($aRecordSet);
    }
}
?>
