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

use Configuration;
use Context;
use Hook;
use PrestaShop\Module\Psgdpr\Exception\Customer\DeleteException;
use PrestaShop\Module\Psgdpr\Repository\CartRepository;
use PrestaShop\Module\Psgdpr\Repository\CartRuleRepository;
use PrestaShop\Module\Psgdpr\Repository\CustomerRepository;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Address\Command\AddCustomerAddressCommand;
use PrestaShop\PrestaShop\Core\Domain\Address\ValueObject\AddressId;
use PrestaShop\PrestaShop\Core\Domain\Customer\Command\AddCustomerCommand;
use PrestaShop\PrestaShop\Core\Domain\Customer\Command\DeleteCustomerCommand;
use PrestaShop\PrestaShop\Core\Domain\Customer\Query\GetCustomerForViewing;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerDeleteMethod;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use PrestaShopException;
use Psgdpr;
use Tools;

class CustomerService
{
    const CUSTOMER = 'customer';
    const EMAIL = 'email';
    const PHONE = 'phone';

    /**
     * @var Psgdpr
     */
    private $module;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var LoggerService
     */
    private $loggerService;

    /**
     * @var CartRepository
     */
    private $cartRepository;

    /**
     * @var CartRuleRepository
     */
    private $cartRuleRepository;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var CommandBusInterface
     */
    private $queryBus;

    /**
     * @var ExportService
     */
    private $exportService;

    /**
     * CustomerService constructor.
     *
     * @param Psgdpr $module
     * @param Context $context
     * @param LoggerService $loggerService
     * @param CartRepository $cartRepository
     * @param CartRuleRepository $cartRuleRepository
     * @param CustomerRepository $customerRepository
     * @param CommandBusInterface $commandBus
     * @param CommandBusInterface $queryBus
     * @param ExportService $exportService
     *
     * @return void
     */
    public function __construct(
        Psgdpr $module,
        Context $context,
        LoggerService $loggerService,
        CartRepository $cartRepository,
        CartRuleRepository $cartRuleRepository,
        CustomerRepository $customerRepository,
        CommandBusInterface $commandBus,
        CommandBusInterface $queryBus,
        ExportService $exportService
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->loggerService = $loggerService;
        $this->cartRepository = $cartRepository;
        $this->cartRuleRepository = $cartRuleRepository;
        $this->customerRepository = $customerRepository;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
        $this->exportService = $exportService;
    }

    /**
     * Delete customer data
     *
     * @param string $dataTypeRequested
     * @param string $data
     *
     * @return void
     */
    public function deleteCustomerData(string $dataTypeRequested, string $data)
    {
        switch ($dataTypeRequested) {
            case self::CUSTOMER:
                $customerId = new CustomerId($data);
                $customerData = $this->customerRepository->findCustomerNameByCustomerId($customerId);

                $this->deleteCustomerDataFromPrestashop($customerId);
                $this->deleteCustomerDataFromModules($customerId);

                $this->loggerService->createLog($customerId->getValue(), LoggerService::REQUEST_TYPE_DELETE, 0, 0, $customerData);
                break;
            case self::EMAIL:
                $customerData = ['email' => $data];
                $this->deleteCustomerDataFromModules($customerData);

                $this->loggerService->createLog(0, LoggerService::REQUEST_TYPE_DELETE, 0, 0, $data);
                break;
            case self::PHONE:
                $customerData = ['phone' => $data];
                $this->deleteCustomerDataFromModules($customerData);

                $this->loggerService->createLog(0, LoggerService::REQUEST_TYPE_DELETE, 0, 0, $data);
                break;
        }
    }

