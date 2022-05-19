<?php
    include "db_connection.php";
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <script>
      
        // $( "#target" ).click(function() {
        //     var questionCount = 1;
        //     var radioValue = $("input[name='choice']:checked").val();
        //     var variableawal = $("input[name='variable']").val();
        //     questionCount = questionCount + 1;
        //     $("#questions").load("load-question.php", {
        //         questionNewCount: questionCount,
        //         radioValue,
        //         variableawal
        //     });
        // });

        // $( "#other" ).click(function() {
        //     <?php
        //     echo "masok";
        //     $reset = "UPDATE rules SET A=1, U=1, M=0, D=0, TD=0, FD=0; UPDATE question SET answer=null; UPDATE premises SET statuses='FR';";
        //     if ($conn->query($reset) === TRUE) {
        //         echo "Reset berhasil";
        //         echo "<br>";
        //     } 
        //     else {
        //         echo "Error: " . $reset . "<br>" . $conn->error;
        //     }
        //     ?>
        // });

        function submitfunction(){
            var questionCount = 1;
            var radioValue = $("input[name='choice']:checked").val();
            var variableawal = $("input[name='variable']").val();
            questionCount = questionCount + 1;
            $("#questions").load("load-question.php", {
                questionNewCount: questionCount,
                radioValue,
                variableawal
            });
        };
   
        function resetfunction(){
            var questionCount = 1;
            var radioValue = $("input[name='choice']:checked").val();
            var variableawal = $("input[name='variable']").val();
            $("#questions").load("reset.php", {
                questionNewCount: questionCount,
                radioValue,
                variableawal
            });
        };
    </script>
    <title>Sistem Pakar</title>
</head>
<body>
    <div id="questions">
        <?php
            $sql = "SELECT * FROM question LIMIT 1";
            $result = mysqli_query($conn, $sql);
            if(mysqli_num_rows($result)>0){
                $i = 1;
                while($row = mysqli_fetch_assoc($result)){
                    if($row["question"] != ""){
                        echo "<form method='POST'>";
                        echo "Question ". $i. "= ". $row["question"]. "<br>";
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
            ?>
    </div>

    <!-- <div id="target">
    Submit
    </div>
    <div id="other">
    Reset
    </div> -->

    <input id="btn" type="button" value="submit" onclick="submitfunction();"/>
    <input id="btn" type="button" value="reset" onclick="resetfunction();"/>
    <br>
    <!-- <button value="reset">Reset</button>

</body>
</html>
