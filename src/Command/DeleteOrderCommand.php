<?php declare(strict_types = 1);

namespace Diga\DigaCommands\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteOrderCommand extends Command
{
    /**
     * @var EntityRepositoryInterface
     */
    protected $orderRepository;

    public function __construct(
        EntityRepositoryInterface $orderRepository
    ) {
        parent::__construct();

        $this->orderRepository = $orderRepository;
    }

    protected function configure(): void
    {
        $this->setName('diga:deleteorder');
        $this->addArgument('order', InputArgument::REQUIRED);
        $this->setDescription('Remove order by ordername using DAL');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $orderNr = $input->getArgument('order');
        $output->writeln('Remove order nr:  ' . $orderNr);

        $context = Context::createDefaultContext();
       
        /** @var EntityCollection $entities */
        $orders = $this->orderRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('orderNumber', $orderNr)),
            $context);

        if (\count($orders) === 0) {
            $io->error('no orders found');

            return 1;
        }

        foreach ($orders as $key => $order) {
            $orderNumber = $order->getOrderNumber();   
            $id = $order->getUniqueIdentifier();
            $output->writeln('order:  ' . $orderNumber . ' id: ' . $id );

            if($id){
                $output->writeln('remove order:  ' . $orderNumber . ' id: ' . $id );
                $this->orderRepository->delete([['id' => $id],], $context);
            }            
        }               
    }
}