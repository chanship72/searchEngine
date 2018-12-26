<?php
include("simple_html_dom.php");

function getSnippet($url, $query){
//    echo $url, $query;
    
    $queryStr = explode(" ",strtolower(trim($query)));
//    echo "queryStr:".$queryStr;
//    $queryArray = array_pop($queryStr);
//    echo "queryArray:".$queryArray;
    
    $file = file_get_contents($url);
    $html_file = str_get_html($file);
    $contents = strtolower(trim($html_file->plaintext));
    
    $contents = strip_tags($contents);
    $contents = str_replace("\'","",$contents);
    $contents = str_replace("!","",$contents);
    $contents = str_replace("?","",$contents);
    $contents = str_replace(",","",$contents);
    
    $termSet = explode(" ", $contents);
//    print_r($termSet);

    $termSetArray = array_values(array_filter($termSet));
//    print_r($termSetArray);
//    print("index:".array_search($queryStr, $termSetArray));
    $idx_starter = 0;
    $ori_termSetArray = $termSetArray;

    $searchIdxList = [];
    
    for($i=0;$i<sizeof($queryStr);$i++){
//        echo "matching:".$queryStr[$i]."(";
        $start = array_search($queryStr[$i], $termSetArray);
        if($start !== FALSE){
//            echo $start.")";
//            echo "Insert!";
            $start = $idx_starter + $start;
            $idx_starter = $start;
//            echo "idx_starter:".$idx_starter;
            array_push($searchIdxList,$idx_starter);

            $termSetArray = array_slice($termSetArray, $idx_starter);
        }
    }
//    print_r($searchIdxList);
    $snip_start = null;
    $snip_candidate = null;
    for($j=0;$j<sizeof($searchIdxList);$j++){
        if(in_array($searchIdxList[$j]+1,$searchIdxList)){
            $snip_start = $searchIdxList[$j];
        }else{
//            echo "searchIdxList[".$j."]:".$searchIdxList[$j];
            if($snip_candidate===null && $snip_start===null){
                $snip_candidate = $searchIdxList[$j];
            }
        }
    }
//    print_r($searchIdxList);

//    echo "snip_candidate:".$snip_candidate;
    if($snip_start !== null){
        $start = $snip_start;
        $searchIdxList = [];
        array_push($searchIdxList,$start);
        array_push($searchIdxList,$start+1);
    }else{
        if($snip_candidate !== null){
            $start = $snip_candidate;
            $searchIdxList = [];
            array_push($searchIdxList,$start);
        }else{
            $start = null;
        }
    }
        
        
//    echo "start:".$start;
    if($start !== null){
        if((int)$start > 8){
            $start -= 8;
        }else{
            $start = 0; 
        }

//        echo "start:".$start;
        if($end > count($ori_termSetArray)){
            $end = count($ori_termSetArray-1);
        }else{
            $end = $start+16;
        }
//        echo "end:".$end;

//        print_r($searchIdxList);
        if($start < $end){
            for($k = $start; $k<$end; $k++){
                if(in_array($k,$searchIdxList)){
                    $snippet .= " <b>".$ori_termSetArray[$k]."</b>";
                }else{
                    $snippet .= " ".$ori_termSetArray[$k];                
                }
            }
            return "... ".$snippet." ...";
        }else{
            return "";
        }
    }else{
        return "";
    }
}
?>