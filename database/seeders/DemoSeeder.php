<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * A realistic, browsable demo business — for trying out the UI, not for
 * tests (those build their own fixtures via factories). Safe to re-run:
 * deletes and recreates "Naija Threads" each time rather than duplicating it.
 *
 *   php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        Business::where('slug', 'naija-threads-demo')->get()->each(function (Business $old) {
            $old->users()->delete();
            OrderItem::whereHas('order', fn ($q) => $q->where('business_id', $old->id))->delete();
            $old->orders()->delete();
            $old->customers()->delete();
            $old->products()->delete();
            $old->categories()->delete();
            $old->locations()->delete();
            $old->delete();
        });

        $business = Business::create([
            'name' => 'Naija Threads',
            'slug' => 'naija-threads-demo',
            'email' => 'hello@naijathreads.test',
            'phone' => '+2348012345678',
            'whatsapp_number' => '+2348012345678',
            'description' => 'Everyday and occasion wear, sourced and styled in Lagos.',
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
            'status' => 'active',
        ]);

        $owner = User::create([
            'business_id' => $business->id,
            'name' => 'Amara Chukwu',
            'email' => 'demo@zwenko.test',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $owner->assignRole('Owner');

        BusinessLocation::create([
            'business_id' => $business->id,
            'name' => 'Main Store — Lekki',
            'is_default' => true,
            'status' => 'active',
        ]);

        $categories = collect([
            'Dresses' => Category::create(['business_id' => $business->id, 'name' => 'Dresses', 'status' => 'active']),
            'Shoes' => Category::create(['business_id' => $business->id, 'name' => 'Shoes', 'status' => 'active']),
            'Accessories' => Category::create(['business_id' => $business->id, 'name' => 'Accessories', 'status' => 'active']),
        ]);

        // A deliberate mix: healthy stock, low stock (at/under threshold),
        // and out of stock — so Products/Inventory/Dashboard all have
        // something real to show for every state.
        $products = [
            ['name' => 'Ankara Maxi Dress', 'sku' => 'DRS-001', 'price' => 15000, 'stock' => 25, 'threshold' => 5, 'cat' => 'Dresses', 'featured' => true],
            ['name' => 'Lace Evening Gown', 'sku' => 'DRS-002', 'price' => 45000, 'stock' => 8, 'threshold' => 5, 'cat' => 'Dresses', 'featured' => false],
            ['name' => 'Denim Jacket Dress', 'sku' => 'DRS-003', 'price' => 12000, 'stock' => 3, 'threshold' => 5, 'cat' => 'Dresses', 'featured' => false],
            ['name' => 'Leather Sandals', 'sku' => 'SHO-001', 'price' => 8500, 'stock' => 40, 'threshold' => 8, 'cat' => 'Shoes', 'featured' => true],
            ['name' => 'Canvas Sneakers', 'sku' => 'SHO-002', 'price' => 11000, 'stock' => 0, 'threshold' => 5, 'cat' => 'Shoes', 'featured' => false],
            ['name' => 'Ankle Boots', 'sku' => 'SHO-003', 'price' => 18000, 'stock' => 15, 'threshold' => 5, 'cat' => 'Shoes', 'featured' => true],
            ['name' => 'Beaded Necklace Set', 'sku' => 'ACC-001', 'price' => 4500, 'stock' => 60, 'threshold' => 10, 'cat' => 'Accessories', 'featured' => false],
            ['name' => 'Leather Handbag', 'sku' => 'ACC-002', 'price' => 22000, 'stock' => 4, 'threshold' => 5, 'cat' => 'Accessories', 'featured' => true],
        ];

        $productModels = collect($products)->map(fn (array $p) => Product::create([
            'business_id' => $business->id,
            'category_id' => $categories[$p['cat']]->id,
            'name' => $p['name'],
            'sku' => $p['sku'],
            'price' => $p['price'],
            'stock_quantity' => $p['stock'],
            'low_stock_threshold' => $p['threshold'],
            'status' => 'active',
            'featured' => $p['featured'],
        ]));

        $customers = collect([
            ['name' => 'Blessing Okoro', 'phone' => '+2348099911001', 'email' => 'blessing@example.com'],
            ['name' => 'Tunde Bakare', 'phone' => '+2348099911002', 'email' => null],
            ['name' => 'Ngozi Eze', 'phone' => '+2348099911003', 'email' => 'ngozi@example.com'],
            ['name' => 'Ibrahim Musa', 'phone' => '+2348099911004', 'email' => null],
        ])->map(fn (array $c) => Customer::create($c + ['business_id' => $business->id]));

        $orders = [
            ['customer' => 0, 'items' => [[0, 1]], 'status' => 'completed', 'payment' => 'paid', 'source' => 'whatsapp', 'method' => 'whatsapp', 'days_ago' => 6],
            ['customer' => 1, 'items' => [[3, 1], [6, 2]], 'status' => 'pending', 'payment' => 'pending', 'source' => 'whatsapp', 'method' => 'whatsapp', 'days_ago' => 0],
            ['customer' => 2, 'items' => [[1, 1]], 'status' => 'processing', 'payment' => 'paid', 'source' => 'storefront', 'method' => 'paystack', 'days_ago' => 1],
            ['customer' => 3, 'items' => [[5, 1], [7, 1]], 'status' => 'completed', 'payment' => 'paid', 'source' => 'storefront', 'method' => 'paystack', 'days_ago' => 3],
            ['customer' => 0, 'items' => [[2, 1]], 'status' => 'cancelled', 'payment' => 'pending', 'source' => 'whatsapp', 'method' => 'whatsapp', 'days_ago' => 4],
        ];

        foreach ($orders as $i => $o) {
            $items = collect($o['items'])->map(fn (array $line) => [
                'product' => $productModels[$line[0]],
                'qty' => $line[1],
            ]);
            $subtotal = $items->sum(fn (array $line) => $line['product']->price * $line['qty']);

            $order = Order::create([
                'business_id' => $business->id,
                'customer_id' => $customers[$o['customer']]->id,
                'order_number' => 'ORD-DEMO-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'currency' => 'NGN',
                'order_status' => $o['status'],
                'payment_status' => $o['payment'],
                'payment_method' => $o['method'],
                'source' => $o['source'],
                'created_at' => now()->subDays($o['days_ago']),
                'updated_at' => now()->subDays($o['days_ago']),
            ]);

            foreach ($items as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'quantity' => $line['qty'],
                    'price' => $line['product']->price,
                    'subtotal' => $line['product']->price * $line['qty'],
                ]);
            }
        }

        $this->command?->info('Demo business ready: Naija Threads');
        $this->command?->info('Login: demo@zwenko.test / password');
        $this->command?->info('Storefront: /store/'.$business->slug);
    }
}
