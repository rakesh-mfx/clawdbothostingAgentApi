<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateJwtKeys extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'agent:generate-keys
                            {--force : Overwrite existing keys}
                            {--bits=4096 : RSA key size in bits}';

    /**
     * The console command description.
     */
    protected $description = 'Generate RSA key pair for JWT signing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $privateKeyPath = config('agent.jwt.private_key_path');
        $publicKeyPath = config('agent.jwt.public_key_path');

        // Resolve paths relative to base if not absolute
        if (!str_starts_with($privateKeyPath, '/') && !preg_match('/^[A-Z]:/i', $privateKeyPath)) {
            $privateKeyPath = base_path($privateKeyPath);
        }
        if (!str_starts_with($publicKeyPath, '/') && !preg_match('/^[A-Z]:/i', $publicKeyPath)) {
            $publicKeyPath = base_path($publicKeyPath);
        }

        // Check if keys already exist
        if (File::exists($privateKeyPath) && !$this->option('force')) {
            $this->error('Keys already exist. Use --force to overwrite.');

            return self::FAILURE;
        }

        $bits = (int) $this->option('bits');
        $this->info("Generating {$bits}-bit RSA key pair...");

        // Create directory if it doesn't exist
        $keyDir = dirname($privateKeyPath);
        if (!File::exists($keyDir)) {
            File::makeDirectory($keyDir, 0700, true);
            $this->line("Created directory: {$keyDir}");
        }

        // Generate the key pair
        $config = [
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $resource = openssl_pkey_new($config);

        if ($resource === false) {
            $this->error('Failed to generate key pair: '.openssl_error_string());

            return self::FAILURE;
        }

        // Extract private key
        $privateKey = '';
        if (!openssl_pkey_export($resource, $privateKey)) {
            $this->error('Failed to export private key: '.openssl_error_string());

            return self::FAILURE;
        }

        // Extract public key
        $keyDetails = openssl_pkey_get_details($resource);
        if ($keyDetails === false) {
            $this->error('Failed to get key details: '.openssl_error_string());

            return self::FAILURE;
        }

        $publicKey = $keyDetails['key'];

        // Save keys
        File::put($privateKeyPath, $privateKey);
        File::chmod($privateKeyPath, 0600);
        $this->info("Private key saved to: {$privateKeyPath}");

        File::put($publicKeyPath, $publicKey);
        File::chmod($publicKeyPath, 0644);
        $this->info("Public key saved to: {$publicKeyPath}");

        $this->newLine();
        $this->info('JWT key pair generated successfully!');
        $this->line('');
        $this->line('Key ID: '.config('agent.jwt.key_id'));
        $this->line('');
        $this->warn('Make sure to keep the private key secure and never commit it to version control.');

        return self::SUCCESS;
    }
}
