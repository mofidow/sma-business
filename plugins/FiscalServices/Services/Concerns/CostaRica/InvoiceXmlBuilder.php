<?php

declare(strict_types=1);

namespace Plugins\FiscalServices\Services\Concerns\CostaRica;

use App\Models\Sma\Order\Sale;
use App\Models\Sma\Order\ReturnOrder;

/**
 * Invoice XML Builder for Costa Rica Electronic Invoicing.
 *
 * Builds XML documents compliant with Costa Rica Hacienda requirements.
 * Supports: Factura Electrónica (01), Nota de Crédito (03), Nota de Débito (02),
 * Tiquete Electrónico (04).
 */
class InvoiceXmlBuilder
{
    private const DOCUMENT_TYPES = [
        '01' => 'FacturaElectronica',
        '02' => 'NotaDebitoElectronica',
        '03' => 'NotaCreditoElectronica',
        '04' => 'TiqueteElectronico',
    ];

    private const NAMESPACE_URI = 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/facturaElectronica';

    public function __construct(
        private Sale $sale,
        private string $documentType = '01',
        private Sale|ReturnOrder|null $reference = null,
    ) {}

    /**
     * Build the XML document.
     *
     * @return array{clave: string, xml: string, consecutivo: string}
     */
    public function build(): array
    {
        $clave = $this->generateClave();
        $consecutivo = $this->generateConsecutivo();

        $rootElement = self::DOCUMENT_TYPES[$this->documentType] ?? 'FacturaElectronica';
        $namespace = $this->getNamespaceForDocumentType();

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Create root element
        $root = $xml->createElementNS($namespace, $rootElement);
        $root->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation',
            "{$namespace} {$rootElement}_V4.3.xsd",
        );
        $xml->appendChild($root);

        // Add document elements
        $this->addElement($xml, $root, 'Clave', $clave);
        $this->addElement($xml, $root, 'CodigoActividad', $this->getActivityCode());
        $this->addElement($xml, $root, 'NumeroConsecutivo', $consecutivo);
        $this->addElement($xml, $root, 'FechaEmision', $this->sale->created_at->toIso8601String());

        // Add issuer (Emisor)
        $this->addIssuerSection($xml, $root);

        // Add receiver (Receptor) - not for Tiquete
        if ($this->documentType !== '04') {
            $this->addReceiverSection($xml, $root);
        }

        // Add sale condition and payment method
        $this->addElement($xml, $root, 'CondicionVenta', $this->getCondicionVenta());
        $this->addElement($xml, $root, 'PlazoCredito', $this->getPlazoCredito());
        $this->addPaymentMethods($xml, $root);

        // Add line items
        $this->addLineItems($xml, $root);

        // Add summary
        $this->addSummary($xml, $root);

        // Add reference information for credit/debit notes
        if (in_array($this->documentType, ['02', '03']) && $this->reference) {
            $this->addReferenceInformation($xml, $root);
        }

