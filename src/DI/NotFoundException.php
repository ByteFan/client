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

namespace Techworker\IOTA\DI;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 *
 * Minimum exception definition.
 */
class NotFoundException extends \Exception implements NotFoundExceptionInterface
{
}
