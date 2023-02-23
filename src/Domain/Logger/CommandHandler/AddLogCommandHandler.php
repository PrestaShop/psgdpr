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

namespace PrestaShop\Module\Psgdpr\Domain\Logger\CommandHandler;

use PrestaShop\Module\Psgdpr\Domain\Logger\Command\AddLogCommand;
use PrestaShop\Module\Psgdpr\Domain\Logger\Exception\AddLogException;
use PrestaShop\Module\PSGDPR\Entity\Log;
use PrestaShop\Module\Psgdpr\Infrastructure\Repository\LoggerRepository;

class AddLogCommandHandler
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
     * Handle add log command
     *
     * @param AddLogCommand $command
     *
     * @throws AddLogException
     *
     * @return void
     */
    public function handle(AddLogCommand $command): void
    {
        try {
            $log = new Log();
            $log->setCustomerId($command->getCustomerId()->getValue());
            $log->setGuestId($command->getGuestId()->getValue());
            $log->setClientName($command->getClientName()->getValue());
            $log->setModuleId($command->getModuleId()->getValue());
            $log->setRequestType($command->getRequestType()->getValue());

            $this->LoggerRepository->add($log);
        } catch (\Exception $e) {
            throw new AddLogException($e->getMessage());
        }
    }
}
