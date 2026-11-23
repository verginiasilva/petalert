<?php

/**
 * PetAlert - Gerador de Payload PIX Padrão BACEN (EMV-QRCPS-MPM / BR Code)
 * Gera a string do Pix Copia e Cola e dados para renderização de QR Code.
 */
class PixPayload
{
    private string $chavePix;
    private string $beneficiario;
    private string $cidade;
    private float $valor;
    private string $txid;
    private string $descricao;

    public function __construct(
        string $chavePix,
        string $beneficiario,
        string $cidade,
        float $valor,
        string $txid = '***',
        string $descricao = 'PetAlert'
    ) {
        $this->chavePix = trim($chavePix);
        $this->beneficiario = $this->sanitizarTexto($beneficiario, 25);
        $this->cidade = $this->sanitizarTexto($cidade, 15);
        $this->valor = round($valor, 2);
        $this->txid = preg_replace('/[^a-zA-Z0-9]/', '', $txid) ?: '***';
        $this->descricao = $this->sanitizarTexto($descricao, 25);
    }

    /**
     * Remove acentos e caracteres especiais para compatibilidade com o padrão EMV
     */
    private function sanitizarTexto(string $texto, int $limite): string
    {
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
        $texto = preg_replace('/[^A-Za-z0-9 ]/', '', $texto);
        return strtoupper(substr(trim($texto), 0, $limite));
    }

    /**
     * Formata um campo no padrão TLV (Tag-Length-Value)
     */
    private function formatarCampo(string $id, string $valor): string
    {
        $tamanho = str_pad((string)strlen($valor), 2, '0', STR_PAD_LEFT);
        return $id . $tamanho . $valor;
    }

    /**
     * Calcula o CRC16-CCITT (polinômio 0x1021, valor inicial 0xFFFF)
     */
    private function calcularCrc16(string $payload): string
    {
        $resultado = 0xFFFF;
        $tamanho = strlen($payload);

        for ($i = 0; $i < $tamanho; $i++) {
            $resultado ^= (ord($payload[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if (($resultado & 0x8000) !== 0) {
                    $resultado = (($resultado << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $resultado = ($resultado << 1) & 0xFFFF;
                }
            }
        }

        return strtoupper(str_pad(dechex($resultado), 4, '0', STR_PAD_LEFT));
    }

    /**
     * Monta o código Pix Copia e Cola completo e válido
     */
    public function gerarCodigo(): string
    {
        // 00: Payload Format Indicator
        $payload = $this->formatarCampo('00', '01');

        // 01: Point of Initiation Method (12 = dinâmico, 11 = estático)
        $payload .= $this->formatarCampo('01', '12');

        // 26: Merchant Account Information
        $gui = $this->formatarCampo('00', 'br.gov.bcb.pix');
        $chave = $this->formatarCampo('01', $this->chavePix);
        $infoAdicional = $this->descricao !== '' ? $this->formatarCampo('02', $this->descricao) : '';
        $merchantAccount = $gui . $chave . $infoAdicional;
        $payload .= $this->formatarCampo('26', $merchantAccount);

        // 52: Merchant Category Code
        $payload .= $this->formatarCampo('52', '0000');

        // 53: Transaction Currency (986 = BRL)
        $payload .= $this->formatarCampo('53', '986');

        // 54: Transaction Amount
        if ($this->valor > 0) {
            $payload .= $this->formatarCampo('54', number_format($this->valor, 2, '.', ''));
        }

        // 58: Country Code
        $payload .= $this->formatarCampo('58', 'BR');

        // 59: Merchant Name
        $payload .= $this->formatarCampo('59', $this->beneficiario ?: 'PETALERT');

        // 60: Merchant City
        $payload .= $this->formatarCampo('60', $this->cidade ?: 'OURINHOS');

        // 62: Additional Data Field (TXID)
        $txidField = $this->formatarCampo('05', $this->txid);
        $payload .= $this->formatarCampo('62', $txidField);

        // 63: CRC16 (Tag + Length + CRC)
        $payloadSemCrc = $payload . '6304';
        $crc = $this->calcularCrc16($payloadSemCrc);

        return $payloadSemCrc . $crc;
    }
}