    /**
     * Delete customer data from Prestashop
     *
     * @param CustomerId $customerId
     *
     * @throws DeleteException
     */
    private function deleteCustomerDataFromPrestashop(CustomerId $customerId)
    {
        $anonymousCustomerInfos = $this->createAnonymousCustomer();

        try {
            $this->cartRepository->anonymizeCustomerCartByCustomerId(
                $customerId,
                $anonymousCustomerInfos['customerId'],
                $anonymousCustomerInfos['addressId']
            );

            $this->cartRuleRepository->deleteCartRulesByCustomerId($customerId);

            $this->commandBus->handle(
                new DeleteCustomerCommand(
                    $customerId->getValue(),
                    CustomerDeleteMethod::ALLOW_CUSTOMER_REGISTRATION
                )
            );
        } catch (\Exception $e) {
            throw new DeleteException($e->getMessage());
        }
    }

    /**
     * Delete customer data from modules
     *
     * @param string $data
     *
     * @throws DeleteException
     */
    private function deleteCustomerDataFromModules($data)
    {
        $modulesList = Hook::getHookModuleExecList('actionDeleteGDPRCustomer');

        if ($modulesList == false) {
            return;
        }

        foreach ($modulesList as $module) {
            if ($module['id_module'] != $this->module->id) {
                Hook::exec('actionDeleteGDPRCustomer', [$data], $module['id_module']);
            }
        }
    }

    /**
     * Get customer data
     *
     * @param string $dataType
     * @param string|int $data
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    public function getCustomerData(string $dataType, $data): array
    {
        switch ($dataType) {
            case self::CUSTOMER:
                $customerId = new CustomerId($data);

                return $this->exportService->exportCustomerData($customerId, ExportService::EXPORT_TYPE_VIEWING);
                break;
            case self::EMAIL:
                $customerData = ['email' => $data];

                return $this->exportService->getThirdPartyModulesInformations($customerData);
                break;
            case self::PHONE:
                $customerData = ['phone' => $data];

                return $this->exportService->getThirdPartyModulesInformations($customerData);
                break;
        }
    }

    /**
     * Find or create an anonymous customer
     *
     * @return array
     */
    private function createAnonymousCustomer(): array
    {
        /** @var Hashing $crypto */
        $crypto = $this->module->get('hashing');

        /** @var DefaultGroupsProviderInterface $defaultGroupProvider */
        $defaultGroupProvider = $this->module->get('prestashop.adapter.group.provider.default_groups_provider');
        $defaultGroups = $defaultGroupProvider->getGroups();

        if (null === $this->context) {
            throw new PrestaShopException('Context is not defined');
        }

        $shop = $this->context->shop;

        if (null === $shop) {
            throw new PrestaShopException('Shop is not defined');
        }

        /** @var array $anonymousCustomerId */
        $anonymousCustomerId = $this->customerRepository->findCustomerIdByEmail('anonymous@psgdpr.com');

        if (false === $anonymousCustomerId) {
            $anonymousCustomer = $this->commandBus->handle(new AddCustomerCommand(
                'Anonymous',
                'Anonymous',
                'anonymous@psgdpr.com',
                $crypto->hash((string) Tools::passwdGen(64)),
                $defaultGroups->getCustomersGroup()->getId(),
                [$defaultGroups->getCustomersGroup()->getId()],
                $shop->getShopId()
            ));

            $anonymousAddress = $this->commandBus->handle(new AddCustomerAddressCommand(
                $anonymousCustomer->getValue(),
                'Anonymous',
                'Anonymous',
                'Anonymous',
                'Anonymous',
                'Anonymous',
                (int) Configuration::get('PS_COUNTRY_DEFAULT'),
                '00000'
            ));

            return [
                'customerId' => $anonymousCustomer,
                'addressId' => $anonymousAddress,
            ];
        }

        /** @var ViewableCustomer $anonymousCustomer */
        $anonymousCustomer = $this->queryBus->handle(
            new GetCustomerForViewing($anonymousCustomerId)
        );

        /** @var AddressInformation $anonymousAddress */
        $anonymousAddress = $anonymousCustomer->getAddressesInformation()[0];

        return [
            'customerId' => $anonymousCustomer->getCustomerId(),
            'addressId' => new AddressId($anonymousAddress->getAddressId()),
        ];
    }
}
