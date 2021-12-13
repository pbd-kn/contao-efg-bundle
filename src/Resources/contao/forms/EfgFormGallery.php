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

/**
 * Namespace.
 */

namespace PBDKN\Efgco4\Resources\contao\forms;

/**
 * Class EfgFormGallery
 * based on ContentGallery by Leo Feyer.
 *
 * Renders gallery with radio buttons or checkboxess
 *
 * @copyright  Thomas Kuhn 2007-2014
 */
class EfgFormGallery extends \ContentElement
{
    /**
     * Files object.
     *
     * @var \FilesModel
     */
    protected $objFiles;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'form_efg_imageselect';

    protected $widget;

    /**
     * Initialize the object.
     *
     * @param \Efg\Widget|\Widget $objWidget
     * @param array               $arrConfig
     */
    public function __construct(\Widget $objWidget, $arrConfig)
    {
        $this->widget = $objWidget;
        $this->multiSRC = $arrConfig['efgMultiSRC'];
        $this->efgImageMultiple = $arrConfig['efgImageMultiple']; // $objWidget->efgImageMultiple;
        $this->efgImageUseHomeDir = $arrConfig['efgImageUseHomeDir']; // $objWidget->efgImageUseHomeDir;
        $this->size = $arrConfig['efgImageSize']; // $objWidget->efgImageSize;
        $this->fullsize = $arrConfig['efgImageFullsize']; // $objWidget->efgImageFullsize;
        $this->sortBy = (!empty($arrConfig['efgImageSortBy']) ? $arrConfig['efgImageSortBy'] : 'name_asc');
        $this->perRow = ((int) ($arrConfig['efgImagePerRow']) > 0) ? (int) ($arrConfig['efgImagePerRow']) : 4;
        $this->perPage = 0;
        $this->imagemargin = $arrConfig['efgImageMargin']; // $objWidget->efgImageMargin;

        $this->arrData = $arrConfig;
    }

