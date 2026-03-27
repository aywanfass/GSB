<?php

/**
 * Barème des indemnités kilométriques au 01/09/2025
 */

return [
    'enabled' => true,
    'default_puissance_cv' => '4CV_D',
    'bareme' => [
        '4CV_D' => ['libelle' => 'Véhicule 4CV Diesel', 'tarif' => 0.52],
        '5/6CV_D' => ['libelle' => 'Véhicule 5/6CV Diesel', 'tarif' => 0.58],
        '4CV_E' => ['libelle' => 'Véhicule 4CV Essence', 'tarif' => 0.62],
        '5/6CV_E' => ['libelle' => 'Véhicule 5/6CV Essence', 'tarif' => 0.67],
    ]
];
