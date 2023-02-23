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

namespace PrestaShop\Module\Psgdpr\Domain\Logger\Command;

use PrestaShop\Module\Psgdpr\Domain\Logger\ValueObject\ClientName;
use PrestaShop\Module\Psgdpr\Domain\Logger\ValueObject\GuestId;
use PrestaShop\Module\Psgdpr\Domain\Logger\ValueObject\ModuleId;
use PrestaShop\Module\Psgdpr\Domain\Logger\ValueObject\RequestType;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;

class AddLogCommand
{
    /**
     * @var CustomerId
     */
    private $customerId;

    /**
     * @var GuestId
     */
    private $guestId;

    /**
     * @var string
     */
    private $clientName;

    /**
     * @var int
     */
    private $moduleId;

    /**
     * @var RequestType
     */
    private $requestType;

    /**
     * AddLogCommand constructor.
     *
     * @param int $customerId
     * @param string $requestType
     */
    public function __construct(
        int $customerId,
        int $guestId,
        string $clientName,
        int $moduleId,
        int $requestType
    ) {
        $this->customerId = new CustomerId($customerId);
        $this->guestId = new GuestId($guestId);
        $this->clientName = new ClientName($clientName);
        $this->moduleId = new ModuleId($moduleId);
        $this->requestType = new RequestType($requestType);
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return GuestId
     */
    public function getGuestId()
    {
        return $this->guestId;
    }

    /**
     * @return ClientName
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * @return ModuleId
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }

    /**
     * @return RequestType
     */
    public function getRequestType()
    {
        return $this->requestType;
    }
}
