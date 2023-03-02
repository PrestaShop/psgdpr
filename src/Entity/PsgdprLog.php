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
use InvalidArgumentException;
use \PrestaShop\Module\Psgdpr\Exception\Logger\RequestTypeValidityException;
use PrestaShop\Module\Psgdpr\Service\LoggerService;
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
     * @return PsgdprLog
     */
    public function setCustomerId(CustomerId $customerId): PsgdprLog
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
     * @return PsgdprLog
     */
    public function setGuestId(int $guestId): PsgdprLog
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
     * @return PsgdprLog
     */
    public function setClientName(string $clientName): PsgdprLog
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
     * @return PsgdprLog
     */
    public function setModuleId(int $moduleId): PsgdprLog
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    /**
     * @param int $requestType
     *
     * @return PsgdprLog
     */
    public function setRequestType(int $requestType): PsgdprLog
    {
        $this->assertRequestTypeIsValid($requestType);

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
     * @return mixed
     */
    public function getCreatedAt(): mixed
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     *
     * @return PsgdprLog
     */
    private function setCreatedAt(DateTime $createdAt): PsgdprLog
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
     * @return PsgdprLog
     */
    private function setUpdatedAt(DateTime $updatedAt): PsgdprLog
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

    /**
     * Asserts that request type is valid
     *
     * @param int $requestType
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertRequestTypeIsValid(int $requestType): void
    {
        $validTypes = [
            LoggerService::REQUEST_TYPE_EXPORT_CSV,
            LoggerService::REQUEST_TYPE_EXPORT_PDF,
            LoggerService::REQUEST_TYPE_CONSENT_COLLECTING,
            LoggerService::REQUEST_TYPE_DELETE_ACCOUNT
        ];

        if (!in_array($requestType, $validTypes)) {
            throw new RequestTypeValidityException(sprintf('Invalid request type %s', $requestType));
        }
    }
}
