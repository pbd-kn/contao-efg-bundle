<?php

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
    private $debugMode = null;

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
