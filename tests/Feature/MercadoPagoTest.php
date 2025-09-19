<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

uses(TestCase::class);
uses(DatabaseTransactions::class);

describe('module.mercadopago', function (): void {
    describe('endpoints', function (): void {
        it('Endpoint de status de pagamento deve estar respondendo')->skip();
    });
});
