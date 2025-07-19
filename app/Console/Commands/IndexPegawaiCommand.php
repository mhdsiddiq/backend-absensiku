<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

class IndexPegawaiCommand extends Command
{
    protected $signature = 'elasticsearch:index-pegawai';
    protected $description = 'Mengindeks semua data pegawai ke Elasticsearch';

    private $elasticsearch;

    public function __construct(Client $elasticsearch)
    {
        parent::__construct();
        $this->elasticsearch = $elasticsearch;
    }

    public function handle()
    {
        $this->info('Mengindeks data pegawai...');

        $pegawai = Pegawai::all();

        foreach ($pegawai as $p)
        {
            $this->elasticsearch->index([
                'index' => 'pegawai',
                'id' => $p->id,
                'body' => $p->toArray(),
            ]);
            $this->output->write('.');
        }

        $this->info('\nSelesai mengindeks data pegawai.');
    }
}