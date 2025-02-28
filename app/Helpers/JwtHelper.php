<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function gerarToken($usuario)
{
    $chave = "segredo123"; 
    $payload = [
        'iat' => time(),
        'exp' => time() + (60*60),
        'sub' => $usuario,
    ];
    return JWT::encode($payload, $chave, 'HS256');
}

function verificarToken($token)
{
    try {
        return JWT::decode($token, new Key("segredo123", 'HS256'));
    } catch (Exception $e) {
        return false;
    }
}
