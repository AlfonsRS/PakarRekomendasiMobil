<?php
    include "db_connection.php";

    $questionNewCount = $_POST["questionNewCount"];
    $jawaban1 = $_POST["radioValue"];
    $tempjawaban = $_POST["variableawal"];


    // echo $tempjawaban;
    // echo "<br>";
    // echo $jawaban1;
    // echo "<br>";
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
        //echo "AAAAAAAAAAAAAAAAAAAAAAAA";
        $hasilbenar = "UPDATE question SET answer='$tempconclusionvalue' WHERE variable='$tempconclusionvariable'";
        if ($conn->query($hasilbenar) === TRUE) {
            // echo "Conclusion Variable diisi";
            // echo "<br>";
        } 
        else {
            echo "Error: " . $hasilbenar . "<br>" . $conn->error;
        }
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

    //CEK SELESAI BELOM
    $finishstate = 0;
    $finish = "SELECT question.answer FROM question JOIN rules WHERE rules.FD=1 AND question.variable='rekomendasi' AND question.answer is not null LIMIT 1";
    $cekfinish = mysqli_query($conn, $finish);
    while($row = mysqli_fetch_assoc($cekfinish)){
        if($row['answer'] != null){
            echo "REKOMENDASI AKHIR = ";
            echo $row['answer'];
            $finishstate = 1;
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
    if($failurestate == 1 && $finishstate == 0){
        echo "REKOMENDASI AKHIR = TIDAK TERDEFINISI";
        $finishstate = 1;
    }

    //CARI VARIABLE PERTANYAAN SELANJUTNYA
    $temppertanyaan = 0;
    if($temp!=0){ //KALO PREMISE BELUM TRUE SEMUA
        $searchmarkedrules = "SELECT rules_id FROM rules WHERE M=1 LIMIT 1"; //CARI MARKED
        $ceksearchmarkedrules = mysqli_query($conn, $searchmarkedrules);
        while($row = mysqli_fetch_assoc($ceksearchmarkedrules)){
            $rules_to_konektor_to_premise = "SELECT premise_id FROM connector WHERE rules_id=". $row['rules_id']. ""; //CARI TOTAL PREMISE DI KONEKTOR BERDASARKAN RULES
            $cekrules_to_konektor_to_premise = mysqli_query($conn, $rules_to_konektor_to_premise);
            while($row2 = mysqli_fetch_assoc($cekrules_to_konektor_to_premise)){
                //echo $row2['premise_id'];
                $cari_variable_pertanyaan = "SELECT variable FROM premises WHERE premises_id=". $row2['premise_id']. " AND statuses='FR'"; //CARI VARIABLE YG FREE
                $cekcari_variable_pertanyaan = mysqli_query($conn, $cari_variable_pertanyaan);
                while($row3 = mysqli_fetch_assoc($cekcari_variable_pertanyaan)){
                    $tempjawaban = $row3['variable'];
                    //echo $tempjawaban;
                    $temppertanyaan = 1;
                    break;
                }
                if($temppertanyaan == 1){
                    break;
                }
            }
            if($temppertanyaan == 1){
                break;
            }
        }
    }
    else{ //KALO PREMISE SUDAH TRUE SEMUA
        $newquestion = "SELECT variable FROM question WHERE answer is null AND question is not null LIMIT 1";
        $ceknewquestion = mysqli_query($conn, $newquestion);
        while($row = mysqli_fetch_assoc($ceknewquestion)){
            $tempjawaban = $row['variable'];
        }
    }

    
    //LANJUT LOOPING PERTANYAAN
    if($finishstate!=1){
        $sql = "SELECT * FROM question WHERE variable='$tempjawaban'";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result)>0){
            $i = 1;
            while($row = mysqli_fetch_assoc($result)){
                if($row["question"] != ""){
                    echo "<form method='POST'>";
                    echo "Question ". $questionNewCount. "= ". $row["question"]. "<br>";
                    echo "<input type='radio' name='choice' id='choice1' value=". $row["choices1"]. ">";
                    echo "<label for='choice1'>". $row["choices1"]. "</label> <br>";
                    echo "<input type='radio' name='choice' id='choice2' value=". $row["choices2"]. ">";
                    echo "<label for='choice2'>". $row["choices2"]. "</label> <br>";
                    if($row["choices3"] != ""){
                        echo "<input type='radio' name='choice' id='choice3' value=". $row["choices3"]. ">";
                        echo "<label for='choice3'>". $row["choices3"]. "</label> <br>";
                    }
                    echo "<input type='hidden' name='variable' value=". $row["variable"]. ">";
                    echo "</form>";
                    echo "<br>";
                    $i++;
                }
            }
        }
        else{
            echo "No more data";
        }
    }

?>