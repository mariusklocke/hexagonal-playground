<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use Exception;
use HexagonalPlayground\Application\ServiceConfiguratorInterface;
use HexagonalPlayground\Infrastructure\Environment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    public const NAME = 'app:setup';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ServiceConfiguratorInterface[] $configurators */
        $configurators = $this->container->get(ServiceConfiguratorInterface::class);

        $merged = [];
        foreach ($configurators as $configurator) {
            $config = $this->configureService($input, $output, $configurator);
            $merged = array_merge($merged, $config);
        }

        $this->showResult($input, $output, $merged);
    }

    private function configureService(InputInterface $input, OutputInterface $output, ServiceConfiguratorInterface $configurator): array
    {
        $io = $this->getStyledIO($input, $output);

        $defaults = $configurator->getDefaults();
        $config = Environment::getArray(array_keys($defaults));
        $config = array_merge($defaults, $config);

        do {
            $e = null;
            Environment::setArray($config);

            try {
                $configurator->validate();
            } catch (Exception $e) {
                $io->warning('Invalid configuration for ' . $configurator->getName());
                $io->warning($e);
                $config = $this->askForValues($input, $output, $config);
            }
        } while ($e instanceof Exception);

        $io->success(sprintf('Configuration for %s is valid', $configurator->getName()));

        return $config;
    }

    private function askForValues(InputInterface $input, OutputInterface $output, array $config)
    {
        $io = $this->getStyledIO($input, $output);

        $values = [];
        foreach ($config as $key => $value) {
            $question = sprintf('Please provide a configuration value for %s', $key);
            $values[$key] = $io->ask($question, $value);
        }

        return $values;
    }

    private function showResult(InputInterface $input, OutputInterface $output, array $merged): void
    {
        $io = $this->getStyledIO($input, $output);
        $io->section('Copy the following lines save it as your .env file');
        foreach ($merged as $name => $value) {
            $io->text("$name=$value");
        }
    }
}
