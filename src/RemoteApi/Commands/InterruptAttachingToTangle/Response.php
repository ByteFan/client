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

namespace Techworker\IOTA\RemoteApi\Commands\InterruptAttachingToTangle;

use Techworker\IOTA\RemoteApi\AbstractResponse;

/**
 * Class Response.
 *
 * Empty response from InterruptAttachingToTangle request.
 *
 * @see https://iota.readme.io/docs/interruptattachingtotangle
 */
class Response extends AbstractResponse
{
    protected function mapResults(): void
    {
    }


    /**
     * Gets the array version of the response.
     *
     * @return array
     */
    public function serialize(): array
    {
        return array_merge([], parent::serialize());
    }
}
