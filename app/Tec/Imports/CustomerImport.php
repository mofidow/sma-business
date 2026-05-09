<?php

namespace App\Tec\Imports;

use App\Models\User;
use Nnjeim\World\Models\State;
use Nnjeim\World\Models\Country;
use App\Models\Sma\People\Customer;
use App\Models\Sma\People\PriceGroup;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\Sma\People\CustomerGroup;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Concerns\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Http\Requests\Sma\People\CustomerRequest;
use App\Tec\Notifications\Import\ImportHasFailedNotification;

class CustomerImport implements ShouldQueue, ToModel, WithBatchInserts, WithChunkReading, WithEvents, WithHeadingRow, WithUpserts, WithValidation
{
    use Importable;

    public function __construct(public User $user) {}

    public function model(array $row)
    {
        $row['price_group_id'] = null;
        if ($row['price_group'] ?? null) {
            $row['price_group_id'] = PriceGroup::where('name', $row['price_group'])->first()?->id;
        }

        $row['customer_group_id'] = null;
        if ($row['customer_group'] ?? null) {
            $row['customer_group_id'] = CustomerGroup::where('name', $row['customer_group'])->first()?->id;
        }

        $row['state_id'] = null;
        if ($row['state'] ?? null) {
            $row['state_id'] = State::where('name', $row['state'])->first()?->id;
        }
        $row['country_id'] = null;
        if ($row['country'] ?? null) {
            $row['country_id'] = Country::where('name', $row['country'])->first()?->id;
        }

        if (! ($row['company'] ?? null)) {
            $row['company'] = $row['name'];
        }

        return new Customer([
            'name'              => $row['name'],
            'phone'             => $row['phone'],
            'email'             => $row['email'],
            'company'           => $row['company'],
            'due_limit'         => $row['due_limit'],
            'price_group_id'    => $row['price_group_id'] ?? null,
            'customer_group_id' => $row['customer_group_id'] ?? null,

            'lot_no'         => $row['lot_no'],
            'street'         => $row['street'],
            'address_line_1' => $row['address_line_1'],
            'address_line_2' => $row['address_line_2'],
            'city'           => $row['city'],
            'postal_code'    => $row['postal_code'],
            'state_id'       => $row['state_id'] ?? null,
            'country_id'     => $row['country_id'] ?? null,

            // 'opening_balance'  => $row['opening_balance'],
            // 'extra_attributes' => $row['extra_attributes'],

            'user_id' => $this->user?->id,
        ]);
    }

    public function rules(): array
    {
        $rules = (new CustomerRequest)->rules();

        return $rules;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return ['name', 'phone', 'email'];
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function (ImportFailed $event) {
                logger()->error('Import failed with error: ' . $event->getException()->getMessage(), [
                    'exception' => $event->getException(),
                ]);
                $this->user->notify(new ImportHasFailedNotification($event, 'An error occurred while importing customers. Please check the error message below and for more details please check the latest log file in the storage/logs directory.'));
            },
        ];
    }
}
