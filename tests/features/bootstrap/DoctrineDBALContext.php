<?php

declare(strict_types=1);

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

class DoctrineDBALContext extends BaseContext
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        $connectionParams = [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__.'/../../../examples/rulerz.db', // meh.
        ];
        $this->connection = DriverManager::getConnection($connectionParams, new Configuration());
    }

    /**
     * {@inheritdoc}
     */
    protected function getCompilationTarget()
    {
        return new \RulerZ\Target\DoctrineDBAL\DoctrineDBAL();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultDataset()
    {
        return $this->connection
            ->createQueryBuilder()
            ->select('*')
            ->from('players');
    }

    /**
     * @When I use the query builder dataset
     */
    public function iUseTheQueryBuilderDataset()
    {
        $this->dataset = $this->getDefaultDataset();
    }
}
