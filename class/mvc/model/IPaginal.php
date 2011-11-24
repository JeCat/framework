<?php
namespace org\jecat\framework\mvc\model ;

use org\jecat\framework\mvc\view\widget\Paginator;

interface IPaginal
{
    public function totalCount();
    public function setPagination($iPerPage,$iPageNum);
}

?>
