<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Aspirasi Sekolah</title>
    <link rel="stylesheet" type="text/css"href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand&display=swap" rel="stylesheet">
</head>
<body id="bg-login">
    <div class="box-login">
        <h2>Login</h2>
        <form action="" method="POST">
            <input type="text" name="user" placeholder="Username" class="input-control">
            <input type="password" name="pass" placeholder="Password" class="input-control">
            <input type="submit" name="submit" value="Login" class="btn">
        </form>  
        <?php
        session_start();
        if (isset($_POST['submit'])){
            include 'db.php';
            
            $user = $_POST['user'];
            $pass = $_POST['pass'];

            // Gunakan prepared statement untuk keamanan
            $stmt = $CON->prepare("SELECT * FROM tb_admin WHERE username = ? AND password = ?");
            $hashed_pass = MD5($pass);
            $stmt->bind_param("ss", $user, $hashed_pass);
            $stmt->execute();
            $cek = $stmt->get_result();
            
            if($cek->num_rows > 0){
                $d = $cek->fetch_object();
                $_SESSION['status_login'] = true;
                $_SESSION['a_global'] = $d;
                $_SESSION['id'] = $d->id_admin;
                echo '<script>window.location="dashboard.php"</script>';
            } else{
                echo '<script>alert("Username atau password Anda salah!")</script>'; 
            }
            $stmt->close();
        }
        ?> 
    </div>
</body>
</html>