    /**
     * Set an object property.
     *
     * @param $strKey
     * @param $varValue
     */
    public function __set($strKey, $varValue): void
    {
        switch ($strKey) {
            case 'efgImageMultiple':
                $this->efgImageMultiple = \strlen($varValue) ? true : false;
                break;

            case 'efgImageUseHomeDir':
                $this->efgImageUseHomeDir = \strlen($varValue) ? true : false;
                break;

            case 'multiSRC':
                $this->multiSRC = $varValue;
                break;

            case 'size':
                $this->size = $varValue;
                break;

            case 'sortBy':
                $this->sortBy = $varValue;
                break;

            case 'perRow':
                $this->perRow = $varValue;
                break;

            case 'perPage':
                $this->perPage = $varValue;
                break;

            case 'imagemargin':
                $this->imagemargin = $varValue;
                break;

            case 'fullsize':
                $this->fullsize = $varValue;
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    /**
     * Return if there are no files.
     *
     * @return string
     */
    public function generate()
    {
        // Use the home directory of the current user as file source
        if ($this->efgImageUseHomeDir && FE_USER_LOGGED_IN) {
            $this->import('FrontendUser', 'User');

            if ($this->User->assignDir && $this->User->homeDir) {
                $this->multiSRC = [$this->User->homeDir];
            }
        } else {
            $this->multiSRC = deserialize($this->multiSRC, true);
        }

        // Return if there are no files
        if (!\is_array($this->multiSRC) || empty($this->multiSRC)) {
            return '';
        }

        foreach ($this->multiSRC as $k => $v) {
            if (\Validator::isUuid($v)) {
                if (16 === \strlen($v)) {
                    $this->multiSRC[$k] = \StringUtil::binToUuid($v);
                }
            }
        }

        // Get the file entries from the database
        $this->objFiles = \FilesModel::findMultipleByUuids($this->multiSRC);

        if (null === $this->objFiles) {
            if (!\Validator::isUuid($this->multiSRC[0])) {
                return '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
            }

            return '';
        }

        return parent::generate();
    }

    /**
     * Generate the gallery.
     */
    protected function compile(): void
    {
        global $objPage;

        $images = [];
        $auxDate = [];
        $auxId = [];
        $objFiles = $this->objFiles;

        // Get all images
        while ($objFiles->next()) {
            // Continue if the files has been processed or does not exist
            if (isset($images[$objFiles->path]) || !file_exists(\System::getContainer()->getParameter('kernel.project_dir').'/'.$objFiles->path)) {
                continue;
            }

            // Single files
            if ('file' === $objFiles->type) {
                $objFile = new \File($objFiles->path, true);

                if (!$objFile->isGdImage) {
                    continue;
                }

                $arrMeta = $this->getMetaData($objFiles->meta, $objPage->language);

                // Use the file name as title if none is given
                if ('' === $arrMeta['title']) {
                    $arrMeta['title'] = specialchars(str_replace('_', ' ', $objFile->filename));
                }

                // Add the image
                $images[$objFiles->path] = [
                    'id' => $objFiles->id,
                    'uuid' => $objFiles->uuid,
                    'name' => $objFile->basename,
                    'singleSRC' => $objFiles->path,
                    'alt' => $arrMeta['title'],
                    'imageUrl' => $arrMeta['link'],
                    'caption' => $arrMeta['caption'],
                ];

                $auxDate[] = $objFile->mtime;
                $auxId[] = $objFiles->id;
            }

            // Folders
            else {
                $objSubfiles = \FilesModel::findByPid($objFiles->uuid);

                if (null === $objSubfiles) {
                    continue;
                }

                while ($objSubfiles->next()) {
                    // Skip subfolders
                    if ('folder' === $objSubfiles->type) {
                        continue;
                    }

                    $objFile = new \File($objSubfiles->path, true);

                    if (!$objFile->isGdImage) {
                        continue;
                    }

                    $arrMeta = $this->getMetaData($objSubfiles->meta, $objPage->language);

                    // Use the file name as title if none is given
                    if ('' === $arrMeta['title']) {
                        $arrMeta['title'] = specialchars(str_replace('_', ' ', $objFile->filename));
                    }

                    // Add the image
                    $images[$objSubfiles->path] = [
                        'id' => $objSubfiles->id,
                        'uuid' => $objSubfiles->uuid,
                        'name' => $objFile->basename,
                        'singleSRC' => $objSubfiles->path,
                        'alt' => $arrMeta['title'],
                        'imageUrl' => $arrMeta['link'],
                        'caption' => $arrMeta['caption'],
                    ];

                    $auxDate[] = $objFile->mtime;
                    $auxId[] = $objSubfiles->id;
                }
            }
        }

        // Sort array
        switch ($this->sortBy) {
            default:
            case 'name_asc':
                uksort($images, 'basename_natcasecmp');
                break;

            case 'name_desc':
                uksort($images, 'basename_natcasercmp');
                break;

            case 'date_asc':
                array_multisort($images, SORT_NUMERIC, $auxDate, SORT_ASC);
                break;

            case 'date_desc':
                array_multisort($images, SORT_NUMERIC, $auxDate, SORT_DESC);
                break;

            case 'meta': // Backwards compatibility
            case 'custom':
                if ('' !== $this->orderSRC) {
                    $tmp = deserialize($this->orderSRC);

                    if (!empty($tmp) && \is_array($tmp)) {
                        // Remove all values
                        $arrOrder = array_map(function (): void {}, array_flip($tmp));

                        // Move the matching elements to their position in $arrOrder
                        foreach ($images as $k => $v) {
                            if (\array_key_exists($v['uuid'], $arrOrder)) {
                                $arrOrder[$v['uuid']] = $v;
                                unset($images[$k]);
                            }
                        }

                        // Append the left-over images at the end
                        if (!empty($images)) {
                            $arrOrder = array_merge($arrOrder, array_values($images));
                        }

                        // Remove empty (unreplaced) entries
                        $images = array_values(array_filter($arrOrder));
                        unset($arrOrder);
                    }
                }
                break;

            case 'random':
                shuffle($images);
                break;
        }

        $images = array_values($images);

        // Limit the total number of items (see #2652)
        if ($this->numberOfItems > 0) {
            $images = \array_slice($images, 0, $this->numberOfItems);
        }

        $offset = 0;
        $total = \count($images);
        $limit = $total;

        // Pagination
        if ($this->perPage > 0) {
            // Get the current page
            $id = 'page_g'.$this->id;
            $page = \Input::get($id) ?: 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total / $this->perPage), 1)) {
                global $objPage;
                $objPage->noSearch = 1;
                $objPage->cache = 0;

                // Send a 404 header
                header('HTTP/1.1 404 Not Found');

                return;
            }

            // Set limit and offset
            $offset = ($page - 1) * $this->perPage;
            $limit = min($this->perPage + $offset, $total);

            $objPagination = new \Pagination($total, $this->perPage, $GLOBALS['TL_CONFIG']['maxPaginationLinks'], $id);
            $this->Template->pagination = $objPagination->generate("\n  ");
        }

