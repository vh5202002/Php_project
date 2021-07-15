<?php
require 'includes/functions.php';

session_start();
if(!isset($_SESSION['loggedin']))
{
    header('Location: index.php');
    exit();
}

if (preg_match("/^[0-9]+$/", $_GET['id']))
{
    deleteProfile($_GET['id'], $_SESSION['username']);

    if(isset($_SESSION['admin']))
    {
        deleteFromAdmin($_GET['id']);
    }
}


header('Location: profiles.php');
exit();
