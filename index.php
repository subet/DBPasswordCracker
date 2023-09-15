<?php

    header("Cache-Control: no-cache, must-revalidate");
    header("Content-type: text/html; charset=utf-8");

    include_once "./config.php";
    include "./classes/db.php";
    $db = new DB(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $records = $db->query("SELECT * FROM not_so_smart_users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Murat Dikici - Password Cracker Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <style>
        .table-condensed tbody td {
            font-size: 12px;
        }

        #processList, #idList {
            height: 250px;
            overflow-x: hidden;
            overflow-y: auto;
            word-break: break-all;
            font-size: 12px;
            line-height: 100%;
        }

        #idList {
            height: 150px;
        }

        #processList p {
            margin-bottom: 0px;
            line-height: 130%;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="alert alert-secondary text-center">
                    <h5>DB Password Cracker</h5>
                    <hr />
                    <p class="m-0">Please click the "Crack Passwords" button and wait the process to complete. The found passwords will be marked in the table.</p>
                </div>
                <button type="button" class="btn btn-success w-100" id="btnCrack" >Crack Passwords</button>
                <div id="processList" class="alert alert-secondary mt-2"><strong>Progress Log</strong><hr /></div>
                <div id="idList" class="alert alert-secondary mt-2"><strong>Found IDs</strong><hr /></div>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <table class="table table-bordered table-sm table-condensed">
                        <thead>
                            <tr>
                                <td colspan="23" class="text-center"><strong>Record IDs to be cracked</strong></td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $i=1;
                            foreach($records as $row) {
                                if ($i==1) echo '<tr>';
                                echo '<td class="text-center" id="user_' . $row["user_id"] . '" data-bs-toggle="tooltip" data-bs-placement="top">' . $row["user_id"] . '</td>';
                                if ($i%23==0) echo '</tr><tr>';
                                $i++;
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<script>
    function setTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    }

    $("#btnCrack").click(function() {

        $("#btnCrack").attr("disabled", true);

        let tasks = [
            {
                startMessage: "T1: Numeric passwords...",
                charset: '0123456789',
                passwordLengths: [5]
            },
            {
                startMessage: "T2: 3 uppercase 1 numeric passwords...",
                charset: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
                passwordLengths: [4]
            },
            {
                startMessage: "T3: Lowercase max 6 chars passwords...",
                charset: 'abcdefghijklmnopqrstuvwxyz',
                passwordLengths: [1,2,3,4,5]
            },
            {
                startMessage: "T4: 6 char mixed passwords... (LONG)",
                charset: 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
                passwordLengths: [6]
            }
        ];

        runCracker(tasks, 0);
        
    });

    function runCracker(tasks, id) {

        let options = tasks[id];

        $("#processList").append("<p>" + options.startMessage + "</p>");
        
        $.ajax({
            url: 'crack-passwords.php',
            type: 'POST',
            data: { charset: options.charset, passwordLengths: options.passwordLengths },
            success: function(data){ 
                let finished = false;
                let found = JSON.parse(data);
                found.forEach(function(item, index) {
                    if (item != undefined) {
                        $("#user_" + item.user_id).addClass("table-success");
                        $("#user_" + item.user_id).attr("title", "PW: " + item.password);
                        $("#idList").append('<div class="badge bg-info mb-2">' + item.user_id + '</div> ');
                    }
                    console.log(item);
                });
                setTooltips();
                $("#processList").append("<p>Done</p>");

                if (id<tasks.length-1) {
                    runCracker(tasks, id+1);
                } else {
                    $("#processList").append("<p>Cracking completed!</p>");
                }
            },
            error: function(data) {
                setTimeout(() => {
                    console.log("Error occured, we are trying again");
                    $("#processList").append("<p>Error occured, retrying...</p>");
                    runCracker(tasks, id);
                }, 1000);
            }
        });
    }
</script>

</html>