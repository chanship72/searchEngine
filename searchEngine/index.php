<?php
ini_set('memory_limit', '1024M');
include 'SpellCorrector.php';
include 'snippet.php';
    
header('Content-Type:text/html; charset=utf-8');
$limit = 10;
$query= isset($_REQUEST['q'])?$_REQUEST['q']:false;
$results = false;

if($query){
	$split = explode(" ", $query);
	$check ="";
    foreach($split as $sol){
        $check.= SpellCorrector::correct($sol).' ';
    }
	$link = "http://localhost:9000/index.php?q=".$check; 
    
    require_once('./solr-php-client/Apache/Solr/Service.php');
    $solr = new Apache_Solr_Service('localhost', 8983, '/solr/latimes/');
    if(get_magic_quotes_gpc() == 1){
            $query = stripslashes($check);
    }

    try{
        if(!isset($_GET['algorithm']))
            $_GET['algorithm']="lucene";
        if($_GET['algorithm'] == "lucene"){
             $results = $solr->search($check, 0, $limit);
        }else{
            $param = array('sort'=>'pageRankFile desc');
            $results = $solr->search($check, 0, $limit, $param);
        }
    }catch(Exception $e){
                    die("<html><head><title>SEARCH EXCEPTION</title></head><body><pre>{$e->__toString()}</pre></body></html>");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <title> LA Times article search </title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" 
    integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <style>
        html{
            font-size:0.9rem;
        }
        .listitem{
            display: block;
            padding-left: 10px;
            color:#007bff;
            background-color: #FEFEFE;
        }
    </style>
    <script>
        function relocateTop(){
            document.getElementById("searchBox").style.marginTop = "350px";
        }
        
        function getSuggestion(str){
            if(str.length == 0){
                return ; 
            }
            else{
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if(this.readyState == 4 && this.status == 200){

                        var availableTags = JSON.parse(this.responseText);
                        var html = ''; 
                        for(var i=1; i<availableTags.length; i++){
                            html += '<div>' + availableTags[i] + '</div>';
                        }
                        showList(availableTags);
                    }
                };
                xmlhttp.open("GET", "http://localhost:9000/autocomplete.php?q=" + str, true);
                xmlhttp.send();
            }
        }
        
        function callClick(obj) {
//                alert("click");
                var tree = document.getElementById('q');
                var textValue = tree.value.split(" ");
                textValue[textValue.length - 1] = obj.innerHTML;
                tree.value = textValue.join(" ");
                var myList = document.getElementById('list');
                myList.innerHTML = '';
                tree.focus();
        }
        
        function showList(availableTags) {
//            alert("showList"+availableTags);
            var ul = document.getElementById("autolist");
            ul.innerHTML = '';
            for(var i = 0; i < availableTags.length; i++) {
                var li = document.createElement("li");
                li.appendChild(document.createTextNode(availableTags[i]));
                li.setAttribute("onclick", "callClick(this)");
                li.setAttribute("class", "listitem");
                ul.appendChild(li);
            }
//            alert("showlist end");
        }        
    </script>
</head>
<body>
<div class="container" id="searchBox">
    <div class="container-fluid px-0 py-0">
        <div class="row justify-content-center bg-light rounded px-2 mt-2">
            <form accept-charset="utf-8" method="get">
                <div class="form-group d-flex justify-content-center mt-2">
                    <h5>LA Times article search</h5>
                </div>
                <div class="form-group d-flex justify-content-center">
<!--
                    <label class="custom-control-label" for="q">Keyword: </label>
                    <input class="custom-control-input" />
-->
                    <div class="input-group mb-0">
                      <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroup-sizing-default">Keyword</span>
                      </div>
                      <input type="text" class="form-control" id="q" name="q" type="text" onkeyup="getSuggestion(this.value)" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8');?>">
                    </div>
                </div>
                <div>
                    <ul id="autolist" style="margin-left:40px;margin-top:-10px;"></ul>
                </div>
                <div class="row form-group d-flex justify-content-around" style="width:500px;">
                    <div class="custom-control custom-radio" >
                        <input class="custom-control-input" type="radio" name="algorithm" id="algorithm1" value="lucene" checked <?php if($_GET['algorithm']=="lucene") {echo "checked";}?>> 
                        <label class="custom-control-label" for="algorithm1">Solr's Default - Lucene</label>
                    </div>
                    <div class="custom-control custom-radio d-flex justify-content-center" >
                        <input class="custom-control-input" id="algorithm2" type="radio" name="algorithm" value="pagerank"  <?php if($_GET['algorithm']=="pagerank"){echo "checked";}?>> 
                        <label class="custom-control-label" for="algorithm2">Google's - PageRank</label>
                    </div>
                </div>
            </form>  
        </div>
    </div>
</div>

<div class='row mx-0 my-2 p-auto rounded'>
    <div class='col-sm-12 my-0 mx-0 py-1 justify-content-start'>
<?php
    if($query){
        if($check != strtolower($query).' '){
            echo "Query Suggestion : <a href='$link'>$check</a>";
            echo "<br/>"; 
            echo "You typed:  ".$query;
        }
    }

        
    if($results){
        $total = (int)$results->response->numFound; 
        $start = min(1,$total);
        $end = min($limit, $total); 

        echo "<table class='table table-sm table-borderless my-0 py-0'><tbody>";
        foreach ($results->response->docs as $doc){
            foreach($doc as $field => $value){
                if($field == "og_url"){
                        $link = $value; 
                }
            }
            echo "<tr class=''>";
            echo "<td class=''>";
            echo "<a href='".$link."' target='_blank'>";        

            foreach($doc as $field => $value){
                if($field!="id" && $field!="title" && $field!="description" && $field!="og_url"){
                    continue;  
                }
                if(sizeof($value)==1){
                    if($field=="description"){
                        $description = htmlspecialchars($value,  ENT_NOQUOTES);
                    }
                    if($field=="id"){
                        $id = htmlspecialchars($value,  ENT_NOQUOTES);
                        $snip = getSnippet($id,$check);
                    }
                    if($field=="title"){
                        $title = htmlspecialchars($value,  ENT_NOQUOTES);
                    }                    
                }else{
                    $i = 0;
                    foreach($value as $item){
                        if($i==2){
                            break;
                        }
                        if($field=="title")
                            $title = htmlspecialchars($item,  ENT_NOQUOTES);
                        $i++;
                    }
                }
            }
            echo "<div class='row mx-0 my-1 p-auto rounded'>";
            echo "<div class='col-sm-12 my-1 mx-0 py-1 justify-content-start'>";
            echo "<table class='table table-sm table-borderless my-0 py-0'>";
                        echo "<tr class='my-0 py-0'>";
                            echo "<td class='float-sm-left my-0 py-0' style='height:1.2rem;'>";
                                echo "<a ng-href='".$link."' target='_blank'><h6>".$title."</h6></a>";
                            echo "</td>";
                        echo "</tr>";
                        echo "<tr class='my-0 py-0'>";
                            echo "<td class='float-sm-left py-0' style='font-size:0.8rem;color:green;'>";
                                echo $id;
                            echo "</td>";
                        echo "</tr>";
                        echo "<tr class='my-0 py-0'>";
                            echo "<td class='float-sm-left py-0' style='font-size:0.8rem;color:black;'>";
                                echo $snip;
                            echo "</td>";
                        echo "</tr>";
                        echo "<tr class='my-0 py-0'>";
                            echo "<td class='justify-content-start' style='font-size:0.8rem;'>";
                                echo "<div class='float-sm-left mr-2 my-0 py-0'>";
                                    echo "<span style='color:#000000;'>".$description."</span>";
                                echo "</div>";
                            echo "</td>";
                        echo "</tr>";
            echo "</table>";        
            echo "</div>";    
            echo "</div>";
            echo "</a>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "<div> Results: ".$start."-".$end." of ".$total."</div>"; 
    }else{
        echo "<script>relocateTop();</script>";
    }
    ?>
    </div>
</div>
</body>
</html>
