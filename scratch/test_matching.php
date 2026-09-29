<?php

function checkNombresCoinciden($nombreArticulo, $refPlancha) {
    $nombreArticulo = strtolower($nombreArticulo);
    $refPlancha = strtolower($refPlancha);

    // 1. Direct substring checks
    if (stripos($nombreArticulo, $refPlancha) !== false || stripos($refPlancha, $nombreArticulo) !== false) {
        return true;
    }

    // 2. Clean and split words
    $stopWords = ['caja', 'bolsa', 'tapa', 'base', 'empaque', 'de', 'con', 'para', 'cabida', 'plancha', 'sorm', 'kord'];
    
    // Extract words using regex to strip punctuation
    preg_match_all('/\w+/', $nombreArticulo, $artWords);
    preg_match_all('/\w+/', $refPlancha, $plWords);

    $cleanArtWords = array_diff($artWords[0] ?? [], $stopWords);
    $cleanPlWords = array_diff($plWords[0] ?? [], $stopWords);

    // If any clean word matches between the two, it's a match!
    foreach ($cleanArtWords as $aw) {
        if (strlen($aw) < 2) continue; // Skip single letters
        foreach ($cleanPlWords as $pw) {
            if ($aw === $pw) {
                return true;
            }
        }
    }

    return false;
}

$tests = [
    ["Caja LB18", "LB18"],
    ["Bolsa GK", "BOLSA GK"],
    ["Caja Antigraso", "Antigraso 25x23"],
    ["Caja LS12", "LS12 Sorm Cabida 1"],
    ["Caja LS12", "LS12 Sorm"],
    ["LI FAMILIAR", "LI FAMILIAR CABIDA 1"],
];

foreach ($tests as $t) {
    $res = checkNombresCoinciden($t[0], $t[1]) ? "MATCH" : "NO MATCH";
    echo "Art: '{$t[0]}' | Plancha: '{$t[1]}' -> {$res}\n";
}
