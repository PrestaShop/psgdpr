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
class PsgdprLog
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
     * @ORM\Column(name="id_customer", type="integer", length=10, nullable=false)
     */
    private $customerId;

    /**
     * @var int
     *
     * @ORM\Column(name="id_guest", type="integer", length=10, nullable=false)
     */
    private $guestId;

    /**
     * @var string
     *
     * @ORM\Column(name="client_name", type="string", length=255, nullable=false)
     */
    private $clientName;

    /**
     * @var int
     *
     * @ORM\Column(name="id_module", type="integer", nullable=false)
     */
    private $moduleId;

    /**
     * @var int
     *
     * @ORM\Column(name="request_type", type="integer", nullable=false)
     */
    private $requestType;

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
    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    /**
     * @param CustomerId $customerId
     *
     * @return PSGDPRLog
     */
    public function setCustomerId(CustomerId $customerId): PSGDPRLog
    {
        $this->customerId = $customerId->getValue();

        return $this;
    }

    /**
     * @return int
     */
    public function getGuestId(): int
    {
        return $this->guestId;
    }

    /**
     * @param int $guestId
     *
     * @return PSGDPRLog
     */
    public function setGuestId(int $guestId): PSGDPRLog
    {
        $this->guestId = $guestId;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientName(): string
    {
        return $this->clientName;
    }

    /**
     * @param string $clientName
     *
     * @return PSGDPRLog
     */
    public function setClientName(string $clientName): PSGDPRLog
    {
        $this->clientName = $clientName;

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
     * @return PSGDPRLog
     */
    public function setModuleId(int $moduleId): PSGDPRLog
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    /**
     * @param int $requestType
     *
     * @return PSGDPRLog
     */
    public function setRequestType(int $requestType): PSGDPRLog
    {
        $this->requestType = $requestType;

        return $this;
    }

    /**
     * @return int
     */
    public function getRequestType(): int
    {
        return $this->requestType;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): Datetime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     *
     * @return PSGDPRLog
     */
    private function setCreatedAt(DateTime $createdAt): PSGDPRLog
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime $updatedAt
     *
     * @return PSGDPRLog
     */
    private function setUpdatedAt(DateTime $updatedAt): PSGDPRLog
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