        return [
            'clave'       => $clave,
            'xml'         => $xml->saveXML(),
            'consecutivo' => $consecutivo,
        ];
    }

    /**
     * Generate the unique document key (Clave).
     */
    private function generateClave(): string
    {
        $store = $this->sale->store;

        $pais = '506'; // Costa Rica
        $dia = $this->sale->created_at->format('d');
        $mes = $this->sale->created_at->format('m');
        $ano = $this->sale->created_at->format('y');
        $cedula = str_pad(preg_replace('/[^0-9]/', '', $store->tax_number ?? ''), 12, '0', STR_PAD_LEFT);
        $consecutivo = $this->generateConsecutivo();
        $situacion = '1'; // Normal
        $seguridad = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

        return $pais . $dia . $mes . $ano . $cedula . $consecutivo . $situacion . $seguridad;
    }

    /**
     * Generate the consecutive number.
     */
    private function generateConsecutivo(): string
    {
        $sucursal = str_pad((string) ($this->sale->store_id ?? 1), 3, '0', STR_PAD_LEFT);
        $terminal = '00001';
        $tipoDocumento = str_pad($this->documentType, 2, '0', STR_PAD_LEFT);
        $numeroFactura = str_pad((string) $this->sale->id, 10, '0', STR_PAD_LEFT);

        return $sucursal . $terminal . $tipoDocumento . $numeroFactura;
    }

    private function getNamespaceForDocumentType(): string
    {
        return match ($this->documentType) {
            '02'    => 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/notaDebitoElectronica',
            '03'    => 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/notaCreditoElectronica',
            '04'    => 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/tiqueteElectronico',
            default => 'https://cdn.comprobanteselectronicos.go.cr/xml-schemas/v4.3/facturaElectronica',
        };
    }

    private function addIssuerSection(\DOMDocument $xml, \DOMElement $root): void
    {
        $store = $this->sale->store;
        $emisor = $xml->createElement('Emisor');
        $root->appendChild($emisor);

        $this->addElement($xml, $emisor, 'Nombre', $this->sanitize($store->name, 100));

        // Identification
        $identificacion = $xml->createElement('Identificacion');
        $emisor->appendChild($identificacion);
        $this->addElement($xml, $identificacion, 'Tipo', $store->tax_id_type ?? '02');
        $this->addElement($xml, $identificacion, 'Numero', preg_replace('/[^0-9]/', '', $store->tax_number ?? ''));

        if ($store->company_name) {
            $this->addElement($xml, $emisor, 'NombreComercial', $this->sanitize($store->company_name, 80));
        }

        // Location
        $this->addLocationSection($xml, $emisor, $store, 'Ubicacion');

        // Phone
        if ($store->phone) {
            $telefono = $xml->createElement('Telefono');
            $emisor->appendChild($telefono);
            $this->addElement($xml, $telefono, 'CodigoPais', '506');
            $this->addElement($xml, $telefono, 'NumTelefono', preg_replace('/[^0-9]/', '', $store->phone));
        }

        // Email
        if ($store->email) {
            $this->addElement($xml, $emisor, 'CorreoElectronico', $store->email);
        }
    }

    private function addReceiverSection(\DOMDocument $xml, \DOMElement $root): void
    {
        $customer = $this->sale->customer;

        if (! $customer || ! $customer->tax_number) {
            return;
        }

        $receptor = $xml->createElement('Receptor');
        $root->appendChild($receptor);

        $this->addElement($xml, $receptor, 'Nombre', $this->sanitize($customer->company ?? $customer->name, 100));

        // Identification
        $identificacion = $xml->createElement('Identificacion');
        $receptor->appendChild($identificacion);
        $this->addElement($xml, $identificacion, 'Tipo', $customer->tax_id_type ?? '01');
        $this->addElement($xml, $identificacion, 'Numero', preg_replace('/[^0-9]/', '', $customer->tax_number));

        if ($customer->company && $customer->name !== $customer->company) {
            $this->addElement($xml, $receptor, 'NombreComercial', $this->sanitize($customer->name, 80));
        }

        // Location
        $this->addLocationSection($xml, $receptor, $customer, 'Ubicacion');

        // Phone
        if ($customer->phone) {
            $telefono = $xml->createElement('Telefono');
            $receptor->appendChild($telefono);
            $this->addElement($xml, $telefono, 'CodigoPais', '506');
            $this->addElement($xml, $telefono, 'NumTelefono', preg_replace('/[^0-9]/', '', $customer->phone));
        }

        // Email
        if ($customer->email) {
            $this->addElement($xml, $receptor, 'CorreoElectronico', $customer->email);
        }
    }

    private function addLocationSection(\DOMDocument $xml, \DOMElement $parent, $entity, string $elementName): void
    {
        $ubicacion = $xml->createElement($elementName);
        $parent->appendChild($ubicacion);

        // Default values for Costa Rica provinces
        $provincia = $entity->state_id ?? '1';
        $canton = $entity->city_id ?? '01';
        $distrito = $entity->district_id ?? '01';

        $this->addElement($xml, $ubicacion, 'Provincia', str_pad((string) $provincia, 1, '0', STR_PAD_LEFT));
        $this->addElement($xml, $ubicacion, 'Canton', str_pad((string) $canton, 2, '0', STR_PAD_LEFT));
        $this->addElement($xml, $ubicacion, 'Distrito', str_pad((string) $distrito, 2, '0', STR_PAD_LEFT));

        $address = $entity->address ?? 'Sin Definir';
        $this->addElement($xml, $ubicacion, 'OtrasSenas', $this->sanitize($address, 250));
    }

    private function addPaymentMethods(\DOMDocument $xml, \DOMElement $root): void
    {
        $medioPago = $xml->createElement('MedioPago');
        $root->appendChild($medioPago);

        // Default to cash (01), can be extended based on payment data
        $paymentCode = '01'; // Efectivo

        if ($this->sale->payments && $this->sale->payments->count() > 0) {
            $payment = $this->sale->payments->first();
            $paymentCode = $this->mapPaymentMethod($payment->method ?? 'cash');
        }

        $medioPago->nodeValue = $paymentCode;
    }

    private function addLineItems(\DOMDocument $xml, \DOMElement $root): void
    {
        $detalleServicio = $xml->createElement('DetalleServicio');
        $root->appendChild($detalleServicio);

        $lineNumber = 1;

        foreach ($this->sale->items as $item) {
            $linea = $xml->createElement('LineaDetalle');
            $detalleServicio->appendChild($linea);

            $this->addElement($xml, $linea, 'NumeroLinea', (string) $lineNumber);

            // Product code (CABYS)
            $codigo = $xml->createElement('Codigo');
            $linea->appendChild($codigo);
            $this->addElement($xml, $codigo, 'Tipo', '04'); // Código interno
            $this->addElement($xml, $codigo, 'Codigo', $item->product->code ?? (string) $item->product_id);

            // CABYS code if available
            if ($item->product->cabys_code ?? null) {
                $this->addElement($xml, $linea, 'CodigoComercial', $item->product->cabys_code);
            }

            $this->addElement($xml, $linea, 'Cantidad', $this->formatNumber($item->quantity, 3));
            $this->addElement($xml, $linea, 'UnidadMedida', $this->getUnitCode($item));
            $this->addElement($xml, $linea, 'Detalle', $this->sanitize($item->product_name, 200));
            $this->addElement($xml, $linea, 'PrecioUnitario', $this->formatNumber($item->net_price, 5));
            $this->addElement($xml, $linea, 'MontoTotal', $this->formatNumber($item->subtotal, 5));

            // Discount
            if ($item->total_discount_amount > 0) {
                $descuento = $xml->createElement('Descuento');
                $linea->appendChild($descuento);
                $this->addElement($xml, $descuento, 'MontoDescuento', $this->formatNumber($item->total_discount_amount, 5));
                $this->addElement($xml, $descuento, 'NaturalezaDescuento', 'Descuento comercial');
            }

            $subtotalValue = $item->subtotal - ($item->total_discount_amount ?? 0);
            $this->addElement($xml, $linea, 'SubTotal', $this->formatNumber($subtotalValue, 5));

            // Taxes
            if ($item->total_tax_amount > 0) {
                $impuesto = $xml->createElement('Impuesto');
                $linea->appendChild($impuesto);
                $this->addElement($xml, $impuesto, 'Codigo', '01'); // IVA
                $this->addElement($xml, $impuesto, 'CodigoTarifa', '08'); // Tarifa general 13%
                $this->addElement($xml, $impuesto, 'Tarifa', '13.00');
                $this->addElement($xml, $impuesto, 'Monto', $this->formatNumber($item->total_tax_amount, 5));
            }

            $this->addElement($xml, $linea, 'MontoTotalLinea', $this->formatNumber($item->total, 5));

            $lineNumber++;
        }
    }

    private function addSummary(\DOMDocument $xml, \DOMElement $root): void
    {
        $resumen = $xml->createElement('ResumenFactura');
        $root->appendChild($resumen);

        $this->addElement($xml, $resumen, 'CodigoTipoMoneda', [
            'CodigoMoneda' => $this->sale->currency ?? 'CRC',
            'TipoCambio'   => $this->formatNumber($this->sale->exchange_rate ?? 1, 5),
        ]);

        // Calculate service vs merchandise totals
        $totalServicioGravado = 0;
        $totalServicioExento = 0;
        $totalMercanciasGravado = 0;
        $totalMercanciasExento = 0;

        foreach ($this->sale->items as $item) {
            $isService = ($item->product->type ?? 'product') === 'service';
            $hasTax = ($item->total_tax_amount ?? 0) > 0;
            $subtotal = $item->subtotal - ($item->total_discount_amount ?? 0);

            if ($isService) {
                if ($hasTax) {
                    $totalServicioGravado += $subtotal;
                } else {
                    $totalServicioExento += $subtotal;
                }
            } else {
                if ($hasTax) {
                    $totalMercanciasGravado += $subtotal;
                } else {
                    $totalMercanciasExento += $subtotal;
                }
            }
        }

        $this->addElement($xml, $resumen, 'TotalServGravados', $this->formatNumber($totalServicioGravado, 5));
        $this->addElement($xml, $resumen, 'TotalServExentos', $this->formatNumber($totalServicioExento, 5));
        $this->addElement($xml, $resumen, 'TotalMercanciasGravadas', $this->formatNumber($totalMercanciasGravado, 5));
        $this->addElement($xml, $resumen, 'TotalMercanciasExentas', $this->formatNumber($totalMercanciasExento, 5));

        $totalGravado = $totalServicioGravado + $totalMercanciasGravado;
        $totalExento = $totalServicioExento + $totalMercanciasExento;

        $this->addElement($xml, $resumen, 'TotalGravado', $this->formatNumber($totalGravado, 5));
        $this->addElement($xml, $resumen, 'TotalExento', $this->formatNumber($totalExento, 5));
        $this->addElement($xml, $resumen, 'TotalVenta', $this->formatNumber($this->sale->total, 5));
        $this->addElement($xml, $resumen, 'TotalDescuentos', $this->formatNumber($this->sale->total_discount_amount ?? 0, 5));
        $this->addElement($xml, $resumen, 'TotalVentaNeta', $this->formatNumber($this->sale->subtotal, 5));
        $this->addElement($xml, $resumen, 'TotalImpuesto', $this->formatNumber($this->sale->total_tax_amount ?? 0, 5));
        $this->addElement($xml, $resumen, 'TotalComprobante', $this->formatNumber($this->sale->grand_total, 5));
    }

    private function addReferenceInformation(\DOMDocument $xml, \DOMElement $root): void
    {
        if (! $this->reference) {
            return;
        }

        $infoRef = $xml->createElement('InformacionReferencia');
        $root->appendChild($infoRef);

        $this->addElement($xml, $infoRef, 'TipoDoc', '01'); // Reference to original invoice
        $this->addElement($xml, $infoRef, 'Numero', $this->reference->reference ?? (string) $this->reference->id);
        $this->addElement($xml, $infoRef, 'FechaEmision', $this->reference->created_at->toIso8601String());

        // Code: 01 = Anula, 02 = Corrige, 03 = Referencia
        $codigo = $this->documentType === '03' ? '01' : '02';
        $this->addElement($xml, $infoRef, 'Codigo', $codigo);

        $razon = $this->documentType === '03'
            ? 'Nota de crédito por devolución'
            : 'Corrección de documento';
        $this->addElement($xml, $infoRef, 'Razon', $razon);
    }

    private function addElement(\DOMDocument $xml, \DOMElement $parent, string $name, string|array $value): void
    {
        if (is_array($value)) {
            $element = $xml->createElement($name);
            $parent->appendChild($element);

            foreach ($value as $childName => $childValue) {
                $this->addElement($xml, $element, $childName, $childValue);
            }
        } else {
            $element = $xml->createElement($name);
            $element->nodeValue = $value;
            $parent->appendChild($element);
        }
    }

    private function getActivityCode(): string
    {
        return $this->sale->store->activity_code ?? '5630.0';
    }

    private function getCondicionVenta(): string
    {
        // 01 = Contado, 02 = Crédito, 03 = Consignación, etc.
        return '01';
    }

    private function getPlazoCredito(): string
    {
        return '0';
    }

    private function mapPaymentMethod(string $method): string
    {
        return match (strtolower($method)) {
            'cash' => '01',
            'card', 'credit' => '02',
            'cheque', 'check' => '03',
            'transfer' => '04',
            'other'    => '99',
            default    => '01',
        };
    }

    private function formatNumber(float|int|string $value, int $decimals): string
    {
        return number_format((float) $value, $decimals, '.', '');
    }

    private function sanitize(string $value, int $maxLength): string
    {
        // Remove special characters that might break XML
        $value = preg_replace('/[^\p{L}\p{N}\s\-.,()\/&]/u', '', $value);

        return mb_substr(trim($value), 0, $maxLength);
    }

    /**
     * Get the unit code for an item.
     */
    private function getUnitCode($item): string
    {
        $unit = $item->product->unit ?? null;

        if ($unit === null) {
            return 'Unid';
        }

        // If unit is an object (relationship), get the code or name
        if (is_object($unit)) {
            return $unit->code ?? $unit->name ?? 'Unid';
        }

        return (string) $unit;
    }
}
