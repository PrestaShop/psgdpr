<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Psgdpr\Entity;

use Doctrine\ORM\Mapping as ORM;
use PrestaShopBundle\Entity\Lang;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class PsgdprConsentLang
{
    /**
     * @var PsgdprConsent
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PrestaShop\Module\Psgdpr\Entity\PsgdprConsent", inversedBy="consentLangs", cascade={"persist", "merge", "remove"})
     * @ORM\JoinColumn(name="id_gdpr_consent", referencedColumnName="id_gdpr_consent", nullable=false)
     */
    private $consent;

    /**
     * @var Lang
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PrestaShopBundle\Entity\Lang")
     * @ORM\JoinColumn(name="id_lang", referencedColumnName="id_lang", nullable=false, onDelete="CASCADE")
     */
    private $lang;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="string", length=255, nullable=false)
     */
    private $message;

    /**
     * @var int
     * @ORM\Column(name="id_shop", type="integer", length=10, nullable=false)
     */
    private $shopId;

    /**
     * @return PsgdprConsent
     */
    public function getConsent(): PsgdprConsent
    {
        return $this->consent;
    }

    /**
     * @param PsgdprConsent $consent
     *
     * @return $this
     */
    public function setConsent(PsgdprConsent $consent): self
    {
        $this->consent = $consent;

        return $this;
    }

    /**
     * @return Lang
     */
    public function getLang(): Lang
    {
        return $this->lang;
    }

    /**
     * @param Lang $lang
     *
     * @return $this
     */
    public function setLang(Lang $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     *
     * @return $this
     */
    public function setShopId(int $shopId): self
    {
        $this->shopId = $shopId;

        return $this;
    }
}
