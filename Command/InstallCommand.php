<?php

namespace DoS\ResourceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dos:install')
            ->setDescription('Fresh installer.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication();

        try {
            $app->find('doctrine:database:create')->run($input, $output);
            $app->find('doctrine:schema:create')->run($input, $output);
            $app->find('doctrine:fixtures:load')->run($input, $output);
            $app->find('cache:clear')->run($input, $output);
        } catch (\Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
        }

        return 1;
    }
}
