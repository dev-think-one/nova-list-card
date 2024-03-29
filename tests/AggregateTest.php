<?php

namespace NovaListCard\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AggregateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        DB::table('orders')->insert([
            'reference' => '12345678',
        ]);

        DB::table('product_orders')->insert([
            ['name' => 'imac', 'quantity' => '1', 'price' => '1500', 'order_id' => 1],
            ['name' => 'galaxy s9', 'quantity' => '2', 'price' => '1000', 'order_id' => 1],
            ['name' => 'Apple Watch', 'quantity' => '3', 'price' => '1200', 'order_id' => 1],
        ]);
    }

    /** @test */
    public function with_count()
    {
        $actual = Order::withAggregate('products', '*', 'count')->first();

        $expected = DB::select(
            'select (select count(*) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_count" from "orders"'
        )[0];

        $this->assertEquals($expected->products_count, $actual->products_count);
    }

    /** @test */
    public function with_sum()
    {
        $actual = Order::query()->withSum('products', 'quantity')->first();

        $expected = DB::select(
            'select (select sum(quantity) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_sum" from "orders"'
        )[0];

        $this->assertEquals($expected->products_sum, $actual->products_sum_quantity);
    }

    /** @test */
    public function with_avg()
    {
        $actual = Order::withAvg('products', 'price')->first();

        $expected = DB::select(
            'select (select avg(price) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_avg" from "orders"'
        )[0];

        $this->assertEquals($expected->products_avg, $actual->products_avg_price);
    }

    /** @test */
    public function with_min()
    {
        $actual = Order::withMin('products', 'price')->first();

        $expected = DB::select(
            'select (select min(price) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_min" from "orders"'
        )[0];

        $this->assertEquals($expected->products_min, $actual->products_min_price);
    }

    /** @test */
    public function with_max()
    {
        $actual = Order::withMax('products', 'price')->first();

        $expected = DB::select(
            'select (select max(price) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_max" from "orders"'
        )[0];

        $this->assertEquals($expected->products_max, $actual->products_max_price);
    }

    /** @test */
    public function with_min_and_alias()
    {
        $actual = Order::withMin('products as min_price', 'price')->first();

        $expected = DB::select(
            'select (select min(price) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "min_price" from "orders"'
        )[0];

        $this->assertEquals($expected->min_price, $actual->min_price);
    }

    /** @test */
    public function with_max_with_alias_with_where()
    {
        $actual = Order::withMax(['products as higher_price' => function ($query) {
            $query->where('quantity', '>', 1);
        }], 'price')->first();

        $expected = DB::select(
            'select (select max(price) from "product_orders" where "orders"."id" = "product_orders"."order_id" and "quantity" > 1) as "higher_price" from "orders"'
        )[0];

        $this->assertEquals($expected->higher_price, $actual->higher_price);
    }

    /** @test */
    public function with_sum_prices_and_count_quantity_with_aliases()
    {
        $actual = Order::withSum('products as order_price', 'price')->withSum('products as order_products_count', 'quantity')->withCount('products')->first();

        $expected = DB::select(
            'select (select sum(price) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "order_price", (select sum(quantity) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "order_products_count", (select count(*) from "product_orders" where "orders"."id" = "product_orders"."order_id") as "products_count" from "orders"'
        )[0];

        $this->assertEquals($expected->order_price, $actual->order_price);
        $this->assertEquals($expected->products_count, $actual->products_count);
        $this->assertEquals($expected->order_products_count, $actual->order_products_count);
    }
}

class Order extends Model
{
    public function products()
    {
        return $this->hasMany(ProductOrder::class, 'order_id');
    }
}

class ProductOrder extends Model
{
    //
}
