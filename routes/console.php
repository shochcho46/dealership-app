<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('vendors:fill-bangla-names {--force : Re-generate even if a Bangla name already exists}', function () {
    $query = \Modules\Product\Models\Vendor::query()->orderBy('id');
    if (! $this->option('force')) {
        $query->where(function ($q) {
            $q->whereNull('shop_name_bn')->orWhere('shop_name_bn', '');
        });
    }

    $total = (clone $query)->count();
    if ($total === 0) {
        $this->info('Nothing to do — all vendors already have a Bangla name.');
        return;
    }

    $this->info("Generating Bangla names for {$total} vendor(s)...");
    $bar = $this->output->createProgressBar($total);
    $failed = [];
    $done = 0;

    $query->chunkById(100, function ($vendors) use ($bar, &$failed, &$done) {
        foreach ($vendors as $vendor) {
            $bn = \Modules\Product\Services\BanglaTransliterator::toBangla($vendor->shop_name);
            if ($bn) {
                $vendor->forceFill(['shop_name_bn' => $bn])->saveQuietly();
                $done++;
            } else {
                $failed[] = "#{$vendor->id} {$vendor->shop_name}";
            }
            $bar->advance();
            usleep(200000);
        }
    });

    $bar->finish();
    $this->newLine(2);
    $this->info("Updated: {$done}");
    if ($failed) {
        $this->warn('Failed (' . count($failed) . '):');
        foreach ($failed as $f) {
            $this->line('  ' . $f);
        }
    }
})->purpose('Auto-fill vendors.shop_name_bn by transliterating shop_name to Bangla');
