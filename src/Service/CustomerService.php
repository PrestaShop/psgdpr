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
use PrestaShop\Module\Psgdpr\Repository\LoggerRepository;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Crypto\Hashing;
use PrestaShop\PrestaShop\Core\Domain\Address\Command\AddCustomerAddressCommand;
use PrestaShop\PrestaShop\Core\Domain\Address\ValueObject\AddressId;
use PrestaShop\PrestaShop\Core\Domain\Customer\Command\AddCustomerCommand;
use PrestaShop\PrestaShop\Core\Domain\Customer\Command\DeleteCustomerCommand;
use PrestaShop\PrestaShop\Core\Domain\Customer\Query\GetCustomerForViewing;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\AddressInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\ViewableCustomer;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerDeleteMethod;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use PrestaShop\PrestaShop\Core\Group\Provider\DefaultGroupsProviderInterface;
use PrestaShopException;
use Psgdpr;
use Tools;

class CustomerService
{
    /**
     * @var Psgdpr
     */
    private $module;

    /**
     * @var Context
     */
    private $context;

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
     * @var DefaultGroupsProviderInterface
     */
    private $defaultGroupProvider;

    /**
     * @var Hashing
     */
    private $hashing;

    /**
     * @var LoggerRepository
     */
    private $loggerRepository;

    /**
     * CustomerService constructor.
     *
     * @param Psgdpr $module
     * @param Context $context
     * @param CartRepository $cartRepository
     * @param CartRuleRepository $cartRuleRepository
     * @param CustomerRepository $customerRepository
     * @param CommandBusInterface $commandBus
     * @param CommandBusInterface $queryBus
     * @param DefaultGroupsProviderInterface $defaultGroupProvider
     * @param Hashing $hashing
     * @param LoggerRepository $loggerRepository
     *
     * @return void
     */
    public function __construct(
        Psgdpr $module,
        Context $context,
        CartRepository $cartRepository,
        CartRuleRepository $cartRuleRepository,
        CustomerRepository $customerRepository,
        CommandBusInterface $commandBus,
        CommandBusInterface $queryBus,
        DefaultGroupsProviderInterface $defaultGroupProvider,
        Hashing $hashing,
        LoggerRepository $loggerRepository
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->cartRepository = $cartRepository;
        $this->cartRuleRepository = $cartRuleRepository;
        $this->customerRepository = $customerRepository;
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
        $this->defaultGroupProvider = $defaultGroupProvider;
        $this->hashing = $hashing;
        $this->loggerRepository = $loggerRepository;
    }

    /**
     * Delete customer data from Prestashop
     *
     * @param CustomerId $customerId
     *
     * @throws DeleteException
     */
    public function deleteCustomerDataFromPrestashop(CustomerId $customerId)
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
     * @param string|string[] $data
     *
     * @throws DeleteException
     */
    public function deleteCustomerDataFromModules($data)
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
     * Delete customer data from psgdpr module.
     *
     * @param CustomerId $customerId
     *
     * @throws DeleteException
     */
    public function deleteCustomerDataFromGDPRModule(CustomerId $customerId)
    {
        try {
            $this->loggerRepository->anonymizeLogsByCustomerId($customerId);
        } catch (\Exception $e) {
            throw new DeleteException($e->getMessage());
        }
    }

    /**
     * Find or create an anonymous customer
     *
     * @return array
     */
    private function createAnonymousCustomer(): array
    {
        $defaultGroups = $this->defaultGroupProvider->getGroups();

        if (null === $this->context) {
            throw new PrestaShopException('Context is not defined');
        }

        $shop = $this->context->shop;

        if (null === $shop) {
            throw new PrestaShopException('Shop is not defined');
        }

        /** @var int|bool $anonymousCustomerId */
        $anonymousCustomerId = $this->customerRepository->findCustomerIdByEmail('anonymous@psgdpr.com');

        if (false === $anonymousCustomerId) {
            $anonymousCustomer = $this->commandBus->handle(new AddCustomerCommand(
                'Anonymous',
                'Anonymous',
                'anonymous@psgdpr.com',
                $this->hashing->hash((string) Tools::passwdGen(64)),
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
