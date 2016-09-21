<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\FrameworkExtraBundle\EventListener;

/**
 * CacheListener handles HTTP cache headers.
 *
 * It can be configured via the Cache, LastModified, and Etag annotations.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @deprecated Deprecated since 3.0, to be removed in 4.0. Use the HttpCacheListener instead.
 */
class CacheListener extends HttpCacheListener
{
}
