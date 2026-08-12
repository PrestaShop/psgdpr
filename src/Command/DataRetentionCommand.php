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

namespace PrestaShop\Module\Psgdpr\Command;

use PrestaShop\Module\Psgdpr\Service\DataRetention\DataRetentionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs the GDPR data-retention job from the CLI:
 *   php bin/console psgdpr:data-retention:run
 *
 * The CLI kernel exposes the full container (incl. the command bus used for
 * anonymization), so this is the recommended way to schedule the job from a
 * system crontab, with no dependency on the ps_cronjobs module.
 */
class DataRetentionCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'psgdpr:data-retention:run';

    /**
     * @var DataRetentionService
     */
    private $dataRetentionService;

    /**
     * @param DataRetentionService $dataRetentionService
     */
    public function __construct(DataRetentionService $dataRetentionService)
    {
        parent::__construct();
        $this->dataRetentionService = $dataRetentionService;
    }

    protected function configure()
    {
        $this
            ->setName('psgdpr:data-retention:run')
            ->setDescription('Warn and anonymize customers inactive beyond the configured retention period.')
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Preview only: report how many customers would be warned/anonymized, without sending any email or anonymizing any data.'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dryRun = (bool) $input->getOption('dry-run');

        $result = $this->dataRetentionService->processRetention($dryRun);

        if ($dryRun) {
            $output->writeln(sprintf(
                '<comment>[DRY RUN]</comment> Would warn %d customer(s) and anonymize %d customer(s). Nothing was sent or changed.',
                $result['warned'],
                $result['anonymized']
            ));

            return 0;
        }

        $output->writeln(sprintf(
            '<info>Data retention:</info> %d customer(s) warned, %d customer(s) anonymized.',
            $result['warned'],
            $result['anonymized']
        ));

        return 0;
    }
}
