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

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="PrestaShop\Module\Psgdpr\Repository\ConsentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class PsgdprConsent
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id_gdpr_consent", type="integer", length=10, nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_module", type="integer", nullable=false)
     */
    private $moduleId;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="error", type="boolean", nullable=false)
     */
    private $error = false;

    /**
     * @var string
     *
     * @ORM\Column(name="error_message", type="string", length=255, nullable=false)
     */
    private $errorMessage = '';

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="PrestaShop\Module\Psgdpr\Entity\PsgdprConsentLang", cascade={"persist", "remove"}, mappedBy="consent")
     */
    private $consentLangs;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_add", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_upd", type="datetime", nullable=false)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->consentLangs = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return ArrayCollection|PersistentCollection
     */
    public function getConsentLangs()
    {
        return $this->consentLangs;
    }

    /**
     * @param ArrayCollection $consentLangs
     *
     * @return $this
     */
    public function setConsentLangs(ArrayCollection $consentLangs): self
    {
        $this->consentLangs = $consentLangs;

        return $this;
    }

    /**
     * @param PsgdprConsentLang $consentLang
     *
     * @return $this
     */
    public function addConsentLang(PsgdprConsentLang $consentLang): self
    {
        $consentLang->setConsent($this);

        $this->consentLangs->add($consentLang);

        return $this;
    }

    /**
     * @return int
     */
    public function getModuleId(): int
    {
        return $this->moduleId;
    }

    /**
     * @param int $moduleId
     *
     * @return $this
     */
    public function setModuleId(int $moduleId): self
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return $this->error;
    }

    /**
     * @param bool $error
     *
     * @return $this
     */
    public function setError(bool $error): self
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     *
     * @return $this
     */
    public function setErrorMessage(string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getConsentContent(): string
    {
        if ($this->consentLangs->count() <= 0) {
            return '';
        }

        $consentLang = $this->consentLangs->first();

        return $consentLang->getContent();
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     *
     * @return $this
     */
    private function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime $updatedAt
     *
     * @return $this
     */
    private function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps(): void
    {
        $dateTimeNow = new DateTime('now');

        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt($dateTimeNow);
        }

        $this->setUpdatedAt($dateTimeNow);
    }
}
