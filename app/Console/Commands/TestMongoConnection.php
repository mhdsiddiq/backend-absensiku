<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AbsensiStats;
use App\Models\DashboardAbsensi;
use MongoDB\Laravel\Connection;
use Illuminate\Support\Facades\DB;

class TestMongoConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mongo:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test koneksi MongoDB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Testing MongoDB connection...');

            // Test koneksi
            $connection = DB::connection('mongodb');
            $this->info('✓ MongoDB connection successful');

            // Test database
            $database = $connection->getMongoClient()->selectDatabase('absensi_db');
            $this->info('✓ Database "absensi_db" accessible');

            // Test collection
            $collection = $database->selectCollection('dashboard_absensi');
            $this->info('✓ Collection "dashboard_absensi" accessible');

            // Test insert sample data
            $sampleData = [
                'tanggal' => '2024-07-16',
                'jumlah_karyawan' => 10,
                'jumlah_absensi' => 8,
                'jumlah_tidak_hadir' => 2,
                'persentase_kehadiran' => 80.00,
                'created_at' => now(),
                'updated_at' => now()
            ];

            $testStats = new DashboardAbsensi($sampleData);
            $testStats->save();

            $this->info('✓ Sample data inserted successfully');
            $this->info('Document ID: ' . $testStats->_id);

            // Test query
            $count = DashboardAbsensi::count();
            $this->info("✓ Total documents in collection: {$count}");

            // Delete test data
            $testStats->delete();
            $this->info('✓ Test data cleaned up');

            $this->info('🎉 MongoDB connection test completed successfully!');

        } catch (\Exception $e) {
            $this->error('❌ MongoDB connection failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
