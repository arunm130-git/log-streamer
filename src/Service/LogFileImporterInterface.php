<?php

namespace App\Service;

interface LogFileImporterInterface
{
public function importLogs(string $filePath): int;
}