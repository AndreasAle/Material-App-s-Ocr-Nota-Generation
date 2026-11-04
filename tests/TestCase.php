<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerMysqlFunctionsForSqlite();
    }

    private function registerMysqlFunctionsForSqlite(): void
    {
        // RefreshDatabase membuat koneksi baru setelah boot, jadi daftarkan ulang
        \App\Support\SqliteMysqlFunctions::register();
    }
}
