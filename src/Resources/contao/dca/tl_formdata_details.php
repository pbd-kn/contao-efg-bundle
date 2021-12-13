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
 *
 *  extended by insert_tag  {{efg_insert::formalias::aliasvalue::column(::format)}}
 *  extended using sendto by selection for sending Mail to additional receipients
 *
 */

/*
 * Table tl_formdata_details
 */
$GLOBALS['TL_DCA']['tl_formdata_details'] = [
    // Config
    'config' => [
        'dataContainer' => 'Formdata',
        'ptable' => 'tl_formdata',
        'closed' => true,
        'notEditable' => false,
        'enableVersioning' => false,
        'doNotCopyRecords' => false,
        'doNotDeleteRecords' => false,
        'switchToEdit' => false,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'ff_name' => 'index',
            ],
        ],
    ],
    // List
    'list' => [
        'sorting' => [
            'mode' => 4,
            'panelLayout' => 'search,filter',
            'headerFields' => ['form', 'date', 'ip', 'be_notes'],
            'child_record_callback' => ['tl_formdata_details', 'listFormdata'],
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],

        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_formdata']['edit'],
                'href' => 'act=edit',
                'icon' => 'bundles/contaoefgco4/icons/edit.gif',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_formdata']['show'],
                'href' => 'act=show',
                'icon' => 'bundles/contaoefgco4/icons/show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => 'pid,id,ff_name,value',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'ff_id' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'ff_name' => [
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'value' => [
            'label' => ['Value', 'Wert des tl_formdata_details-Datensatzes'],
            'inputType' => 'text',
            'exclude' => false,
            'search' => false,
            'sorting' => false,
            'filter' => false,
            'sql' => 'text NULL',
        ],
    ],
];
