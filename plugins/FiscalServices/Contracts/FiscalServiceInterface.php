<?php

declare(strict_types=1);

namespace Plugins\FiscalServices\Contracts;

use App\Models\Sma\Order\Sale;
use App\Models\Sma\Order\ReturnOrder;

/**
 * Defines the contract for fiscal service integrations that communicate with government systems.
 */
interface FiscalServiceInterface
{
    /**
     * Get the QR code URL for a given sale.
     */
    public function getQRCodeUrl(Sale $sale): ?string;

    /**
     * Report a newly created sale to the fiscal authority.
     */
    public function reportNewSale(Sale $sale): FiscalServiceResponse;

    /**
     * Report an update to a previously submitted sale.
     */
    public function reportSaleUpdate(Sale $sale, Sale $originalSale): FiscalServiceResponse;

    /**
     * Report a newly created return sale to the fiscal authority.
     */
    public function reportNewReturnSale(ReturnOrder $returnOrder): FiscalServiceResponse;
}
