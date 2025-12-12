<?php
// test_modelos.php

// PEGA TU API KEY AQUI DENTRO
$apiKey = "AIzaSyCmf-W47s92qtBvhDJs0l3MyhCS928xO_0"; 

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Solo para local

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "Error de cURL: " . curl_error($ch);
    exit;
}

curl_close($ch);

$json = json_decode($response, true);

echo "<h1>Lista de Modelos Disponibles</h1>";
echo "<pre>"; // Esto hace que el JSON se lea mejor

if (isset($json['models'])) {
    foreach ($json['models'] as $modelo) {
        // Solo mostramos los que sirven para generar contenido
        if (in_array("generateContent", $modelo['supportedGenerationMethods'])) {
            echo "Nombre: " . $modelo['name'] . "\n";
            echo "Version: " . $modelo['version'] . "\n";
            echo "--------------------------------\n";
        }
    }
} else {
    echo "No se encontraron modelos o hubo un error:\n";
    print_r($json);
}

echo "</pre>";
?>