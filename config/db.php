<?php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'linkmy_db');

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$conn){
        die("Koneksi database gagal: ". mysqli_connect_error());
    }

    mysqli_set_charset($conn, "utf8mb4");

    function escape_string($string){
        global $conn;
        return mysqli_real_escape_string($conn, $string);
    }

    function execute_query($query, $params = [], $types = ""){
        global $conn;
        if (empty($params)){
            return mysqli_query($conn, $query);
        }

        $stmt = mysqli_prepare($conn, $query);

        if ($stmt == false){
            return false;
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result == false){
            return false;
        }
        return $result;
    }

    function get_single_row($query, $params = [], $types = ""){
        $result =  execute_query($query, $params, $types);

        if ($result && mysqli_num_rows($result) > 0){
            return mysqli_fetch_assoc($result);
        }
        return null;
    }

    function get_all_rows($query, $params = [], $types = ""){
        $result = execute_query($query, $params, $types);
        $rows = [];

        if ($result && mysqli_num_rows($result) > 0){
            while ($row = mysqli_fetch_assoc($result)){
                $rows[] = $row;
            }
        }
        return $rows;
    }
?>