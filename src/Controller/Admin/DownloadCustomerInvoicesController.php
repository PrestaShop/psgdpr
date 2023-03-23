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

namespace PrestaShop\Module\Psgdpr\Controller\Admin;

use Context;
use Exception;
use ObjectModel;
use PDF;
use PrestaShop\Module\Psgdpr\Exception\CustomerHasNotInvoicesException;
use PrestaShop\Module\Psgdpr\Exception\DownloadInvoicesFailedException;
use PrestaShop\Module\Psgdpr\Repository\OrderInvoiceRepository;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;

class DownloadCustomerInvoicesController extends FrameworkBundleAdminController
{
    /**
     * @var OrderInvoiceRepository
     */
    private $orderInvoiceRepository;

    public function __construct(OrderInvoiceRepository $orderInvoiceRepository)
    {
        $this->orderInvoiceRepository = $orderInvoiceRepository;
    }

    /**
     * Endpoint to retrieve all pdf invoices from a specific customer
     *
     * @param Request $request
     * @param int $customerId
     *
     * @throws DownloadInvoicesFailedException
     */
    public function downloadInvoicesByCustomerId(Request $request, int $customerId)
    {
        $customerId = new CustomerId($customerId);

        $this->assertThatCustomerHasInvoicesBeforeDownload($customerId);

        try {
            $orderInvoiceList = $this->orderInvoiceRepository->findAllInvoicesByCustomerId($customerId);
            $orderInvoiceCollection = ObjectModel::hydrateCollection('OrderInvoice', $orderInvoiceList);

            $pdf = new PDF($orderInvoiceCollection, PDF::TEMPLATE_INVOICE, Context::getContext()->smarty);
            $pdf->render();
        } catch (Exception $e) {
            throw new DownloadInvoicesFailedException('An error occured while trying to download the invoices');
        }
    }

    /**
     * @param CustomerId $customerId
     *
     * @throws CustomerHasNotInvoicesException
     */
    private function assertThatCustomerHasInvoicesBeforeDownload(CustomerId $customerId)
    {
        $customerHasInvoices = $this->orderInvoiceRepository->findIfInvoicesExistByCustomerId($customerId);

        if (!$customerHasInvoices) {
            throw new CustomerHasNotInvoicesException('The given customer has not any invoices associated to his account');
        }
    }
}
