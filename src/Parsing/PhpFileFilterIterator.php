<?php

declare(strict_types=1);

namespace Whsv26\Mediator\Parsing;

use RecursiveFilterIterator;
use SplFileInfo;

/**
 * @psalm-type PathName = string
 * @psalm-type FileInfo = RecursiveDirectoryIterator|string|SplFileInfo
 *
 * @extends RecursiveFilterIterator<PathName, FileInfo>
 */
class PhpFileFilterIterator extends RecursiveFilterIterator
{
    public function accept(): bool
    {
        $file = $this->current();

        if (!$file instanceof SplFileInfo) {
            return false;
        }

        $filename = $file->getFilename();

        // Skip hidden files and directories.
        if ($filename[0] === '.') {
            return false;
        }

        // Exclude vendor
        if ($file->isDir()) {
            return 'vendor' !== $filename;
        } else {
            return 'php' === $file->getExtension()
                && ctype_upper($filename[0]);
        }
    }
}
