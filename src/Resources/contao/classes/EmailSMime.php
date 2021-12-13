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

namespace PBDKN\Efgco4\Resources\contao\classes;

/**
 * Class EmailSMime.
 *
 * @version 1.0
 *
 * @copyright 1601.communication gmbh <lb@1601.com>
 * @ToDo: Sign messages
 */
class EmailSMime extends \Contao\Email
{
    protected $SwiftSigner;

    protected $privateCertificate;

    protected $publicCertificate;

    protected $certPassphrase;

    public function __construct(\Swift_Mailer $objMailer = null)
    {
        parent::__construct($objMailer);

        $this->SwiftSigner = (new \Swift_Signers_SMimeSigner());
    }

    public function setPrivateCertPath(string $privPath)
    {
        if (!file_exists($privPath)) {
            return false;
        }

        $this->privateCertificate = $privPath;

        return $this->privateCertificate;
    }

    public function setPublicCertPath(string $pubPath)
    {
        if (!file_exists($pubPath)) {
            return false;
        }

        $this->publicCertificate = $pubPath;

        return $this->publicCertificate;
    }

    public function setCertPassphrase(string $passphrase)
    {
        $this->certPassphrase = $passphrase;

        return $this->certPassphrase;
    }

    public function sendSMimeTo()
    {
        $arrRecipients = $this->compileRecipients(\func_get_args());

        if (empty($arrRecipients)) {
            return false;
        }

        $this->objMessage->setTo($arrRecipients);
        $this->objMessage->setCharset($this->strCharset);

        // Default subject
        if ('' === $this->strSubject) {
            $this->strSubject = 'No subject';
        }

        $this->objMessage->setSubject($this->strSubject);

        // HTML e-mail
        if ('' !== $this->strHtml) {
            // Embed images
            if ($this->blnEmbedImages) {
                if ('' === $this->strImageDir) {
                    $this->strImageDir = \System::getContainer()->getParameter('kernel.project_dir').'/';
                }

                $arrCid = [];
                $arrMatches = [];
                $strBase = \Environment::get('base');

                // Thanks to @ofriedrich and @aschempp (see #4562)
                preg_match_all('/<[a-z][a-z0-9]*\b[^>]*((src=|background=|url\()["\']??)(.+\.(jpe?g|png|gif|bmp|tiff?|swf))(["\' ]??(\)??))[^>]*>/Ui', $this->strHtml, $arrMatches);

                // Check for internal images
                if (!empty($arrMatches) && isset($arrMatches[0])) {
                    for ($i = 0, $c = \count($arrMatches[0]); $i < $c; ++$i) {
                        $url = $arrMatches[3][$i];

                        // Try to remove the base URL
                        $src = str_replace($strBase, '', $url);
                        $src = rawurldecode($src); // see #3713

                        // Embed the image if the URL is now relative
                        if (!preg_match('@^https?://@', $src) && file_exists($this->strImageDir.$src)) {
                            if (!isset($arrCid[$src])) {
                                $arrCid[$src] = $this->objMessage->embed(\Swift_EmbeddedFile::fromPath($this->strImageDir.$src));
                            }

                            $this->strHtml = str_replace($arrMatches[1][$i].$arrMatches[3][$i].$arrMatches[5][$i], $arrMatches[1][$i].$arrCid[$src].$arrMatches[5][$i], $this->strHtml);
                        }
                    }
                }
            }

            $this->objMessage->setBody($this->strHtml, 'text/html');
        }

        // Text content
        if ('' !== $this->strText) {
            if ('' !== $this->strHtml) {
                $this->objMessage->addPart($this->strText, 'text/plain');
            } else {
                $this->objMessage->setBody($this->strText, 'text/plain');
            }
        }

        // Add the administrator e-mail as default sender
        if ('' === $this->strSender) {
            [$this->strSenderName, $this->strSender] = \StringUtil::splitFriendlyEmail(\Config::get('adminEmail'));
        }

        // Sender
        if ('' !== $this->strSenderName) {
            $this->objMessage->setFrom([$this->strSender => $this->strSenderName]);
        } else {
            $this->objMessage->setFrom($this->strSender);
        }

        // Set the return path (see #5004)
        $this->objMessage->setReturnPath($this->strSender);

        /*
         * @ToDo Sign Message
         */
        /*$this->SwiftSigner->setSignCertificate($this->publicCertificate , $this->privateCertificate, $this->certPassphrase);*/

        $this->SwiftSigner->setEncryptCertificate($this->publicCertificate);

        $this->objMessage->attachSigner($this->SwiftSigner);

        // Send the e-mail
        $intSent = $this->objMailer->send($this->objMessage, $this->arrFailures);

        // Log failures
        if (!empty($this->arrFailures)) {
            \System::log('E-mail address rejected: '.implode(', ', $this->arrFailures), __METHOD__, $this->strLogFile);
        }

        // Return if no e-mails have been sent
        if ($intSent < 1) {
            return false;
        }

        $arrCc = $this->objMessage->getCc();
        $arrBcc = $this->objMessage->getBcc();

        // Add a log entry
        $strMessage = 'An e-mail has been sent to '.implode(', ', array_keys($this->objMessage->getTo()));

        if (!empty($arrCc)) {
            $strMessage .= ', CC to '.implode(', ', array_keys($arrCc));
        }

        if (!empty($arrBcc)) {
            $strMessage .= ', BCC to '.implode(', ', array_keys($arrBcc));
        }

        \System::log($strMessage, __METHOD__, $this->strLogFile);

        return true;
    }
}
