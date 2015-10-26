<?php 
class Page 
{
    static function NumeralPager($p,$page,$baseUri,$total){
        if($page>1 && $p<=$page){
            $lastPage = $p == 0 ? '' : ('<li><a href="'.$baseUri. '&p=' . ($p - 1) . '" aria-label="Previous"><span aria-hidden="true">上一页</span></a></li>');
            $previousPages='';
            for ($i=max(array(0,$p-4));$i<$p;$i++){
                $previousPages.='<li><a href="'.$baseUri. '&p=' . $i . '">'.($i+1).'</a></li>';
            }
            $currentPage='<li class="active"><a href="###">' . ($p + 1) . '</a></li>';
            $nextPages='';
            for ($i=$p+1,$end=min(array($page,$p+5));$i<$end;$i++){
                $nextPages.='<li><a href="'.$baseUri. '&p=' . $i . '">'.($i+1).'</a></li>';
            }
            $nextPage = $p == $page - 1 ? '' : ('<li><a href="'.$baseUri. '&p=' . ($p + 1) . '"aria-label="Next"><span aria-hidden="true">下一页</span></a></li');
            return '<nav><ul class="pagination">' . $lastPage . $previousPages . $currentPage . $nextPages . $nextPage . '</ul>';
        }else{
            return '';
        }
    }
}