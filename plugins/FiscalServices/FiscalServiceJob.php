<?php

declare(strict_types=1);

namespace Plugins\FiscalServices;

use LogicException;
use App\Models\Sma\Order\Sale;
use Illuminate\Support\Facades\Log;
use App\Models\Sma\Order\ReturnOrder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Plugins\FiscalServices\Contracts\FiscalServiceResponse;

final class FiscalServiceJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    private const ACTION_NEW_SALE = 'new-sale';

    private const ACTION_SALE_UPDATE = 'sale-update';

    private const ACTION_NEW_RETURN_SALE = 'new-return-sale';

    public function __construct(
        private string $action,
        private ?Sale $sale = null,
        private ?Sale $originalSale = null,
        private ?ReturnOrder $returnOrder = null,
    ) {}

    public static function dispatchNewSale(Sale $sale): void
    {
        self::dispatch(self::ACTION_NEW_SALE, $sale);
    }

    public static function dispatchSaleUpdate(Sale $sale, Sale $originalSale): void
    {
        self::dispatch(self::ACTION_SALE_UPDATE, $sale, $originalSale);
    }

    public static function dispatchNewReturnSale(ReturnOrder $returnOrder): void
    {
        self::dispatch(self::ACTION_NEW_RETURN_SALE, null, null, $returnOrder);
    }

    public function handle(): void
    {
        if (! (get_settings('fiscal_service_driver') ?? null)) {
            return;
        }

        $response = match ($this->action) {
            self::ACTION_NEW_SALE        => $this->reportNewSale(),
            self::ACTION_SALE_UPDATE     => $this->reportSaleUpdate(),
            self::ACTION_NEW_RETURN_SALE => $this->reportNewReturnSale(),
            default                      => throw new LogicException('Unsupported fiscal reporting action [' . $this->action . '].'),
        };

        if ($response === null) {
            return;
        }

        $this->persistResponse($response);

        if ($response->isSuccessful()) {
            return;
        }

        $this->logFailure($response);
    }

    private function reportNewSale(): FiscalServiceResponse
    {
        $sale = $this->sale ?? throw new LogicException('Missing sale for new sale fiscal report.');

        return FiscalServicePlugin::resolve()->reportNewSale($sale);
    }

    private function reportSaleUpdate(): FiscalServiceResponse
    {
        $sale = $this->sale ?? throw new LogicException('Missing sale for sale update fiscal report.');
        $originalSale = $this->originalSale ?? throw new LogicException('Missing original sale for sale update fiscal report.');

        return FiscalServicePlugin::resolve()->reportSaleUpdate($sale, $originalSale);
    }

    private function reportNewReturnSale(): FiscalServiceResponse
    {
        $returnOrder = $this->returnOrder ?? throw new LogicException('Missing return order for return sale fiscal report.');

        return FiscalServicePlugin::resolve()->reportNewReturnSale($returnOrder);
    }

    private function persistResponse(FiscalServiceResponse $response): void
    {
        $data = $this->responsePayload($response);

        if ($this->action === self::ACTION_NEW_RETURN_SALE) {
            $returnOrder = $this->returnOrder ?? throw new LogicException('Return order missing during fiscal response persistence.');
            $returnOrder->update(['fiscal_service_response' => $data]);

            return;
        }

        $sale = $this->sale ?? throw new LogicException('Sale missing during fiscal response persistence.');
        $sale->update(['fiscal_service_response' => $data]);
    }

    /**
     * @return array{success: bool, message: string, reference: ?string, payload: array<string, mixed>}
     */
    private function responsePayload(FiscalServiceResponse $response): array
    {
        return [
            'success'   => $response->isSuccessful(),
            'message'   => $response->message(),
            'reference' => $response->reference(),
            'payload'   => $response->payload(),
        ];
    }

    private function logFailure(FiscalServiceResponse $response): void
    {
        if ($this->action === self::ACTION_NEW_RETURN_SALE) {
            $returnOrder = $this->returnOrder ?? throw new LogicException('Return order missing during failure logging.');

            Log::warning('Fiscal service rejected return sale submission.', [
                'return_order_id' => $returnOrder->getKey(),
                'message'         => $response->message(),
                'payload'         => $response->payload(),
            ]);

            return;
        }

        $sale = $this->sale ?? throw new LogicException('Sale missing during failure logging.');
        $context = [
            'sale_id' => $sale->getKey(),
            'message' => $response->message(),
            'payload' => $response->payload(),
        ];

        if ($this->action === self::ACTION_SALE_UPDATE) {
            $context['original_id'] = $this->originalSale?->getKey();

            Log::warning('Fiscal service rejected sale update.', $context);

            return;
        }

        Log::warning('Fiscal service rejected sale submission.', $context);
    }
}
