<?php

declare(strict_types=1);

namespace Plugins\FiscalServices\Contracts;

interface BootableFiscalService
{
    public function boot(): void;
}
