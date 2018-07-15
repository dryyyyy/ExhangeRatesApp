<?php

namespace App\Command;

use App\Service\ExRatesService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
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
        $this->setDescription('Fetches current Exchange Rates from CBR and RBC and stores the average value in a DataBase');
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
