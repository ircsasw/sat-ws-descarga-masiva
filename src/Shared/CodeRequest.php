<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use Eclipxe\MicroCatalog\MicroCatalog;

/**
 * @method bool isAccepted()
 * @method bool isExhausted()
 * @method bool isMaximumLimitReaded()
 * @method bool isEmptyResult()
 * @method bool isDuplicated()
 * @method string getMessage()
 * @method string getName()
 */
final class CodeRequest extends MicroCatalog
{
    protected const VALUES = [
        5000 => [
            'name' => 'Accepted',
            'message' => 'Solicitud recibida con éxito',
        ],
        5002 => [
            'name' => 'Exhausted',
            'message' => 'Se agotó las solicitudes de por vida: Máximo para solicitudes con los mismos parámetros',
        ],
        5003 => [
            'name' => 'MaximumLimitReaded',
            'message' => 'Tope máximo: Indica que se está superando el tope máximo de CFDI o Metadata',
        ],
        5004 => [
            'name' => 'EmptyResult',
            'message' => 'No se encontró la información: Indica que no generó paquetes por falta de información.',
        ],
        5005 => [
            'name' => 'Duplicated',
            'message' => 'Solicitud duplicada: Si existe una solicitud vigente con los mismos parámetros',
        ],
    ];

    public static function getEntriesArray(): array
    {
        return self::VALUES;
    }

    public function getEntryValueOnUndefined()
    {
        return ['name' => 'Unknown', 'message' => 'Desconocida'];
    }

    public function getEntryId(): string
    {
        return $this->getName();
    }

    public function getValue(): int
    {
        return intval($this->getEntryIndex());
    }
}
