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

use PrestaShop\Module\Psgdpr\Exception\Customer\ExportException;
use PrestaShop\Module\Psgdpr\Service\CustomerService;
use PrestaShop\Module\Psgdpr\Service\ExportService;
use PrestaShop\Module\Psgdpr\Service\LoggerService;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class psgdprExportDataToCsvModuleFrontController extends ModuleFrontController
{
    /**
     * @var Psgdpr
     */
    public $module;

    /**
     * @var ExportService
     */
    private $exportService;

    /**
     * @var LoggerService
     */
    private $loggerService;

    public function initContent()
    {
        parent::initContent();

        $this->exportService = $this->module->get('psgdpr.service.export');
        $this->loggerService = $this->module->get('psgdpr.service.logger');

        if ($this->customerIsAuthenticated() === false) {
            Tools::redirect('connexion?back=my-account');
        }

        $customer = Context::getContext()->customer;

        //$this->loggerService->createLog(new CustomerId($customer->id), LoggerService::REQUEST_TYPE_EXPORT_CSV, 0);

        try {
            $csvFile = $this->exportService->transformViewableCustomerToCsv($customer);
            $csvName = $customer->id . '_' . date('Y-m-d_His') . '.csv';

            $response = new Response($csvFile);
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $csvName . '";');
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Transfer-Encoding', 'binary');

            $response->send();

            exit();
        } catch (ExportException $e) {
            throw new ExportException('A problem occurred while exporting customer please try again');
        }
    }

    /**
     * @return bool
     */
    private function customerIsAuthenticated(): bool
    {
        $customer = Context::getContext()->customer;
        $secure_key = sha1($customer->secure_key);
        $token = Tools::getValue('psgdpr_token');

        if ($customer->isLogged() === false || !isset($token) || $token != $secure_key) {
            return false;
        }

        return true;
    }
}
