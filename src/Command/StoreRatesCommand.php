<?php

namespace App\Command;

use App\Service\ExRatesService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StoreRatesCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:store_rates';

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

        $this->rates->fetchData();
        try{
            $result = $this->rates->fetchData();
        } catch (\Exception $ex) {
            $result = ['error' => $ex->getMessage()];
        }

        $result->sendTodaysRatesToDB();

        $io->success('New values have been added to DB.');
    }
}
