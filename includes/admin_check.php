<?php
if(!is_admin()){
  header('Location: '.BASE_URL.'/auth/login.php');
  exit;
}
