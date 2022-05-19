<?php
    include "db_connection.php";
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script>

        function submitfunction(){
            var confvalue = $("input[name='conf']").val();
            var radioValue = $("input[name='choice']:checked").val();
            var variableawal = $("input[name='variable']").val();
            $("#questions").load("load-question-backwardchaining.php", {
                confvalue,
                radioValue,
                variableawal
            });
        };
   
        function resetfunction(){
            var confvalue = $("input[name='conf']").val();
            var radioValue = $("input[name='choice']:checked").val();
            var variableawal = $("input[name='variable']").val();
            $("#questions").load("reset.php", {
                confvalue,
                radioValue,
                variableawal
            });
        };
    </script>

    <style>
    table {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 100%;
    }

    td, th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
    }

    tr:nth-child(even) {
    background-color: #dddddd;
    }
    </style>

    <title>Sistem Pakar</title>
</head>
<body>
    <div id="questions">
        <?php
            $rules_rulesid = 0;
            $counterpertanyaan = 0;
            $tempquestion = "";
            $getcon_var = "SELECT * FROM rules LIMIT 1"; //double_cabin
            $cekgetcon_var = mysqli_query($conn, $getcon_var);
            if(mysqli_num_rows($cekgetcon_var)>0){
                $i = 1;
                while($row = mysqli_fetch_assoc($cekgetcon_var)){
                    if($row["conclusion_variable"] == "rekomendasi"){ //cek conclusion variable
                        $getpremisesid = "SELECT * FROM connector WHERE rules_id=". $row['rules_id'].""; //ambil semua premises_id dari rules_id 1
                        $cekgetpremisesid = mysqli_query($conn, $getpremisesid);
                        while($row2 = mysqli_fetch_assoc($cekgetpremisesid)){
                            $getvariable = "SELECT * FROM premises WHERE premises_id=". $row2['premise_id']."";
                            $cekgetvariable = mysqli_query($conn, $getvariable);
                            while($row3 = mysqli_fetch_assoc($cekgetvariable)){
                                if($row3['variable']=="ukuran" && $row3['statuses']=="FR"){ // immediate conclusion
                                    $vartemp1 = $row3["variable"];
                                    $vartemp2 = $row3["value"];
                                    $getcon_var2 = "SELECT * FROM rules WHERE conclusion_variable='$vartemp1' AND conclusion_value='$vartemp2'";
                                    $cekgetcon_var2 = mysqli_query($conn, $getcon_var2);
                                    while($rowcon = mysqli_fetch_assoc($cekgetcon_var2)){
                                        $getpremisesid2 = "SELECT * FROM connector WHERE rules_id=". $rowcon['rules_id']. ""; //ambil semua premises_id dari rules_id immediate conclusion
                                        $cekgetpremisesid2 = mysqli_query($conn, $getpremisesid2);
                                        while($rowcon2 = mysqli_fetch_assoc($cekgetpremisesid2)){
                                            $getvariable2 = "SELECT * FROM premises WHERE premises_id=". $rowcon2['premise_id']. "";
                                            $cekgetvariable2 = mysqli_query($conn, $getvariable2);
                                            while($rowcon3 = mysqli_fetch_assoc($cekgetvariable2)){
                                                if($rowcon3['statuses']=="FR"){
                                                    $rules_rulesid = $rowcon['rules_id'];
                                                    $tempquestion = $rowcon3['variable'];
                                                    $counterpertanyaan = 1;
                                                    break;
                                                }
                                            }
                                            if($counterpertanyaan == 1){
                                                break;
                                            }
                                        }
                                        if($counterpertanyaan == 1){
                                            break;
                                        }
                                    }
                                    if($counterpertanyaan == 1){
                                        break;
                                    }
                                }   
                                else{
                                    $tempquestion = $row3['variable'];
                                    $counterpertanyaan = 1;
                                    break;
                                }
                            }
                            if($counterpertanyaan == 1){
                                break;
                            }
                        }
                        if($counterpertanyaan == 1){
                            break;
                        }
                    }
                    // else{

                    // }
                }
            }
            else{
                echo "No more data";
            }
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            echo "<div style='border:3px solid green;float:right;width:35%'>";
            echo "<b><u>Facts</u></b>";
            echo "<br>";
            $workingmemory = "SELECT * FROM question";
            $cekworkingmemory = mysqli_query($conn, $workingmemory);
            while($row = mysqli_fetch_assoc($cekworkingmemory)){
                if($row["answer"]!=null){
                    if($row['variable']=="ukuran"){
                        echo "<br>";
                        echo "<b>Immediate Conclusion:</b> ". $row["variable"]. "= ". $row["answer"]. " (". $row["confidence"]. ")<br>";
                    }
                    else if($row['variable']=="rekomendasi"){
                        echo "<br>";
                        echo "<b>Rekomendasi Akhir:</b> ". $row["variable"]. "= ". $row["answer"]." (". $row["confidence"]. ")<br>";
                    }
                    else{
                        echo $row['variable']. "= ". $row["answer"]." (". $row["confidence"]. ")<br>";
                    }
                }
            }
            echo "</div>";
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            echo "<div style='border:3px solid green;float:right;width:35%'>";
            echo "<b><u>Rules</u></b>";
            echo "<br>";
            echo "Rules ". $rules_rulesid. " IF<br>";
            $counter_kata_and = 0;
            $connector = "SELECT * FROM connector WHERE rules_id=". $rules_rulesid. ""; //ambil semua premises_id dari rules_id immediate conclusion
            $cekconnector = mysqli_query($conn, $connector);
            while($row = mysqli_fetch_assoc($cekconnector)){
                $premise = "SELECT * FROM premises WHERE premises_id =". $row['premise_id'] ."";
                $cekpremise = mysqli_query($conn, $premise);
                while($row2 = mysqli_fetch_assoc($cekpremise)){
                    if($counter_kata_and == 0){
                        $counter_kata_and = 1;
                    }
                    else{
                        echo " and<br>";
                    }
                    echo $row2["variable"]. "= ". $row2["value"]."";
                }
            }
            echo "<br>THEN<br>";
            $rules = "SELECT * FROM rules WHERE rules_id=$rules_rulesid";
            $cekrules = mysqli_query($conn, $rules);
            while($row2 = mysqli_fetch_assoc($cekrules)){
                echo $row2["conclusion_variable"]. " = ". $row2["conclusion_value"]. " (". $row2["confidence"]. ")<br>";
            }
            echo "</div>";
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            echo "<div style='float:right;width:70%'>";
            echo "<br>";
            echo "<table>";
            echo "<tr>";
            echo "<th>Rule Number</th>";
            echo "<th>Rule Status</th>";
            echo "<th>Premise Clause Name</th>";
            echo "<th>Premise Clause Answer</th>";
            echo "<th>Premise Clause Status</th>";
            echo "</tr>";
            $workingmemory = "SELECT * FROM rules";
            $cekworkingmemory = mysqli_query($conn, $workingmemory);
            while($row = mysqli_fetch_assoc($cekworkingmemory)){
                echo "<tr>";
                echo "<td>". $row['rules_id']. "</td>";
                echo "<td>";
                if($row['A']==1){
                    echo "A ";
                } 
                if($row["U"]==1){
                    echo "U ";
                }
                if($row["M"]==1){
                    echo "M ";
                }
                if($row["D"]==1){
                    echo "D ";
                }
                if($row["TD"]==1){
                    echo "TD ";
                }
                if($row["FD"]==1){
                    echo "FD ";
                }
                echo "</td>";
                echo "<td>";
                $getpremisesid = "SELECT * FROM connector WHERE rules_id=". $row['rules_id'].""; //ambil semua premises_id dari rules_id 1
                $cekgetpremisesid = mysqli_query($conn, $getpremisesid);
                while($row2 = mysqli_fetch_assoc($cekgetpremisesid)){
                    $getvariable = "SELECT * FROM premises WHERE premises_id=". $row2['premise_id']."";
                    $cekgetvariable = mysqli_query($conn, $getvariable);
                    while($row3 = mysqli_fetch_assoc($cekgetvariable)){
                        echo $row3["variable"];
                        echo "<br>";
                    }
                }
                echo "</td>";
                echo "<td>";
                $getpremisesid = "SELECT * FROM connector WHERE rules_id=". $row['rules_id'].""; //ambil semua premises_id dari rules_id 1
                $cekgetpremisesid = mysqli_query($conn, $getpremisesid);
                while($row2 = mysqli_fetch_assoc($cekgetpremisesid)){
                    $getvariable = "SELECT * FROM premises WHERE premises_id=". $row2['premise_id']."";
                    $cekgetvariable = mysqli_query($conn, $getvariable);
                    while($row3 = mysqli_fetch_assoc($cekgetvariable)){
                        echo $row3["value"];
                        echo "<br>";
                    }
                }
                echo "</td>";
                echo "<td>";
                $getpremisesid = "SELECT * FROM connector WHERE rules_id=". $row['rules_id'].""; //ambil semua premises_id dari rules_id 1
                $cekgetpremisesid = mysqli_query($conn, $getpremisesid);
                while($row2 = mysqli_fetch_assoc($cekgetpremisesid)){
                    $getvariable = "SELECT * FROM premises WHERE premises_id=". $row2['premise_id']."";
                    $cekgetvariable = mysqli_query($conn, $getvariable);
                    while($row3 = mysqli_fetch_assoc($cekgetvariable)){
                        echo $row3["statuses"];
                        echo "<br>";
                    }
                }
                echo "</td>";
            }
            echo "</table>";
            echo "</div>";
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $question = "SELECT * FROM question WHERE variable='$tempquestion'";
            $cekquestion = mysqli_query($conn, $question);
            while($rowquestion = mysqli_fetch_assoc($cekquestion)){
                if($rowquestion["question"] != ""){
                    echo "<form method='POST'>";
                    echo "<b>Question = ". $rowquestion["question"]. "</b><br>";
                    echo "<input type='radio' name='choice' id='choice1' value=". $rowquestion["choices1"]. ">";
                    echo "<label for='choice1'>". $rowquestion["choices1"]. "</label> <br>";
                    echo "<input type='radio' name='choice' id='choice2' value=". $rowquestion["choices2"]. ">";
                    echo "<label for='choice2'>". $rowquestion["choices2"]. "</label> <br>";
                    if($rowquestion["choices3"] != ""){
                        echo "<input type='radio' name='choice' id='choice3' value=". $rowquestion["choices3"]. ">";
                        echo "<label for='choice3'>". $rowquestion["choices3"]. "</label> <br>";
                    }
                    echo "<b>Confidence Level = </b><br>";
                    echo "<input type='number' name='conf'>";
                    echo "<input type='hidden' name='variable' value=$tempquestion>";
                    echo "</form>";
                    echo "<br>";
                    $i++;
                }
            }


            ?>

    </div>
        
    <input id="btn" type="button" value="submit" onclick="submitfunction();"/>
    <input id="btn" type="button" value="reset" onclick="resetfunction();"/>
    <br>

</body>
</html>
