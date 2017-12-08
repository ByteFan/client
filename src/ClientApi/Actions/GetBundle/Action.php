<?php
/**
 * This file is part of the IOTA PHP package.
 *
 * (c) Benjamin Ansbach <benjaminansbach@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Techworker\IOTA\ClientApi\Actions\GetBundle;

use Techworker\IOTA\ClientApi\AbstractAction;
use Techworker\IOTA\ClientApi\AbstractResult;
use Techworker\IOTA\Cryptography\Hashing\CurlFactory;
use Techworker\IOTA\Cryptography\Hashing\KerlFactory;
use Techworker\IOTA\Exception;
use Techworker\IOTA\Node;
use Techworker\IOTA\RemoteApi\Commands\GetTrytes;
use Techworker\IOTA\Type\Bundle;
use Techworker\IOTA\Type\BundleHash;
use Techworker\IOTA\Type\Transaction;
use Techworker\IOTA\Type\TransactionHash;

/**
 * Replays a transfer by doing Proof of Work again.
 */
class Action extends AbstractAction
{
    use GetTrytes\RequestTrait;

    /**
     * @var TransactionHash
     */
    protected $transactionHash;

    /**
     * The factory to create a new kerl instance.
     *
     * @var KerlFactory
     */
    protected $kerlFactory;

    /**
     * The factory to create a new curl instance.
     *
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * The result instance.
     *
     * @var Result
     */
    protected $result;

    /**
     * Action constructor.
     * @param Node $node
     * @param GetTrytes\RequestFactory $getTrytesFactory
     * @param KerlFactory $kerlFactory
     * @param CurlFactory $curlFactory
     */
    public function __construct(Node $node, GetTrytes\RequestFactory $getTrytesFactory, KerlFactory $kerlFactory, CurlFactory $curlFactory)
    {
        $this->kerlFactory = $kerlFactory;
        $this->curlFactory = $curlFactory;
        $this->setGetTrytesFactory($getTrytesFactory);
        parent::__construct($node);
    }

    /**
     * Sets the transaction hash.
     *
     * @param TransactionHash $transactionHash
     *
     * @return Action
     */
    public function setTransactionHash(TransactionHash $transactionHash): self
    {
        $this->transactionHash = $transactionHash;

        return $this;
    }

    /**
     * Executes the action.
     *
     * @return AbstractResult|Result
     */
    public function execute(): Result
    {
        $this->result = new Result($this);
        $this->result->setBundle($this->traverseBundle($this->transactionHash));

        return $this->result->finish();
    }

    /**
     * Basically traverse the Bundle by going down the trunkTransactions until
     * the bundle hash of the transaction is no longer the same. In case the input
     * transaction hash is not a tail, we return an error.
     *
     * @param TransactionHash $trunkTx
     * @param BundleHash|null $bundleHash
     * @param Bundle          $bundle
     *
     * @return Bundle
     *
     * @throws \Exception
     */
    public function traverseBundle(
        TransactionHash $trunkTx,
        BundleHash $bundleHash = null,
        Bundle $bundle = null
    ): Bundle {
        if (null === $bundle) {
            $bundle = new Bundle($this->kerlFactory, $this->curlFactory, $bundleHash);
        }

        // Get trytes of transaction hash
        $getTrytesResponse = $this->getTrytes($this->node, [$trunkTx]);
        $this->result->addChildTrace($getTrytesResponse->getTrace());

        if (0 === \count($getTrytesResponse->getTransactions())) {
            // TODO: what?
            throw new Exception('Bundle transactions not visible');
        }

        $transaction = new Transaction(
            $this->curlFactory,
            (string) $getTrytesResponse->getTransactions()[0]
        );

        if (null === $bundleHash && 0 !== $transaction->getCurrentIndex()) {
            throw new Exception('Invalid tail transaction supplied.');
        }

        // If no bundle hash, define it
        if (null === $bundleHash) {
            $bundleHash = $transaction->getBundleHash();
            $bundle->setBundleHash($bundleHash);
        }

        // If different bundle hash, return with bundle
        if ((string) $bundleHash !== (string) $transaction->getBundleHash()) {
            return $bundle;
        }

        // If only one bundle element, return
        if (0 === $transaction->getLastIndex() && 0 === $transaction->getCurrentIndex()) {
            return $bundle->addTransaction($transaction);
        }

        // Define new trunkTransaction for search
        $trunkTx = $transaction->getTrunkTransactionHash();

        // Add transaction object to bundle
        $bundle->addTransaction($transaction);

        // Continue traversing with new trunkTx
        return $this->traverseBundle($trunkTx, $bundleHash, $bundle);
    }

    public function serialize(): array
    {
        return array_merge(parent::serialize(), [
            'transactionHash' => $this->transactionHash->serialize()
        ]);
    }
}
