<?php
include "Connection.php";
$token = $_COOKIE['token'] ?? '';
$user = loadToken($token);