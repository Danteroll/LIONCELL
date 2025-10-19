   <?php

   include 'config.php';
   session_start();
   $user_id = $_SESSION['usuario'];

   if(!isset($user_id)){
      header('location:../formulario.php');
   };

   if(isset($_GET['logout'])){
      session_unset();
      session_destroy();
      header('location:../formulario.php');
      exit();
   }

   ?>

   <!DOCTYPE html>
   <html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>home</title>
      <link rel="icon" href="imagenes/LogoLionCell.ico">

      <!-- custom css file link  -->
      <link rel="stylesheet" href="css/style.css">


   </head>
   <body>
      
   <div class="container">

      <div class="profile">
         <?php
            $select = mysqli_query($conexion, "SELECT * FROM `usuarios` WHERE id = '$user_id'") or die('query failed');
            if(mysqli_num_rows($select) > 0){
               $fetch = mysqli_fetch_assoc($select);
            }
            if($fetch['image'] == ''){
               echo '<img src="images/default-avatar.png">';
            }else{
               echo '<img src="uploaded_img/'.$fetch['image'].'">';
            }
         ?>
         <h3><?php echo $fetch['usuario']; ?></h3>
         <a href="update_profile-user.php" class="btn">Actualizar Usuario</a>
         <a href="../php/cerrar_sesion.php" class="delete-btn">Cerrar sesion</a>
         <p><a href="../index.php">Regresar</a></p>
      </div>

   </div>

   </body>
   </html>