<?php declare(strict_types=1);

/**
 * To load the DE snippet json file.
 *
 * Copyright (C) BrandCrock GmbH. All rights reserved.
 *
 * If you have found this script useful, a small
 * recommendation as well as a comment on our
 * home page(https://brandcrock.com/)
 * would be greatly appreciated.
 *
 * @author  BrandCrock GmbH
 * @package BrandCrockWanderlust
 * @support support@brandcrock.com
 *
 * License proprietary.
 */

namespace Bc\BrandCrockWanderlust\Resources\snippet\de_DE;

use Shopware\Core\System\Snippet\Files\SnippetFileInterface;

class SnippetFile_de_DE implements SnippetFileInterface
{
    public function getName(): string
    {
        return 'storefront.de-DE';
    }

    public function getPath(): string
    {
        return __DIR__ . '/storefront.de-DE.json';
    }

    public function getIso(): string
    {
        return 'de-DE';
    }

    public function getAuthor(): string
    {
        return 'BrandCrock GmbH';
    }

    public function isBase(): bool
    {
        return false;
    }
}
