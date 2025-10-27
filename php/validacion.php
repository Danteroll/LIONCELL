<?php
// php/validacion.php

function esUsuarioValido($s) {
  return preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9]{3,30}$/u', $s);
}

function esNombreValido($s) { // permite espacios simples, solo letras
  return preg_match('/^(?!.*\s{2,})[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+)*$/u', $s);
}

function esApellidoValido($s) { // sin espacios, solo letras
  return preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ]+$/u', $s);
}

function esCorreoASCIIValido($email) {
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
  if (preg_match('/[^\x00-\x7F]/', $email)) return false; // sin ñ/tildes
  $partes = explode('@', $email);
  $dominio = $partes[1] ?? '';
  if (!preg_match('/\.[A-Za-z]{2,24}$/', $dominio)) return false; // TLD 2–24
  if (strpos($dominio, '..') !== false) return false; // sin doble punto
  return true;
}

function esContrasenaValida($s) {
  return preg_match('/^(?=\S{8,25}$)(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]+$/', $s);
}

function salir($msg, $back = true){
  if ($back) {
    echo "<script>alert('{$msg}'); window.history.back();</script>";
  } else {
    echo "<script>alert('{$msg}'); window.location='../formulario.php';</script>";
  }
  exit;
}

?>