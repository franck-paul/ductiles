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
 * @copyright GPL-2.0-only
 */

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
    "Ductile Photo",                          // Name
    "Variation de Ductile pour un photoblog", // Description
    "Kozlika et Franck Paul",                 // Author
    '0.7',                                    // Version
    array(                                    // Properties
        'standalone_config' => true,
        'type'              => 'theme'
    )
);
