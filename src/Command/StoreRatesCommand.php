<?php

namespace App\Command;

use App\Entity\ExchangeRate;
use App\Service\CbrSDK;
use App\Service\ExRatesService;
use App\Service\RbcSDK;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StoreRatesCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:store_rates';


    private $em;
    private $source1;
    private $source2;

    /**
     * StoreRatesCommand constructor.
     * @param EntityManagerInterface $em
     * @param CbrSDK $source1
     * @param RbcSDK $source2
     */
    public function __construct(EntityManagerInterface $em, CbrSDK $source1, RbcSDK $source2)
    {

        $this->em = $em;
        $this->source1 = $source1;
        $this->source2 = $source2;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Fetches current Exchange Rates and stores the average value in a DataBase')
            ->addArgument('from_currency', InputArgument::OPTIONAL, 'convert from this currency')
            ->addArgument('to_currency', InputArgument::OPTIONAL, 'convert to this currency');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if (strlen($input->getArgument('from_currency')) == 3 && strlen($input->getArgument('to_currency')) == 3) {
                $io->writeln('Arguments passed, using arguments values.');
                $arg1 = $input->getArgument('from_currency');
                $arg2 = $input->getArgument('to_currency');
            } else {
                $io->writeln('No arguments or incorrect arguments passed (ABC-like string expected), using values from the config.');
                $arg1 = $this->getContainer()->getParameter('app.currency_from');
                $arg2 = $this->getContainer()->getParameter('app.currency_to');
            }

            $rates = new ExRatesService($arg1, $arg2);
            $rates->addSource($this->source1, $this->source2);
            $average = $rates->getAverage();
        } catch (\Exception $ex) {
            throw $ex;
        }

        $exchangeRate = new ExchangeRate();
        $exchangeRate->setFromCurrency($rates->getFrom());
        $exchangeRate->setToCurrency($rates->getTo());
        $exchangeRate->setValue($average);
        $exchangeRate->setDate(date("d-m-Y"));

        $this->em->persist($exchangeRate);
        $this->em->flush();

        $io->success('New values have been added to DB.');
    }
}
