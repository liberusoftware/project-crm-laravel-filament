<?php

declare(strict_types=1);

use Liberu\CRM\Core\Support\PiiEncryptionBackfill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Encrypt Contact.phone_number + Company.phone_number at rest. Neither is looked
 * up by value (no equality queries / indexes), so no blind index — just widen to
 * text and encrypt existing rows in place. See Liberu\CRM\Core\Support\PiiEncryptionBackfill.
 */
return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            // Keep nullable (a widened NOT-NULL column would reject phone-less
            // contacts — the credential-encryption lesson).
            $table->text('phone_number')->nullable()->change();
        });
        Schema::table('companies', function (Blueprint $table): void {
            $table->text('phone_number')->nullable()->change();
        });

        PiiEncryptionBackfill::encryptColumn('contacts', 'phone_number');
        PiiEncryptionBackfill::encryptColumn('companies', 'phone_number');
    }

    public function down(): void
    {
        // Irreversible for the encrypted values.
    }
};
