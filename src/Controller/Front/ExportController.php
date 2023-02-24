<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use PrestaShop\Module\Psgdpr\Domain\Export\Exception\ExportException;
use PrestaShop\Module\Psgdpr\Domain\Export\Export;
use PrestaShop\Module\Psgdpr\Domain\Logger\Command\AddLogCommand;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;

class ExportController {
  /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var Customer
     */
    public $customer;

    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
        $this->customer = Context::getContext()->customer;
    }

    /**
     * @throws PrestaShopDatabaseException
     */
    public function initContent()
    {
        if ($this->customerIsAuthenticated() === false) {
            Tools::redirect('connexion?back=my-account');
        }

        $this->commandBus->handle(
            new AddLogCommand($this->customer->id, 'exportCsv', 0)
        );

        try {
            $customerExport = new Export($this->customer->id);
            $customerExport->toCsv();
        } catch (ExportException $e) {
            exit('A problem occurred while exporting customer please try again');
        }
    }

    /**
     * @return bool
     */
    private function customerIsAuthenticated(): bool
    {
        $secure_key = sha1($this->customer->secure_key);
        $token = Tools::getValue('psgdpr_token');

        if ($this->customer->isLogged() === false || !isset($token) || $token != $secure_key) {
            return false;
        }

        return true;
    }
}
