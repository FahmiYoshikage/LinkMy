<?php
    // Database configuration - supports both local (XAMPP) and Docker environments
    // Docker environment variables will override defaults
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASSWORD') ?: '');
    define('DB_NAME', getenv('DB_NAME') ?: 'linkmy_db');

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
        if ($stmt === false){
            return false;
        }
        if (!empty($params)){
            if (empty($types)){
                $types = str_repeat('s', count($params));
            }
            $bindParams = [];
            $bindParams[] = $stmt; 
            $bindParams[] = $types; 
            foreach ($params as $key => $value){
                $bindParams[] = &$params[$key];
            }
            call_user_func_array('mysqli_stmt_bind_param', $bindParams);
        }

        if (!mysqli_stmt_execute($stmt)){
            return false;
        }

        $result = mysqli_stmt_get_result($stmt);
        if ($result === false){
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