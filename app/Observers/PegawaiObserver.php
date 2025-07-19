<?php

namespace App\Observers;

use App\Models\Pegawai;
use Elastic\Elasticsearch\Client;

class PegawaiObserver
{
    private $elasticsearch;

    public function __construct(Client $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    public function created(Pegawai $pegawai): void
    {
        $this->elasticsearch->index([
            'index' => 'pegawai',
            'id' => $pegawai->id,
            'body' => $pegawai->toArray(),
        ]);
    }

    public function updated(Pegawai $pegawai): void
    {
        $this->elasticsearch->update([
            'index' => 'pegawai',
            'id' => $pegawai->id,
            'body' => [
                'doc' => $pegawai->toArray(),
            ],
        ]);
    }

    public function deleted(Pegawai $pegawai): void
    {
        $this->elasticsearch->delete([
            'index' => 'pegawai',
            'id' => $pegawai->id,
        ]);
    }

    public function restored(Pegawai $pegawai): void
    {
        //
    }

    public function forceDeleted(Pegawai $pegawai): void
    {
        //
    }
}