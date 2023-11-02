<?php

namespace App\Command;

use App\Messenger\Proxy\CheckProxyMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

#[AsCommand(
    name: 'app:proxies',
    description: 'Actualization list of proxy in DB',
)]
class CheckProxiesCommand extends Command
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly MessageBusInterface $messageBus
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
//            $response = $this->httpClient->request('GET', 'https://api.proxyscrape.com/proxytable.php?nf=true&country=all');
            $response = $this->httpClient->request('GET', 'https://api.proxyscrape.com/proxytable.php');
            $result = json_decode($response->getContent());
            foreach ([
                         'http',
                         'socks4',
                         'socks5'
                     ] as $key) {
                foreach ($result->$key as $ip => $data) {
                    $io->note(sprintf('Check proxy: %s', $ip));
                    $this->messageBus->dispatch(new CheckProxyMessage(
                        $ip
                    ));
                }
            }
        } catch (Throwable $e) {
            $io->error($e->getMessage());
        }

        return Command::SUCCESS;
    }
}
