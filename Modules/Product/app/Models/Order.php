<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;

use function PHPSTORM_META\type;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'admin_id',
        'place_by',
        'total_amount',
        'paid_amount',
        'total_quantity',
        'total_discount_amount',
        'total_return_quantity',
        'order_status_id',
        'payment_status',
        'total_damage_quantity',
        'total_lost_quantity',
        'vendor_id',
        'paid_at',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'total_quantity' => 'integer',
        'total_discount_amount' => 'decimal:2',
        'total_return_quantity' => 'integer',
        'payment_status' => 'integer',
        'total_damage_quantity' => 'integer',
        'total_lost_quantity' => 'integer',
        'latitude' => 'string',
        'longitude' => 'string',
    ];

    /**
     * Boot method to generate invoice ID automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->invoice_id)) {
                $order->invoice_id = $order->generateInvoiceId();
            }
        });
    }

    /**
     * Generate unique invoice ID in format: inv-ss-XXXX-ID
     */
    public function generateInvoiceId()
    {
        $vendorCode = 'abc'; // Default if no vendor
            if ($this->vendor_id) {
                $vendor = Vendor::find($this->vendor_id);
                if ($vendor && !empty($vendor->shop_name)) {
                // $vendorCode = strtoupper(substr($vendor->shop_name, 0, 2));

                $vendorCode = mb_strtoupper(
                    mb_substr($vendor->shop_name, 0, 2, 'UTF-8'),
                    'UTF-8'
                );

                }
            }
        $datePart = now()->format('d-m-y');
        $randomDigits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $lastId = static::max('id') ?? 0;
        $nextId = $lastId + 1;

        return "SSE-{$datePart}-{$randomDigits}-{$vendorCode}-{$nextId}";
    }

    /**
     * Relationship with Admin
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Relationship with Admin who placed the order
     */
    public function placeBy()
    {
        return $this->belongsTo(Admin::class, 'place_by');
    }

    /**
     * Relationship with OrderStatus
     */
    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    /**
     * Relationship with Vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Relationship with OrderItems
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function vendorAccounts()
    {
        return $this->hasMany(VendorAccount::class);
    }

    /**
     * Scope for active orders
     */
    public function scopeActive($query)
    {
        return $query->whereHas('orderStatus', function ($q) {
            $q->where('name', '!=', 'Cancelled');
        });
    }

    /**
     * Scope for cancelled orders
     */
    public function scopeCancelled($query)
    {
        return $query->whereHas('orderStatus', function ($q) {
            $q->where('name', 'Cancelled');
        });
    }

    /**
     * Get net amount after discount
     */
    public function getNetAmountAttribute()
    {
        return $this->total_amount ;
    }

    public function getOrderPaymentAttribute()
    {
        return $this->vendorAccounts->where('type',2)->sum('amount'); ;
    }


    public function getOrderDueAttribute()
    {
        return (float)($this->total_amount  - $this->paid_amount);
    }

    /**
     * Get total profit from order items
     */
    public function getTotalProfitAttribute()
    {

        return $this->orderItems->sum(function ($item) {
        return $item->orderItemStocks->sum(function ($stock) {
            return ($stock->actual_profit ?? 0) - ($stock->discount_amount ?? 0);
        });
    });
    }

    /**
     * Get order status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        if (!$this->orderStatus) {
            return 'bg-secondary';
        }

        switch (strtolower($this->orderStatus->name)) {
            case 'pending':
                return 'bg-warning';
            case 'processing':
                return 'bg-info';
            case 'completed':
                return 'bg-success';
            case 'cancelled':
                return 'bg-danger';
            case 'delivered':
                return 'bg-primary';
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Get payment status text
     */
    public function getPaymentStatusTextAttribute()
    {
        switch ($this->payment_status) {
            case 0:
                return 'Unpaid';
            case 1:
                return 'Partial Paid';
            case 2:
                return 'Paid';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get payment status badge class
     */
    public function getPaymentStatusBadgeClassAttribute()
    {
        switch ($this->payment_status) {
            case 0:
                return 'bg-danger';
            case 1:
                return 'bg-warning';
            case 2:
                return 'bg-success';
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        // return $this->orderStatus &&
        //        !in_array(strtolower($this->orderStatus->name), ['delivered', 'shipped', 'cancelled']);

         $statusName = strtolower(optional($this->orderStatus)->name);

        return $this->payment_status == 0
            && !in_array($statusName, ['delivered', 'shipped', 'cancelled']);
    }

    /**
     * Cancel order and restore stock
     */
    public function cancelOrder()
    {
        $this->load('orderItems.orderItemStocks.stock');
        if (!$this->canBeCancelled()) {
            return false;
        }

        // Restore stock quantities from order item stocks
        foreach ($this->orderItems as $item) {
            foreach ($item->orderItemStocks as $orderItemStock) {
                $stock = $orderItemStock->stock;
                if ($stock) {
                    // Reduce frozen quantity to restore availability
                    $stock->froze_quantity = max(0, $stock->froze_quantity - $orderItemStock->quantity);
                    $stock->save();
                }
            }
        }

        // Update order status to cancelled
        $cancelledStatus = OrderStatus::where('name', 'cancelled')->first();
        if ($cancelledStatus) {
            $this->order_status_id = $cancelledStatus->id;
            $this->save();
        }

        return true;
    }

    /**
     * Calculate distance from vendor location using Haversine formula
     * Returns distance in meters, or null if location data is missing
     * 
     * @return float|null Distance in meters
     */
    public function getDistanceFromVendor()
    {
        // Check if all required location data exists
        if (!$this->latitude || !$this->longitude || !$this->vendor || !$this->vendor->lat || !$this->vendor->long) {
            return null;
        }

        $earthRadius = 6371000; // Earth's radius in meters

        // Convert degrees to radians
        $lat1 = deg2rad(floatval($this->latitude));
        $lon1 = deg2rad(floatval($this->longitude));
        $lat2 = deg2rad(floatval($this->vendor->lat));
        $lon2 = deg2rad(floatval($this->vendor->long));

        // Calculate differences
        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        // Haversine formula
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($lat1) * cos($lat2) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance; // Distance in meters
    }

    /**
     * Get formatted distance from vendor
     * Returns formatted string like "250 meters" or "1.25 km"
     * 
     * @return string|null
     */
    public function getFormattedDistance()
    {
        $distance = $this->getDistanceFromVendor();

        if ($distance === null) {
            return null;
        }

        // If distance is greater than 500 meters, convert to kilometers
        if ($distance > 500) {
            return number_format($distance / 1000, 2) . ' km';
        }

        return round($distance) . ' meters';
    }

    /**
     * Check if order and vendor have location data
     * 
     * @return bool
     */
    public function hasLocationData()
    {
        return !empty($this->latitude) && 
               !empty($this->longitude) && 
               $this->vendor && 
               !empty($this->vendor->lat) && 
               !empty($this->vendor->long);
    }

    /**
     * Get Google Maps directions URL
     * Returns URL to show route between vendor and order location with markers and distance
     * 
     * @return string|null
     */
    public function getGoogleMapsUrl()
    {
        if (!$this->hasLocationData()) {
            return null;
        }

        // Origin: Vendor location (starting point)
        $origin = $this->vendor->lat . ',' . $this->vendor->long;
        
        // Destination: Order placement location (end point)
        $destination = $this->latitude . ',' . $this->longitude;

        // Google Maps Directions API URL (no API key required)
        return 'https://www.google.com/maps/dir/?api=1&origin=' . urlencode($origin) . '&destination=' . urlencode($destination);
    }
}
