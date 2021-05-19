<?php

declare(strict_types=1);

/*
 *
 *  Contao Open Source CMS
 *
 *  Copyright (c) 2005-2014 Leo Feyer
 *
 *  @package   Efg
 *  @author    Thomas Kuhn <mail@th-kuhn.de>
 *  @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *  @copyright Thomas Kuhn 2007-2014
 *
 *
 *  Porting EFG to Contao 4
 *  Based on EFG Contao 3 from Thomas Kuhn
 *
 *  @package   contao-efg-bundle
 *  @author    Peter Broghammer <mail@pb-contao@gmx.de>
 *  @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *  @copyright Peter Broghammer 2021-
 *
 *  Thomas Kuhn's Efg package has been completely converted to contao 4.9
 *  extended by insert_tag  {{efg_insert::formalias::aliasvalue::column(::format)}}
 *
 */

class_alias(PBDKN\Efgco4\Resources\contao\drivers\DC_Formdata::class, 'DC_Formdata');
class_alias(PBDKN\Efgco4\Resources\contao\classes\Formdata::class, 'Formdata');
class_alias(PBDKN\Efgco4\Resources\contao\classes\FormdataBackend::class, 'FormdataBackend');

/*
 * Register the templates
 */
TemplateLoader::addFiles([
    'efg_internal_config' => 'PBDKN/Efgco4/Resources/contao/templates/internal',
    'efg_internal_dca_formdata' => 'PBDKN/Efgco4/Resources/contao/templates/internal',
    'efg_internal_modules' => 'PBDKN/Efgco4/Resources/contao/templates/internal',
    'edit_fd_default' => 'PBDKN/Efgco4/Resources/contao/templates/forms',
    'form_efg_imageselect' => 'PBDKN/Efgco4/Resources/contao/templates/forms',
    'form_paginator' => 'PBDKN/Efgco4/Resources/contao/templates/forms',
    'info_fd_simple_default' => 'PBDKN/Efgco4/Resources/contao/templates/listing/info',
    'info_fd_table_default' => 'PBDKN/Efgco4/Resources/contao/templates/listing/info',
    'list_fd_simple_default' => 'PBDKN/Efgco4/Resources/contao/templates/listing/list',
    'list_fd_table_default' => 'PBDKN/Efgco4/Resources/contao/templates/listing/list',
]);
