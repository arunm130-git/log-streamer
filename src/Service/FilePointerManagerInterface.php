<?php

namespace App\Service;

interface FilePointerManagerInterface
{
    public function getFilePointer(string $filePath): int;
    public function setFilePointer(string $filePath, int $pointer): void;
}