<?php

namespace Modules\Product\Models;

use App\Models\Admin;
use App\Services\Frontend\CatalogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Admin\Entities\Business;

class WebsiteInquiry extends Model
{
    public const STATUS_NEW = 0;
    public const STATUS_CONTACTED = 1;
    public const STATUS_CLOSED = 2;

    public const STATUSES = [
        self::STATUS_NEW       => 'New',
        self::STATUS_CONTACTED => 'Contacted',
        self::STATUS_CLOSED    => 'Closed',
    ];

    /** Max words allowed in the confirmation SMS. */
    public const SMS_MAX_WORDS = 60;

    protected $fillable = [
        'name',
        'shop_name',
        'mobile',
        'area',
        'delivery_type',
        'message',
        'product_id',
        'items',
        'subtotal',
        'delivery_fee',
        'total',
        'invoice_code',
        'status',
        'handled_by',
        'contacted_at',
        'sms_sent_at',
        'sms_status',
        'sms_response',
        'ip',
    ];

    public const DELIVERY_TYPES = [
        'shop' => 'Shop',
        'home' => 'Home',
    ];

    protected $casts = [
        'items'        => 'array',
        'status'       => 'integer',
        'subtotal'     => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total'        => 'decimal:2',
        'contacted_at' => 'datetime',
        'sms_sent_at'  => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (WebsiteInquiry $inquiry) {
            if (empty($inquiry->invoice_code)) {
                $inquiry->invoice_code = self::newInvoiceCode();
            }
        });

