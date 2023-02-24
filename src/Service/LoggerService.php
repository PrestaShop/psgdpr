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

use PrestaShop\Module\Psgdpr\Exception\Logger\AddLogException;
use PrestaShop\Module\PSGDPR\Entity\Log;
use PrestaShop\Module\Psgdpr\Repository\LoggerRepository;

class LoggerService
{
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
     * @param int $guestId
     * @param string $clientName
     * @param int $moduleId
     * @param int $requestType
     *
     * @throws AddLogException
     *
     * @return void
     */
    public function createLog(int $customerId, int $guestId, string $clientName, int $moduleId, int $requestType): void
    {
        try {
            $log = new Log();
            $log->setCustomerId($customerId);
            $log->setGuestId($guestId);
            $log->setClientName($clientName);
            $log->setModuleId($moduleId);
            $log->setRequestType($requestType);

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
