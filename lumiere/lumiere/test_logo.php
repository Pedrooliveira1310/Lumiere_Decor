<?php
// Teste para verificar se a logo está disponível
$logoPath = __DIR__ . '/uploads/logo.png';
$logoPath2 = __DIR__ . '/uploads/logo.png';

echo "Verificando logo...\n";
echo "Caminho: " . $logoPath . "\n";
echo "Existe: " . (file_exists($logoPath) ? "SIM" : "NÃO") . "\n";

if (file_exists($logoPath)) {
    $size = filesize($logoPath);
    echo "Tamanho: " . $size . " bytes\n";
    echo "<br><img src='uploads/logo.png' alt='Logo' style='max-width:100px'>";
} else {
    echo "ERRO: Logo não encontrada!";
}
?>
