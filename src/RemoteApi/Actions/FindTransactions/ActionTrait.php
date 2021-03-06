<?php

declare(strict_types=1);

/*
 * This file is part of the IOTA PHP package.
 *
 * (c) Benjamin Ansbach <benjaminansbach@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IOTA\RemoteApi\Actions\FindTransactions;

use IOTA\Node;
use IOTA\RemoteApi\AbstractResult;
use IOTA\RemoteApi\Exception;
use IOTA\Type\Address;
use IOTA\Type\Approvee;
use IOTA\Type\BundleHash;
use IOTA\Type\Tag;

/**
 * Trait RequestTrait.
 *
 * Wrapper function to execute the request.
 */
trait ActionTrait
{
    /**
     * The request factory.
     *
     * @var ActionFactory
     */
    private $findTransactionsFactory;

    /**
     * Sets the factory for the request.
     *
     * @param ActionFactory $findTransactionsFactory
     *
     * @return ActionTrait
     */
    protected function setFindTransactionsFactory(ActionFactory $findTransactionsFactory): self
    {
        $this->findTransactionsFactory = $findTransactionsFactory;

        return $this;
    }

    // @noinspection MoreThanThreeArgumentsInspection

    /**
     * Executes the request.
     *
     * @param Node         $node
     * @param Address[]    $addresses
     * @param BundleHash[] $bundleHashes
     * @param Tag[]        $tags
     * @param Approvee[]   $approvees
     *
     * @throws Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @return AbstractResult|Result
     */
    protected function findTransactions(
        Node $node,
                                 array $addresses = [],
                                 array $bundleHashes = [],
                                 array $tags = [],
                                 array $approvees = []
    ): Result {
        $request = $this->findTransactionsFactory->factory($node);
        $request->setAddresses($addresses);
        $request->setBundleHashes($bundleHashes);
        $request->setTags($tags);
        $request->setApprovees($approvees);

        return $request->execute();
    }
}
