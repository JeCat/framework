<?php

namespace jc\mvc\model\db\orm;

use jc\lang\Object;
use jc\db\DB;
use jc\mvc\model\db\IModel ;
use jc\db\sql\StatementFactory ;

class Selecter extends Object{
    public function execute(DB $aDB, IModel $aModel){
        $aPrototype = $aModel->prototype();
        $sTableName = $aPrototype ->tableName();
        $aSqlSelect = StatementFactory::singleton()->createSelect($sTableName);
        $aCriteria = clone $aPrototype->criteria();
        $aSqlSelect ->setCriteria($aCriteria);
        $aRestriction = $aCriteria->restriction();
        $arrKeys = $aPrototype->keys();
        $arrChanged = $Model->changed();
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
                            ['Prototype'] => $aPrototype,
                            ['Table alias'] => $aPrototype->name(),
                            ['association'] => array(),
                            ['father node'] => -1
                            );
        $arrPrototypeTree[0]['Table object'] = StatementFactory::createTable(
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
                                                            ['object'] => $aAssociation,
                                                            ['node no'] => $nNext
                                                        );
                $arrPrototypeTree[$nNext] = array(
                                                ['Prototype'] => $aAssociation->toPrototype(),
                                                ['Table alias'] => $aTmpTableAlias.'.'.$aAssociation->name(),
                                                ['association'] => array(),
                                                ['father node'] => $nTmp
                                            );
                $arrPrototypeTree[$nNext]['Table object'] = StatementFactory::createTable(
                                                                    $arrPrototypeTree[$nNext]['Prototype']->tableName(),
                                                                    $arrPrototypeTree[$nNext]['Table alias']
                                                                            );
                $arrTravelQueue[]=$nNext;
            }
        }
        $arrTravelQueue = array();
        $arrJoinQueue = array();
        $arrTravelQueue [] = 0;
        while( ! empty($arrTravelQueue)){
            $nTmp = array_shift($arrTravelQueue);
            $aTmpPrototype = $arrProrotypeTree[$nTmp]['Prototype'];
            $aTmpTableAlias = $arrPrototypeTree[$nTmp]['Table alias'];
            
        }
