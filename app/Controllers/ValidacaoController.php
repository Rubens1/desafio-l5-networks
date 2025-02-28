<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class ValidacaoController extends ResourceController
{
    public function validarCpfCnpj($cpfCnpj)
    {
        $cpfCnpj = preg_replace('/\D/', '', $cpfCnpj);

        if (strlen($cpfCnpj) === 11) {
            return $this->validarCPF($cpfCnpj);
        } elseif (strlen($cpfCnpj) === 14) {
            return $this->validarCNPJ($cpfCnpj);
        }

        return [
            'status' => 'erro',
            'message' => 'CPF ou CNPJ inválido.'
        ];
    }

    public function validarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return [
                'status' => 'erro',
                'message' => 'CPF inválido.'
            ];
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return [
                    'status' => 'erro',
                    'message' => 'CPF inválido.'
                ];
            }
        }

        return [
            'status' => 'success',
            'message' => 'CPF válido.'
        ];
    }

    public function validarCNPJ($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return [
                'status' => 'erro',
                'message' => 'CNPJ inválido.'
            ];
        }

        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return [
                'status' => 'erro',
                'message' => 'CNPJ inválido.'
            ];
        }

        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;
        if ($cnpj[13] != ($resto < 2 ? 0 : 11 - $resto)) {
            return [
                'status' => 'erro',
                'message' => 'CNPJ inválido.'
            ];
        }

        return [
            'status' => 'success',
            'message' => 'CNPJ válido.'
        ];
    }
}
