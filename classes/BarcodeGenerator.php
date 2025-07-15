<?php
require_once '../includes/config.php';
require_once '../includes/database.php';

class BarcodeGenerator {
    public function generate($text) {
        // Usar una librería como picqer/php-barcode-generator en un proyecto real
        // Esta es una implementación simplificada para demostración
        
        header('Content-Type: image/png');
        
        $width = 200;
        $height = 50;
        $image = imagecreate($width, $height);
        
        // Colores
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        // Fondo blanco
        imagefilledrectangle($image, 0, 0, $width, $height, $white);
        
        // Texto del código de barras (simulado)
        imagestring($image, 3, 10, 20, $text, $black);
        
        // Líneas simulando código de barras
        for ($i = 0; $i < strlen($text); $i++) {
            $thickness = rand(1, 3);
            $lineHeight = rand(10, 30);
            $x = 10 + ($i * 10);
            imageline($image, $x, 10, $x, 10 + $lineHeight, $black);
        }
        
        imagepng($image);
        imagedestroy($image);
    }
}

// Generar código de barras si se llama directamente
if (isset($_GET['text'])) {
    $generator = new BarcodeGenerator();
    $generator->generate($_GET['text']);
}
?>