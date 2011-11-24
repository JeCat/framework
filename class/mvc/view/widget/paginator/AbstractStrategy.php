<?php
namespace org\jecat\framework\mvc\view\widget\paginator;

use org\jecat\framework\mvc\view\widget\paginator\Middle;
use org\jecat\framework\lang\Exception;

abstract class AbstractStrategy{
    abstract public function pageNumList($iWidth,$iCurrent,$iTotal);
    
    static public function createByName($sName){
        $className=__NAMESPACE__.'\\'.ucfirst($sName);
        if( class_exists($className)){
            $o = new $className;
            return $o;
        }else{
            throw new Exception('can not found class '.$className."\n".'您设置的分页器策略为:'.$className.'。此策略不存在。');
        }
    }
}
?>
