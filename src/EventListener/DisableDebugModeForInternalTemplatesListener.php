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

namespace PBDKN\Efgco4\EventListener;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Template;

/**
 * This disables the debug mode while parsing an efg_internal_* back end template.
 * Otherwise Contao will add "TEMPLATE START" and "TEMPLATE END" comments.
 */
class DisableDebugModeForInternalTemplatesListener
{
    /**
     * @var bool|null
     */
    private $debugMode;

    /**
     * @Hook("parseTemplate")
     */
    public function onParseTemplate(Template $template): void
    {
        if (!Config::get('debugMode') || !$template instanceof BackendTemplate) {
            return;
        }

        if (0 !== stripos($template->getName(), 'efg_internal_')) {
            return;
        }

        // Disable debug mode
        $this->debugMode = Config::get('debugMode');
        Config::set('debugMode', false);
    }

    /**
     * @Hook("parseBackendTemplate")
     */
    public function onParseBackendTemplate(string $buffer, string $template): string
    {
        // Restore debug mode
        if (null !== $this->debugMode && 0 === stripos($template, 'efg_internal_')) {
            Config::set('debugMode', $this->debugMode);
            $this->debugMode = null;
        }

        return $buffer;
    }
}
