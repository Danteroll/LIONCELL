    <?php

    include 'config.php';
    session_start();
    $user_id = $_SESSION['usuario'];

    if(isset($_POST['update_profile'])){

    $update_name = mysqli_real_escape_string($conexion, $_POST['update_name']);
    $update_email = mysqli_real_escape_string($conexion, $_POST['update_email']);
    $update_image = $_FILES['update_image']['name'];
    $update_image_size = $_FILES['update_image']['size'];
    $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
    $update_image_folder = 'uploaded_img/'.$update_image;

    // Verifica si el correo ya existe en la base de datos
    if(!empty($update_email)){
        $verificar_correo = mysqli_query($conexion, "SELECT * FROM usuarios WHERE correo='$update_email' ");
        if(mysqli_num_rows($verificar_correo) > 0){
            echo '
                <script>
                    alert("Este correo ya está registrado, intenta con otro diferente");
                    window.location = "update_profile-user.php";
                </script>
            ';
            exit();
        }
    }

    // Verifica si el nombre de usuario ya existe en la base de datos
    if(!empty($update_name)){
        $verificar_usuario = mysqli_query($conexion, "SELECT * FROM usuarios WHERE usuario='$update_name' ");
        if(mysqli_num_rows($verificar_usuario) > 0){
            echo '
                <script>
                    alert("Este usuario ya está registrado, intenta con otro diferente");
                    window.location = "update_profile-user.php";
                </script>
            ';
            exit();
        }
    }

    // Actualiza el correo y/o el nombre de usuario
    if(!empty($update_email) || !empty($update_name)){
        if(empty($update_email)){
            mysqli_query($conexion, "UPDATE `usuarios` SET usuario = '$update_name' WHERE id = '$user_id'") or die('query failed');
            $message[] = 'Nombre de usuario actualizado correctamente!';
        } elseif(empty($update_name)){
            mysqli_query($conexion, "UPDATE `usuarios` SET correo = '$update_email' WHERE id = '$user_id'") or die('query failed');
            $message[] = 'Correo actualizado correctamente!';
        } else {
            mysqli_query($conexion, "UPDATE `usuarios` SET usuario = '$update_name', correo = '$update_email' WHERE id = '$user_id'") or die('query failed');
            $message[] = 'Nombre de usuario y correo actualizados correctamente!';
        }
    }

    // Actualiza la imagen de perfil
    if(!empty($update_image)){
        if($update_image_size > 2000000){
            $message[] = 'La imagen es muy grande';
        } else {
            $image_update_query = mysqli_query($conexion, "UPDATE usuarios SET image = '$update_image' WHERE id = '$user_id'") or die('query failed');
            if($image_update_query){
                move_uploaded_file($update_image_tmp_name, $update_image_folder);
            }
            $message[] = 'Imagen actualizada correctamente!';
        }
    }
    }

    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>update profile</title>

    <link rel="stylesheet" href="css/style.css">

    </head>
    <body>
    
    <div class="update-profile">

    <?php
        $select = mysqli_query($conexion, "SELECT * FROM usuarios WHERE id = '$user_id'") or die('query failed');
        if(mysqli_num_rows($select) > 0){
            $fetch = mysqli_fetch_assoc($select);
        }
    ?>

    <form action="" method="post" enctype="multipart/form-data">
        <?php
            if($fetch['image'] == ''){
                echo '<img src="images/default-avatar.png">';
            }else{
                echo '<img src="uploaded_img/'.$fetch['image'].'">';
            }
            if(isset($message)){
                foreach($message as $message){
                echo '<div class="message">'.$message.'</div>';
                }
            }
        ?>
        <div class="flex">
            <div class="inputBox">
                <span>Usuario :</span>
                <input type="text" name="update_name" placeholder="escribe un nuevo usuario si deseas cambiarlo" class="box"  pattern="^(?!.*\s{2})[A-Za-z0-9]+(\s?[A-Za-z0-9]+)?$" oninvalid="mostrarAlerta1(this.validity.patternMismatch ? 'caracteres' : 'longitud')">
                <span>Tu correo :</span>
                <input type="email" name="update_email" placeholder="escribe un nuevo correo si deseas cambiarlo" class="box" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" oninvalid="mostrarAlerta2(this.validity.patternMismatch ? 'correo electrónico' : 'longitud')">
                <span>Actualiza tu foto :</span>
                <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png" class="box">
            </div>
        </div>
        <input type="submit" value="actualizar tu perfil" name="update_profile" class="btn">
        <a href="home.php" class="delete-btn">regresar</a>
    </form>
    <script src="./alerta.js"></script>
    
    </div>
    </body>
    </html>