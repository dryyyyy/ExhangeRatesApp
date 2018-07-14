<?php

namespace App\Command;

use App\Service\ExRatesService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StoreRatesCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:storeRates';

    private $rates;
    public function __construct(ExRatesService $rates)
    {
        $this->rates = $rates;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $doctrine = $this->getContainer()->get('doctrine');
        $entityManager = $doctrine->getEntityManager();

        $this->rates->putToDB($entityManager);

        $io->success('New values have been added to DB.');
    }
}
