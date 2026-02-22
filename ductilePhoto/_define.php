<?php

/**
 * @brief ductilePhoto, a theme for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Themes
 *
 * @author Kozlika
 * @author Franck Paul
 *
 * @copyright Kozlika and Franck Paul
 * @copyright GPL-2.0
 */
$this->registerModule(
    'Ductile Photo',
    'Variation de Ductile pour un photoblog',
    'Kozlika et Franck Paul',
    '2.0',
    [
        'date'              => '2003-08-13T13:42:00+0100',
        'requires'          => [['core', '2.36']],
        'standalone_config' => true,
        'type'              => 'theme',
        'license'           => 'gpl2',
        'overload'          => true,
    ]
);
