<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

uses(TestCase::class);
uses(DatabaseTransactions::class);

describe('module.mercadopago', function () {
    describe('endpoints', function () {
        it('Endpoint de status de pagamento deve estar respondendo')->skip();
    });
});
