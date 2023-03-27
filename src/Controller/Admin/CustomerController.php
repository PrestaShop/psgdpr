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

use Exception;
use Order;
use PrestaShop\Module\Psgdpr\Repository\OrderInvoiceRepository;
use PrestaShop\Module\Psgdpr\Service\BackResponder\BackResponderFactory;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Customer\Query\SearchCustomers;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends FrameworkBundleAdminController
{
    /**
     * @var CommandBusInterface
     */
    private $queryBus;

    /**
     * @var OrderInvoiceRepository
     */
    private $orderInvoiceRepository;

    /**
     * @var BackResponderFactory
     */
    private $BackResponderFactory;

    /**
     * @param CommandBusInterface $queryBus
     * @param OrderInvoiceRepository $orderInvoiceRepository
     * @param BackResponderFactory $BackResponderFactory
     *
     * @return void
     */
    public function __construct(
        CommandBusInterface $queryBus,
        OrderInvoiceRepository $orderInvoiceRepository,
        BackResponderFactory $BackResponderFactory
    ) {
        $this->queryBus = $queryBus;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->BackResponderFactory = $BackResponderFactory;
    }

    /**
     * Search customer by email
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function searchCustomers(Request $request): JsonResponse
    {
        $response = new JsonResponse();

        $requestBodyContent = $request->getContent();
        $phrase = strval($requestBodyContent['phrase']);

        if (!isset($phrase) && empty($phrase)) {
            return $response
                ->setStatusCode(400)
                ->setData(['message' => 'Property phrase is missing or empty.'])
            ;
        }

        /** @var array $customerList */
        $customerList = $this->queryBus->handle(new SearchCustomers([$phrase]));

        if (empty($customerList)) {
            return $response
                ->setStatusCode(404)
                ->setContent(['message' => 'Customer not found'])
            ;
        }

        $customerList = array_map(function ($customer) {
            return [
                'idCustomer' => $customer['id_customer'],
                'firstname' => $customer['firstname'],
                'lastname' => $customer['lastname'],
                'email' => $customer['email'],
                'nb_orders' => Order::getCustomerNbOrders($customer['id_customer']),
                'customerData' => [],
            ];
        }, $customerList);

        $response->setContent($customerList);

        return $response;
    }

    /**
     * Delete User data by customer id
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function deleteCustomerData(Request $request): JsonResponse
    {
        $response = new JsonResponse();

        $requestBodyContent = $request->getContent();
        $dataTypeRequested = strval($requestBodyContent['dataTypeRequested']);
        $customerData = strval($requestBodyContent['customerData']);

        try {
            $customerDataResponderStrategy = $this->BackResponderFactory->getStrategyByType($dataTypeRequested);

            return $customerDataResponderStrategy->delete($customerData);
        } catch (Exception $e) {
            return $response
                ->setStatusCode(500)
                ->setContent(['message' => 'A problem occurred while deleting please try again'])
            ;
        }
    }

    /**
     * Get user data for the given customer
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getCustomerData(Request $request): JsonResponse
    {
        $response = new JsonResponse();

        $dataTypeRequested = strval($requestBodyContent['dataTypeRequested']);
        $customerData = strval($requestBodyContent['customerData']);

        try {
            $customerDataResponderStrategy = $this->BackResponderFactory->getStrategyByType($dataTypeRequested);

            return $customerDataResponderStrategy->export($customerData);
        } catch (Exception $e) {
            return $this->json(['message' => 'A problem occurred while retrieving customer data please try again'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate link to DownloadCustomerInvoicesController in order to download invoices
     *
     * @param Request $request
     * @param int $customerId
     *
     * @return JsonResponse
     */
    public function getDownloadInvoicesLinkByCustomerId(Request $request, int $customerId): JsonResponse
    {
        $response = new JsonResponse();

        $customerId = new CustomerId($customerId);
        $customerHasInvoices = $this->orderInvoiceRepository->findIfInvoicesExistByCustomerId($customerId);

        if ($customerHasInvoices) {
            return $response->setContent([
                'invoicesDownloadLink' => $this->generateUrl('psgdpr_api_download_customer_invoices', ['customerId' => $customerId->getValue()]),
            ]);
        } else {
            return $this->json(['message' => 'There is no invoices found for this customer'], 404);
        }
    }
}
