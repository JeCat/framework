<?php
namespace org\jecat\framework\mvc\view\widget\paginator;

class Middle extends AbstractStrategy{
    public function pageNumList($iWidth,$iCurrent,$iTotal){
        if($iTotal < 1) return array();
        if($iWidth > $iTotal){
            return range(1,$iTotal);
        }
        $iStart = $iCurrent - (int)( $iWidth /2 );
        if( $iStart <1 ) $iStart = 1;
        else if($iStart + $iWidth -1 > $iTotal ) $iStart = $iTotal +1 -$iWidth;
        return range($iStart,$iStart + $iWidth-1);
    }
}

?>
