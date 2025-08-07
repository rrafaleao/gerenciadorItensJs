<?php

$servername = 'localhost'
$username = 'root'
$password = ''
$dbname = 'gerenciamentoitens'

$conn = new mysqli($servername, $username, $password, $dbname);
echo($conn)