        // Record which admin changed the status, and when it was first contacted.
        static::updating(function (WebsiteInquiry $inquiry) {
            if ($inquiry->isDirty('status') && Auth::guard('admin')->check()) {
                $inquiry->handled_by = Auth::guard('admin')->id();
            }
            if ($inquiry->isDirty('status') && $inquiry->status === self::STATUS_CONTACTED && !$inquiry->contacted_at) {
                $inquiry->contacted_at = now();
            }
        });
    }

    public static function newInvoiceCode(): string
    {
        do {
            $code = Str::random(8);
        } while (static::where('invoice_code', $code)->exists());

        return $code;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function handledBy()
    {
        return $this->belongsTo(Admin::class, 'handled_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'New';
    }

    public function getWhatsappNumberAttribute(): string
    {
        $digits = preg_replace('/\D/', '', $this->mobile);
        if (str_starts_with($digits, '01')) {
            $digits = '88' . $digits;
        }
        return $digits;
    }

    /** "INQ-000125" */
    public function getInvoiceNumberAttribute(): string
    {
        return 'INQ-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /** Public short link sent by SMS. */
    public function getInvoiceUrlAttribute(): ?string
    {
        if (!$this->invoice_code) {
            return null;
        }

        return route('inquiry.invoice', $this->invoice_code);
    }

    public function getHasInvoiceAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_CONTACTED, self::STATUS_CLOSED], true);
    }

    /** Once confirmed (Contacted) or Closed, prices, quantities and the delivery fee can't change. */
    public function isPriceLocked(): bool
    {
        return (int) $this->status !== self::STATUS_NEW;
    }

    /** A confirmed order can never go back to New (that would unlock its prices). */
    public function canReopen(): bool
    {
        return $this->contacted_at === null;
    }

    /**
     * Build one priced line from a catalog product array.
     */
    public static function lineFromCatalog(array $product, int $qty): array
    {
        // Website orders are in pieces: qty = number of pieces, price per piece.
        $unitPrice = (float) ($product['price_piece'] ?? 0);
        $mrpUnit   = $product['mrp_piece'] ?? null;

        return [
            'product_id' => $product['id'],
            'name'       => $product['name'],
            'cases'      => $qty,
            'pack_unit'  => 'piece',
            'per_pack'   => null,
            'unit_price' => round($unitPrice, 2),
            'mrp_unit'   => $mrpUnit ? round((float) $mrpUnit, 2) : null,
            'line_total' => round($unitPrice * $qty, 2),
        ];
    }

    /**
     * Make sure every line has a price (old inquiries were saved without prices,
     * and single-product inquiries had no items). Saves when something was filled in.
     */
    public function ensurePriced(): self
    {
        $catalog = app(CatalogService::class);
        $items   = $this->items ?: [];
        $changed = false;

        if (!$items && $this->product_id) {
            $p = $catalog->find((int) $this->product_id);
            $product = $p ?: optional(Product::find($this->product_id), fn ($m) => ['id' => $m->id, 'name' => $m->name]);
            if ($product) {
                $items = [$p ? self::lineFromCatalog($p, 1) : [
                    'product_id' => $product['id'], 'name' => $product['name'], 'cases' => 1, 'pack_unit' => 'piece',
                    'per_pack' => null, 'unit_price' => 0, 'mrp_unit' => null, 'line_total' => 0,
                ]];
                $changed = true;
            }
        }

        foreach ($items as $i => $item) {
            if (!array_key_exists('unit_price', $item)) {
                $p = $catalog->find((int) ($item['product_id'] ?? 0));
                $qty = (int) ($item['cases'] ?? 1);
                $items[$i] = $p ? array_merge(self::lineFromCatalog($p, $qty), ['name' => $item['name'] ?? $p['name']])
                    : array_merge($item, ['unit_price' => 0, 'mrp_unit' => null, 'line_total' => 0]);
                $changed = true;
            }
        }

        if ($changed || ($items && (float) $this->subtotal == 0.0)) {
            $this->items = $items;
            $this->recalculate();
            if ($this->exists) {
                $this->saveQuietly();
            }
        }

        return $this;
    }

    /** Recompute line totals, subtotal and total (does not save). */
    public function recalculate(): self
    {
        $items = collect($this->items ?: [])->map(function ($item) {
            $item['cases']      = max(1, (int) ($item['cases'] ?? 1));
            $item['unit_price'] = round((float) ($item['unit_price'] ?? 0), 2);
            $item['line_total'] = round($item['unit_price'] * $item['cases'], 2);
            return $item;
        })->values()->all();

        $this->items    = $items ?: null;
        $this->subtotal = round(collect($items)->sum('line_total'), 2);
        $this->total    = round((float) $this->subtotal + (float) $this->delivery_fee, 2);

        return $this;
    }

    /**
     * Product models for the items (for images), keyed by id.
     */
    public function productModels(): Collection
    {
        $ids = collect($this->items ?: [])->pluck('product_id')->push($this->product_id)->filter()->unique();

        return $ids->isEmpty() ? collect() : Product::with('media')->whereIn('id', $ids)->get()->keyBy('id');
    }

    /**
     * Short English confirmation SMS (≤ 60 words, plain GSM characters → cheapest).
     */
    public function confirmationSms(?Business $business = null): string
    {
        $business = $business ?? Business::first();
        $first    = Str::of($this->name)->squish()->before(' ')->limit(15, '')->toString() ?: 'Customer';
        $money    = fn ($v) => 'Tk' . rtrim(rtrim(number_format((float) $v, 2), '0'), '.');

        $lines = [
            "Dear {$first}, your order #{$this->id} is confirmed.",
            'Products: ' . $money($this->subtotal),
            'Delivery: ' . $money($this->delivery_fee),
            'Total: ' . $money($this->total),
            'Invoice: ' . $this->invoice_url,
        ];
        if ($business && $business->shortName()) {
            $lines[] = '- ' . Str::limit($business->shortName(), 30, '');
        }

        $text = implode("\n", $lines);

        // Hard cap: never more than SMS_MAX_WORDS words.
        $words = preg_split('/\s+/', trim($text));
        if (count($words) > self::SMS_MAX_WORDS) {
            $text = implode(' ', array_slice($words, 0, self::SMS_MAX_WORDS));
        }

        return $text;
    }
}
