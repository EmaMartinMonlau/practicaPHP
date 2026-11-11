<?php
// 1. BUCLE FOR (Útil cuando sabemos el número exacto de repeticiones)
echo "<h2>Lista con FOR</h2><ul>";
for ($posicion = 1; $posicion <= 3; $posicion++) {
    echo "<li>Posición " . $posicion . "</li>";
}
echo "</ul>";

// 2. BUCLE WHILE (Se repite mientras una condición sea verdad)
echo "<h2>Lista con WHILE</h2><ul>";
$posicionWhile = 1;
while ($posicionWhile <= 3) {
    echo "<li>Posición " . $posicionWhile . "</li>";
    $posicionWhile++; 
}
echo "</ul>";

// 3. FOREACH CON UN ARRAY (La mejor forma para listas de datos)
// Creamos una estantería (Array) con 3 de tus canciones reales.
echo "<h2>Lista con FOREACH</h2><ul>";
$titulos = ['Indie Rock Dreamy', 'Pop Rock', 'Calm Calm Music', 'Canción prueba 1', 'Canción prueba 2', 'Canción prueba 3'];

// El foreach coge la estantería ($titulos) y en cada vuelta saca un cajón ($titulo)
foreach ($titulos as $titulo) {
    echo "<li>" . $titulo . "</li>";
}
echo "</ul>";
?>