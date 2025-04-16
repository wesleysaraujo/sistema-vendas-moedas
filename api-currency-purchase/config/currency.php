<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Porcentagem de taxa de serviço
    |--------------------------------------------------------------------------
    |
    | Este valor é a porcentagem que será cobrada como uma taxa de serviço
    | para transações de câmbio. Esta taxa é aplicada ao
    | Valor base da transação na moeda local (BRL).
    |
    */
    'fee_percentage' => env('SERVICE_FEE_PERCENTAGE', 2.00),

    /*
    |--------------------------------------------------------------------------
    | Moeda Padrão
    |--------------------------------------------------------------------------
    |
    | Esta é a moeda padrão que será usada quando não houver moeda especificado.
    |
    */
    'default_currency' => env('DEFAULT_CURRENCY', 'BRL'),

    /*
    |--------------------------------------------------------------------------
    | Duração do cache
    |--------------------------------------------------------------------------
    |
    | Este é o número de minutos que as taxas de câmbio devem ser armazenadas em cache.
    |
    */
    'cache_duration' => env('CURRENCY_CACHE_DURATION', 10), // 10min
];
