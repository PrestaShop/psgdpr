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
use Doctrine\ORM\Mapping as ORM;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;

/**
 * @ORM\Table()
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class PsgdprConsent
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_gdpr_log", type="integer", length=10, nullable=false)
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
    private $active;

    /**
     * @var bool
     *
     * @ORM\Column(name="error", type="boolean", nullable=false)
     */
    private $error;

    /**
     * @var string
     *
     * @ORM\Column(name="error_message", type="string", length=255, nullable=false)
     */
    private $errorMessage;


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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return PsgdprConsent
     */
    public function setModuleId(int $moduleId): PsgdprConsent
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
     * @return PsgdprConsent
     */
    public function setActive(bool $active): PsgdprConsent
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
     * @return PsgdprConsent
     */
    public function setError(bool $error): PsgdprConsent
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
     * @return PsgdprConsent
     */
    public function setErrorMessage(string $errorMessage): PsgdprConsent
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt(): mixed
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     *
     * @return PsgdprConsent
     */
    private function setCreatedAt(DateTime $createdAt): PsgdprConsent
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
     * @return PsgdprConsent
     */
    private function setUpdatedAt(DateTime $updatedAt): PsgdprConsent
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
