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

namespace PrestaShop\Module\Psgdpr\Service;

use PrestaShop\Module\Psgdpr\Entity\Log;
use PrestaShop\Module\Psgdpr\Exception\Logger\AddLogException;

use PrestaShop\Module\Psgdpr\Repository\LoggerRepository;

class LoggerService
{
    CONST REQUEST_TYPE_EXPORT_CSV = 1;
    CONST REQUEST_TYPE_EXPORT_PDF = 2;
    CONST REQUEST_TYPE_DELETE_ACCOUNT = 3;
    CONST REQUEST_TYPE_CONSENT_COLLECTING = 4;

    /**
     * @var LoggerRepository
     */
    private $LoggerRepository;

    public function __construct(LoggerRepository $LoggerRepository)
    {
        $this->LoggerRepository = $LoggerRepository;
    }

    /**
     * Create log
     *
     * @param int $customerId
     * @param int $requestType
     * @param int $moduleId
     * @param string $clientName
     * @param int $guestId
     *
     * @throws AddLogException
     *
     * @return void
     */
    public function createLog(int $customerId, int $requestType, int $moduleId, int $guestId = 0, string $clientName = ''): void
    {
        try {
            $log = new Log();
            $log->setCustomerId($customerId);
            $log->setRequestType($requestType);
            $log->setModuleId($moduleId);
            $log->setGuestId($guestId);
            $log->setClientName($clientName);
            $this->LoggerRepository->add($log);
        } catch (AddLogException $e) {
            throw new AddLogException($e->getMessage());
        }
    }

    /**
     * Get logs
     *
     * @return array
     */
    public function getLogs(): array
    {
        return $this->LoggerRepository->findAll();
    }
}