        $rowcount = 0;
        $colwidth = floor(100 / $this->perRow);
        $intMaxWidth = (TL_MODE === 'BE') ? floor((640 / $this->perRow)) : floor(($GLOBALS['TL_CONFIG']['maxImageWidth'] / $this->perRow));
        $strLightboxId = 'lightbox[lb'.$this->id.']';
        $body = [];

        // Rows
        for ($i = $offset; $i < $limit; $i = ($i + $this->perRow)) {
            $class_tr = '';

            if (0 === $rowcount) {
                $class_tr .= ' row_first';
            }

            if (($i + $this->perRow) >= $limit) {
                $class_tr .= ' row_last';
            }

            $class_eo = (($rowcount % 2) === 0) ? ' even' : ' odd';

            // Columns
            for ($j = 0; $j < $this->perRow; ++$j) {
                $class_td = '';

                if (0 === $j) {
                    $class_td = ' col_first';
                }

                if ($j === ($this->perRow - 1)) {
                    $class_td = ' col_last';
                }

                $objCell = new \stdClass();
                $key = 'row_'.$rowcount.$class_tr.$class_eo;

                // Empty cell
                if (!\is_array($images[($i + $j)]) || ($j + $i) >= $limit) {
                    $objCell->class = 'col_'.$j.$class_td;
                } else {
                    // Add size and margin
                    $images[($i + $j)]['size'] = $this->size;
                    $images[($i + $j)]['imagemargin'] = $this->imagemargin;
                    $images[($i + $j)]['fullsize'] = $this->fullsize;

                    $this->addImageToTemplate($objCell, $images[($i + $j)], $intMaxWidth, $strLightboxId);

                    // Add column width and class
                    $objCell->colWidth = $colwidth.'%';
                    $objCell->class = 'col_'.$j.$class_td;
                    $objCell->optId = 'opt_'.$this->widget->id.'_'.($i + $j);
                    $objCell->optName = $this->widget->name;
                    $objCell->srcFile = $images[($i + $j)]['singleSRC'];
                    $objCell->srcFileId = $images[($i + $j)]['id'];
                    $objCell->value = $images[($i + $j)]['id'];

                    $blnChecked = false;
                    if ($this->efgImageMultiple) {
                        if (!\is_array($this->widget->value)) {
                            $this->widget->value = [$this->widget->value];
                        }

                        $blnChecked = (\is_array($this->widget->value) && (\in_array($objCell->srcFile, $this->widget->value, true) || \in_array($objCell->srcFileId, $this->widget->value, true)));
                    } else {
                        $blnChecked = ($this->widget->value === $objCell->srcFile || $this->widget->value === $objCell->srcFileId);
                    }
                    $objCell->checked = ($blnChecked ? ' checked="checked"' : '');
                }

                $body[$key][$j] = $objCell;
            }

            ++$rowcount;
        }

        $this->Template->multiple = ($this->efgImageMultiple) ? true : false;
        $this->Template->body = $body;
        $this->Template->images = $images;
    }
}
