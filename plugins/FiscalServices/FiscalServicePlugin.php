<?php

declare(strict_types=1);

namespace Plugins\FiscalServices;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Plugins\FiscalServices\Services\ExampleFiscalService;
use Plugins\FiscalServices\Contracts\BootableFiscalService;
use Plugins\FiscalServices\Contracts\FiscalServiceInterface;

final class FiscalServicePlugin
{
    /**
     * @var class-string<FiscalServiceInterface>|null
     */
    private static ?string $activeService = null;

    private static bool $booted = false;

    /**
     * @return class-string<FiscalServiceInterface>
     */
    public static function active(): string
    {
        if (self::$activeService === null) {
            $driver = config('fiscal-services.default', 'example');
            $service = self::available()[$driver] ?? ExampleFiscalService::class;

            self::$activeService = $service;
        }

        return self::$activeService;
    }

    public static function resolve(): FiscalServiceInterface
    {
        $service = self::active();

        if (! is_subclass_of($service, FiscalServiceInterface::class)) {
            throw new InvalidArgumentException(sprintf('The active fiscal service [%s] must implement %s.', $service, FiscalServiceInterface::class));
        }

        return app($service);
    }

    /**
     * @param  class-string<FiscalServiceInterface>  $service
     */
    public static function use(string $service): void
    {
        if (! is_subclass_of($service, FiscalServiceInterface::class)) {
            throw new InvalidArgumentException(sprintf('The provided fiscal service [%s] must implement %s.', $service, FiscalServiceInterface::class));
        }

        self::$activeService = $service;
        self::$booted = false;
    }

    public static function forget(): void
    {
        self::$activeService = null;
        self::$booted = false;
    }

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        $service = self::resolve();

        if ($service instanceof BootableFiscalService) {
            $service->boot();
        }

        self::$booted = true;
    }

    /**
     * @return array<string, class-string<FiscalServiceInterface>>
     */
    public static function available(): array
    {
        $services = [];

        foreach (self::options() as $option) {
            $services[$option['value']] = $option['service'];
        }

        return $services;
    }

    /**
     * @return array<int, array{value:string,label:string,description:?string,service:class-string<FiscalServiceInterface>}>
     */
    public static function options(): array
    {
        $drivers = config('fiscal-services.drivers', []);

        $options = [];

        foreach ($drivers as $name => $configuration) {
            $service = $configuration['service'] ?? null;

            if (! is_string($service) || ! is_subclass_of($service, FiscalServiceInterface::class)) {
                continue;
            }

            $label = $configuration['label'] ?? Str::headline($name);
            $description = $configuration['description'] ?? null;

            $options[$name] = [
                'value'       => $name,
                'label'       => is_string($label) && $label !== '' ? $label : Str::headline($name),
                'description' => is_string($description) && $description !== '' ? $description : null,
                'service'     => $service,
            ];
        }

        if (! isset($options['example'])) {
            $options['example'] = [
                'value'       => 'example',
                'label'       => 'Example',
                'description' => 'Sandbox fiscal service useful for demos and local testing.',
                'service'     => ExampleFiscalService::class,
            ];
        }

        ksort($options);

        return array_values($options);
    }

    /**
     * @param  array<int, string>|null  $drivers
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function fields(?array $drivers = null): array
    {
        $configuredDrivers = config('fiscal-services.drivers', []);

        if ($drivers !== null) {
            $configuredDrivers = array_intersect_key($configuredDrivers, array_flip($drivers));
        }

        return collect($configuredDrivers)
            ->mapWithKeys(function (array $config, string $driver) {
                $fields = $config['fields'] ?? [];

                if (! is_array($fields)) {
                    return [$driver => []];
                }

                $normalised = collect($fields)
                    ->filter(fn ($field) => is_array($field) && isset($field['key']))
                    ->map(function (array $field) {
                        return [
                            'key'         => (string) $field['key'],
                            'label'       => $field['label'] ?? $field['key'],
                            'type'        => $field['type'] ?? 'text',
                            'component'   => $field['component'] ?? 'input',
                            'required'    => (bool) ($field['required'] ?? false),
                            'placeholder' => $field['placeholder'] ?? null,
                            'help'        => $field['help'] ?? null,
                            'rows'        => $field['rows'] ?? null,
                            'options'     => $field['options'] ?? [],
                        ];
                    })
                    ->values()
                    ->all();

                return [$driver => $normalised];
            })
            ->all();
    }
}
