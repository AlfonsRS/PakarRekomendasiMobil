<?php
    include "db_connection.php";

    $confvalue = $_POST["confvalue"];
    $jawaban1 = $_POST["radioValue"];
    $tempjawaban = $_POST["variableawal"];

    $input = "UPDATE question SET answer='$jawaban1' WHERE variable='$tempjawaban'";
    if ($conn->query($input) === TRUE) {
        // echo "Jawaban Berhasil diinput";
        // echo "<br>";
    } 
    else {
        echo "Error: " . $input . "<br>" . $conn->error;
    }


    //CEK JAWABAN
    $cek = "SELECT * FROM question";
    $cek1 = "SELECT * FROM connector";
    $cek2 = "SELECT * FROM premises";
    $cek3 = "SELECT * FROM rules";
    $cekquestion = mysqli_query($conn, $cek);
    $cekkonektor = mysqli_query($conn, $cek1);
    $cekpremise = mysqli_query($conn, $cek2);
    $cekrules = mysqli_query($conn, $cek3);


    //SET TRUE FALSE PREMISE
    $correctpremise_id = 0;
    while($rowkonektor = mysqli_fetch_assoc($cekkonektor)){
        while($rowpremise = mysqli_fetch_assoc($cekpremise)){
            if($tempjawaban == $rowpremise["variable"] && $jawaban1 == $rowpremise["value"]){

                $correctpremise_id = $rowpremise["premises_id"];
                $updatestatusbenar = "UPDATE premises SET statuses='TU' WHERE premises_id=$correctpremise_id";
                if ($conn->query($updatestatusbenar) === TRUE) {
                    // echo "Premise True berhasil diupdate";
                    // echo "<br>";
                } 
                else {
                    echo "Error: " . $updatestatusbenar . "<br>" . $conn->error;
                }

                
                $updatestatussalah = "UPDATE premises SET statuses='FA' WHERE variable='$tempjawaban' AND statuses='FR'";
                if ($conn->query($updatestatussalah) === TRUE) {
                    // echo "Premise False berhasil diupdate";
                    // echo "<br>";
                } 
                else {
                    echo "Error: " . $updatestatussalah . "<br>" . $conn->error;
                }


            }
        }
    }


     //SET CONFIDENCE LEVEL DI QUESTION
    $input = "UPDATE question SET confidence=$confvalue WHERE variable='$tempjawaban'";
    if ($conn->query($input) === TRUE) {
        // echo "Jawaban Berhasil diinput";
        // echo "<br>";
    } 
    else {
        echo "Error: " . $input . "<br>" . $conn->error;
    }
    
    //SET A U M D
    //SET M
    $temp_m_checked = 0;
    $setM = "SELECT rules_id FROM connector WHERE premise_id=$correctpremise_id";
    $ceksetM = mysqli_query($conn, $setM);
    while($row = mysqli_fetch_assoc($ceksetM)){
        $checksetM = "SELECT * FROM rules WHERE rules_id=". $row['rules_id']. "";
        $cekchecksetM = mysqli_query($conn, $checksetM);
        while($row1 = mysqli_fetch_assoc($cekchecksetM)){
            if($row1['D']!=1){
                //echo $row['rules_id'];
                $updaterulesM = "UPDATE rules SET M=1, U=0 WHERE rules_id=". $row['rules_id']."";
                if ($conn->query($updaterulesM) === TRUE) {
                    //echo "Marked berhasil diupdate";
                    $temp_m_checked = 1;
                    //echo "<br>";
                    break;
                } 
                else {
                    echo "Error: " . $updaterulesM . "<br>" . $conn->error;
                }
            }
        }
        if($temp_m_checked == 1){
            break;
        }
    }

    //SET D
    $setpremisesalah = "SELECT premises_id FROM premises WHERE premises.statuses='FA' AND premises.variable='$tempjawaban'";
    $ceksetpremisesalah = mysqli_query($conn, $setpremisesalah);
    while($row = mysqli_fetch_assoc($ceksetpremisesalah)){
        $setD = "SELECT connector.rules_id FROM connector JOIN rules WHERE connector.premise_id=" .$row['premises_id']. " AND rules.A=1 AND rules.U=1 AND rules.D=0";
        $ceksetD = mysqli_query($conn, $setD);
        while($row2 = mysqli_fetch_assoc($ceksetD)){
            $updaterulesD = "UPDATE rules SET A=0, U=1, M=0, D=1 WHERE rules_id=". $row2['rules_id']."";
            if ($conn->query($updaterulesD) === TRUE) {
                // echo "Discard berhasil diupdate";
                // echo "<br>";
            } 
            else {
                echo "Error: " . $updaterulesD . "<br>" . $conn->error;
            }
        }
    }

    //CEK 1 RULES UDA TRUE SEMUA NDA
    $temp = 0;
    $temprulesid = 0;
    $tempconclusionvariable = "";
    $tempconclusionvalue = "";
    $counttotalM = 0;
    $countfalse = 0;
    $totalM = "SELECT * FROM rules WHERE M=1";
    $cektotalM = mysqli_query($conn, $totalM);
    while($row = mysqli_fetch_assoc($cektotalM)){
        $counttotalM += 1;
    }
    $searchmarkedrules = "SELECT * FROM rules WHERE M=1"; //CARI MARKED
    $ceksearchmarkedrules = mysqli_query($conn, $searchmarkedrules);
    while($row = mysqli_fetch_assoc($ceksearchmarkedrules)){
        $temprulesid = $row['rules_id']; //SIMPEN
        $tempconclusionvariable = $row['conclusion_variable']; //SIMPEN
        $tempconclusionvalue = $row['conclusion_value']; //SIMPEN
        $rules_to_konektor_to_premise = "SELECT premise_id FROM connector WHERE rules_id=". $row['rules_id']. ""; //CARI TOTAL PREMISE DI KONEKTOR BERDASARKAN RULES
        $cekrules_to_konektor_to_premise = mysqli_query($conn, $rules_to_konektor_to_premise);
        while($row2 = mysqli_fetch_assoc($cekrules_to_konektor_to_premise)){
            $cari_variable_pertanyaan = "SELECT statuses FROM premises WHERE premises_id=". $row2['premise_id']. ""; //CARI VARIABLE UNTUK DILIHAT STATUSNYA
            $cekcari_variable_pertanyaan = mysqli_query($conn, $cari_variable_pertanyaan);
            while($row3 = mysqli_fetch_assoc($cekcari_variable_pertanyaan)){
                if($row3['statuses'] == "FR" ){
                    $temp += 1;
                }
                else if($row3['statuses'] == "FA"){
                    $countfalse = 1;
                    $matikanrules = "UPDATE rules SET A=0, U=1, M=0, D=1 WHERE rules_id=$temprulesid";
                    if ($conn->query($matikanrules) === TRUE) {
                        //echo "Rules salah dimatikan";
                        //echo "<br>";
                    } 
                    else {
                        echo "Error: " . $matikanrules . "<br>" . $conn->error;
                    }
                    $temp += 1;
                    break;
                }
                else{
                    $temp += 0;
                }
                //echo "sape sini?";
            }
        }
        if($counttotalM==1){
            break;
        }
        if($countfalse==0){
            break;
        }
        $countfalse==0;
        $temp = 0;
    }
    //KALO BENER TRUE SEMUA
    if($temp == 0){
        $hasilbenar = "UPDATE question SET answer='$tempconclusionvalue' WHERE variable='$tempconclusionvariable'";
        if ($conn->query($hasilbenar) === TRUE) {
            // echo "Conclusion Variable diisi";
            // echo "<br>";
        } 
        else {
            echo "Error: " . $hasilbenar . "<br>" . $conn->error;
        }
        //HITUNG CONFIDENCE (MIN(LEVEL LEBIH TINGGI) * CONFIDENCE CONCLUSION)
        $conclusion_confidence = 0;
        $conf_connector = "SELECT * FROM connector WHERE rules_id = $temprulesid";
        $cekconf_connector = mysqli_query($conn, $conf_connector);
        //CARI MIN
        while($conflevel = mysqli_fetch_assoc($cekconf_connector)){
            $conf_premise = "SELECT * FROM premises WHERE premises_id =". $conflevel['premise_id']. "";
            $cekconf_premise = mysqli_query($conn, $conf_premise);
            while($conflevel_premise = mysqli_fetch_assoc($cekconf_premise)){
                $var =  $conflevel_premise["variable"];
                $conf_question = "SELECT * FROM question WHERE variable ='$var'";
                $cekconf_question = mysqli_query($conn, $conf_question);
                while($conflevel_question = mysqli_fetch_assoc($cekconf_question)){
                    if($conclusion_confidence == 0){
                        $conclusion_confidence = $conflevel_question['confidence'];
                    } 
                    else{
                        if($conclusion_confidence > $conflevel_question['confidence']){
                            $conclusion_confidence = $conflevel_question['confidence'];
                        }
                    }
                }
            }
        }
        $conf_rules = "SELECT confidence FROM rules WHERE rules_id = $temprulesid";
        $cekconf_rules = mysqli_query($conn, $conf_rules);
        while($conffinal = mysqli_fetch_assoc($cekconf_rules)){
            $conclusion_confidence = $conclusion_confidence * $conffinal['confidence'];
        }
        $input_confidence = "UPDATE question SET confidence=$conclusion_confidence/100 WHERE variable='$tempconclusionvariable'";
        if ($conn->query($input_confidence) === TRUE) {
            // echo "Conclusion Variable diisi";
            // echo "<br>";
        } 
        else {
            echo "Error: " . $input_confidence . "<br>" . $conn->error;
        }
        //UPDATE STATUS
        $updatestatus = "UPDATE rules SET A=1, U=1, M=0, D=0, TD=1 WHERE rules_id=$temprulesid";
        if ($conn->query($updatestatus) === TRUE) {
            // echo "Rule di Trigger";
            // echo "<br>";
        } 
        else {
            echo "Error: " . $updatestatus . "<br>" . $conn->error;
        }
        //UPDATE PREMISE CONCLUSION VARIABLE
        $updatepremisetrue = "UPDATE premises SET statuses ='TU' WHERE value='$tempconclusionvalue'";
        if ($conn->query($updatepremisetrue) === TRUE) {
            // echo "Premise Conclusion Var true diupdate";
            // echo "<br>";
        } 
        else {
            echo "Error: " . $updatepremisetrue . "<br>" . $conn->error;
        }
        $updatepremisefalse = "UPDATE premises SET statuses ='FA' WHERE statuses='FR' AND variable='$tempconclusionvariable'";
        if ($conn->query($updatepremisefalse) === TRUE) {
            // echo "Premise Conclusion Var false diupdate";
            // echo "<br>";
        } 
        else {
            echo "Error: " . $updatepremisefalse . "<br>" . $conn->error;
        }

        //MATIKAN SISA PERTANYAAN YANG SUDAH GA PENTING
        $offuselessquestion = "SELECT rules_id FROM rules WHERE conclusion_variable='$tempconclusionvariable'";
        $cekoffuselessquestion = mysqli_query($conn, $offuselessquestion);
        while($row = mysqli_fetch_assoc($cekoffuselessquestion)){
            $conn_to_prem = "SELECT premise_id FROM connector WHERE rules_id=". $row['rules_id']."";
            $cekconn_to_prem = mysqli_query($conn, $conn_to_prem);
            while($row2 = mysqli_fetch_assoc($cekconn_to_prem)){
                $var_to_ques = "SELECT variable FROM premises WHERE premises_id=". $row2['premise_id']. "";
                $cekvar_to_ques = mysqli_query($conn, $var_to_ques);
                while($row3 = mysqli_fetch_assoc($cekvar_to_ques)){
                    $tempvar = $row3['variable'];
                    $questionoff = "UPDATE question SET answer='-' WHERE variable='$tempvar' AND answer is null";
                    if ($conn->query($questionoff) === TRUE) {
                        // echo "Pertanyaan tidak berguna ditutup";
                        // echo "<br>";
                    } 
                    else {
                        echo "Error: " . $questionoff . "<br>" . $conn->error;
                    }
                }
            }
        }


        //SET D
        $setpremisesalah = "SELECT premises_id FROM premises WHERE premises.statuses='FA' AND premises.variable='$tempconclusionvariable'";
        $ceksetpremisesalah = mysqli_query($conn, $setpremisesalah);
        while($row = mysqli_fetch_assoc($ceksetpremisesalah)){
            $setD = "SELECT connector.rules_id FROM connector JOIN rules WHERE connector.premise_id=" .$row['premises_id']. " AND rules.A=1 AND rules.U=1 AND rules.D=0";
            $ceksetD = mysqli_query($conn, $setD);
            while($row2 = mysqli_fetch_assoc($ceksetD)){
                $updaterulesD = "UPDATE rules SET A=0, U=1, M=0, D=1 WHERE rules_id=". $row2['rules_id']."";
                if ($conn->query($updaterulesD) === TRUE) {
                    // echo "Discard berhasil diupdate";
                    // echo "<br>";
                } 
                else {
                    echo "Error: " . $updaterulesD . "<br>" . $conn->error;
                }
            }
        }

         //FIRE RULE
        $rulefire = "UPDATE rules SET A=0, U=1, M=0, D=0, TD=0, FD=1 WHERE rules_id=$temprulesid";
        if ($conn->query($rulefire) === TRUE) {
            // echo "Rule di Fire";
            // echo "<br>";
        } 
        else {
            echo "Error: " . $rulefire . "<br>" . $conn->error;
        }
    }

    //CEK SALAH SEMUA
    $failurestate = 1;
    $failure = "SELECT D FROM rules WHERE conclusion_variable='rekomendasi'";
    $cekfailure = mysqli_query($conn, $failure);
    while($row = mysqli_fetch_assoc($cekfailure)){
        if($row['D'] != 1){
            $failurestate = 0;
        }
    }

    //CARI VARIABLE PERTANYAAN SELANJUTNYA
    $counterpertanyaan = 0;
    $getcon_var = "SELECT * FROM rules"; 
    $cekgetcon_var = mysqli_query($conn, $getcon_var);
    if(mysqli_num_rows($cekgetcon_var)>0){
        $i = 1;
        while($row = mysqli_fetch_assoc($cekgetcon_var)){
            if($row["conclusion_variable"] == "rekomendasi" && $row["D"] == 0){ //cek conclusion variable
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
                                            $tempjawaban = $rowcon3['variable'];
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
                        else if($row3['variable']!="ukuran" && $row3['statuses']=="FR"){
                            $temprulesid = $row['rules_id'];
                            $tempjawaban = $row3['variable'];
                            $counterpertanyaan = 1;
                            break;
                        }
                        else if($row3['statuses']=="TU"){
                            //LANJUT LOOPING CARI PREMISE LAIN
                        }
                        else if($row3['statuses']=="FA"){ //immediate conclusion salah
                            
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

    
    echo "<div style='border:3px solid green;float:right;width:35%'>";
    echo "<b><u>Facts</u></b>";
    echo "<br>";
    $counter_rekomendasi_terisi = 0;
    $confidence_rekomendasi = 0;
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
                echo "<b>Rekomendasi Akhir:</b> ". $row["variable"]. "= ". $row["answer"]. " (". $row["confidence"]. ")<br>";
                $counter_rekomendasi_terisi = 1;
                $confidence_rekomendasi = $row["confidence"];
            }
            else{
                echo $row['variable']. "= ". $row["answer"]." (". $row["confidence"]. ")<br>";
            }
        }
    }
    echo "</div>";

    echo "<div style='border:3px solid green;float:right;width:35%'>";
    echo "<b><u>Rules</u></b>";
    echo "<br>";
    echo "Rules ". $temprulesid. " IF<br>";
    $counter_kata_and = 0;
    $connector = "SELECT * FROM connector WHERE rules_id=". $temprulesid. "";
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
            if($row2["variable"]=="ukuran"){
                $var = $row2["variable"];
                $conf_ukuran = "SELECT * FROM question WHERE variable='$var'";
                $cekconf_ukuran = mysqli_query($conn, $conf_ukuran);
                while($rowukuran = mysqli_fetch_assoc($cekconf_ukuran)){
                    echo $rowukuran["variable"]. "= ". $rowukuran["answer"]. " (". $rowukuran["confidence"]. ") ";
                }
            }
            else{
            echo $row2["variable"]. "= ". $row2["value"]. "";
            }
        }
    }
    echo "<br>THEN<br>";
    $rules = "SELECT * FROM rules WHERE rules_id=$temprulesid";
    $cekrules = mysqli_query($conn, $rules);
    while($row2 = mysqli_fetch_assoc($cekrules)){
        if($counter_rekomendasi_terisi == 1){
            echo $row2["conclusion_variable"]. " = ". $row2["conclusion_value"]. " (". $confidence_rekomendasi. ")<br>";
        }
        else{
            echo $row2["conclusion_variable"]. " = ". $row2["conclusion_value"]. " (". $row2["confidence"]. ")<br>";
        }
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


    //CEK SELESAI BELOM
    $finishstate = 0;
    $finish = "SELECT question.answer FROM question JOIN rules WHERE rules.FD=1 AND question.variable='rekomendasi' AND question.answer is not null LIMIT 1";
    $cekfinish = mysqli_query($conn, $finish);
    while($row = mysqli_fetch_assoc($cekfinish)){
        if($row['answer'] != null){
            echo "REKOMENDASI AKHIR = ";
            echo $row['answer'];
            echo "<br>";
            $finishstate = 1;
        }
    }
    if($failurestate == 1 && $finishstate == 0){
        echo "REKOMENDASI AKHIR = tidak_terdefinisi";
        echo "<br>";
        $finishstate = 1;
    }

    if($finishstate!=1){
        $question = "SELECT * FROM question WHERE variable='$tempjawaban'";
        $cekquestion = mysqli_query($conn, $question);
        while($rowquestion = mysqli_fetch_assoc($cekquestion)){
            if($rowquestion["question"] != ""){
                echo "<form method='POST'>";
                echo "<b>Question= ". $rowquestion["question"]. "</b><br>";
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
                echo "<input type='hidden' name='variable' value=$tempjawaban>";
                echo "</form>";
                echo "<br>";
                $i++;
            }
        }
    }
?>