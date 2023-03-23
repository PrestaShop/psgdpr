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

use Exception;
use PrestaShop\Module\Psgdpr\Entity\PsgdprLog;
use PrestaShop\Module\Psgdpr\Exception\Logger\AddLogException;
use PrestaShop\Module\Psgdpr\Repository\LoggerRepository;

class LoggerService
{
    const REQUEST_TYPE_CONSENT_COLLECTING = 1;
    const REQUEST_TYPE_EXPORT_PDF = 2;
    const REQUEST_TYPE_EXPORT_CSV = 3;
    const REQUEST_TYPE_DELETE = 4;

    /**
     * @var LoggerRepository
     */
    private $LoggerRepository;

    /**
     * @param LoggerRepository $LoggerRepository
     *
     * @return void
     */
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
     * @param int $guestId
     * @param string $clientData
     *
     * @throws Exception
     *
     * @return void
     */
    public function createLog(int $customerId, int $requestType, int $moduleId, int $guestId = 0, $clientData = ''): void
    {
        try {
            $log = new PsgdprLog();
            $log->setCustomerId($customerId);
            $log->setRequestType($requestType);
            $log->setModuleId($moduleId);
            $log->setGuestId($guestId);
            $log->setClientData($clientData);
            $this->LoggerRepository->add($log);
        } catch (Exception $e) {
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
