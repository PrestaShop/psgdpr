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
use PrestaShop\Module\Psgdpr\Exception\Customer\DeleteException;
use PrestaShop\Module\Psgdpr\Repository\OrderInvoiceRepository;
use PrestaShop\Module\Psgdpr\Service\CustomerService;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Customer\Query\SearchCustomers;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Service\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends FrameworkBundleAdminController
{
    /**
     * @var CommandBusInterface
     */
    private $queryBus;

    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var OrderInvoiceRepository
     */
    private $orderInvoiceRepository;

    public function __construct(
        CommandBusInterface $queryBus,
        CustomerService $customerService,
        OrderInvoiceRepository $orderInvoiceRepository
    ) {
        $this->queryBus = $queryBus;
        $this->customerService = $customerService;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
    }

    /**
     * Search customer by email
     *
     * @param Request $request
     *
     * @return Response
     */
    public function searchCustomers(Request $request): Response
    {
        $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        if (!isset($requestBodyContent['phrase']) && empty($requestBodyContent['phrase'])) {
            $response->setStatusCode(400);
            $response->setContent(
                json_encode(['message' => 'Property phrase is missing or empty.'])
            );

            return $response;
        }

        $phrase = strval($requestBodyContent['phrase']);

        /** @var array $customerList */
        $customerList = $this->queryBus->handle(new SearchCustomers([$phrase]));

        if (empty($customerList)) {
            $response->setStatusCode(404);
            $response->setContent(
                json_encode(['message' => 'Customer not found'])
            );

            return $response;
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

        $response->setStatusCode(200);
        $response->setContent(
            json_encode($customerList)
        );

        return $response;
    }

    /**
     * Delete User data by customer id
     *
     * @param Request $request
     *
     * @return Response
     */
    public function deleteCustomerData(Request $request): Response
    {
        $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $dataTypeRequested = strval($requestBodyContent['dataTypeRequested']);
        $customerData = strval($requestBodyContent['customerData']);

        try {
            $this->customerService->deleteCustomerData($dataTypeRequested, $customerData);
            $result = ['message' => 'delete completed'];
            $response->setStatusCode(200);
        } catch (Exception $e) {
            $result = ['message' => 'A problem occurred while deleting please try again'];
            $response->setStatusCode(500);
        }

        $response->setContent(json_encode($result));

        return $response;
    }

    /**
     * Get user data for the given customer
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getCustomerData(Request $request): Response
    {
        $requestBodyContent = (array) json_decode((string) $request->getContent(false), true);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $dataTypeRequested = strval($requestBodyContent['dataTypeRequested']);
        $customerData = strval($requestBodyContent['customerData']);

        try {
            $result = $this->customerService->getCustomerData($dataTypeRequested, $customerData);
            $response->setStatusCode(200);
        } catch (Exception $e) {
            $result = ['message' => 'A problem occurred while retrieving customer data please try again'];
            $response->setStatusCode(500);
        }

        $response->setContent(json_encode($result));

        return $response;
    }

    /**
     * Generate link to DownloadCustomerInvoicesController in order to download invoices
     *
     * @param Request $request
     * @param int $customerId
     *
     * @return Response
     */
    public function getDownloadInvoicesLinkByCustomerId(Request $request, int $customerId): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        /** @var Router $router */
        $router = $this->get('router');

        try {
            $customerId = new CustomerId($customerId);
            $customerHasInvoices = $this->orderInvoiceRepository->findIfInvoicesExistByCustomerId($customerId);

            if ($customerHasInvoices) {
                $result = [
                    'invoicesDownloadLink' => $router->generate('psgdpr_api_download_customer_invoices', ['customerId' => $customerId->getValue()]),
                ];
            } else {
                $result = [
                    'message' => 'There is no invoices found for this customer',
                ];
            }

            $response->setStatusCode(200);
        } catch (DeleteException $e) {
            $result = ['message' => 'A problem occurred while retrieving number of invoices'];
            $response->setStatusCode(500);
        }

        $response->setContent(json_encode($result));

        return $response;
    }
}
