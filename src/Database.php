<?php

declare(strict_types=1);

namespace App;

use RedBeanPHP\R;

class Database
{
    public static function connect(): void
    {
        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');

        R::setup(
            "mysql:host={$host};port={$port};dbname={$name}",
            $user,
            $pass
        );

        R::freeze(true);
    }

    public static function seed(): void
    {
        R::freeze(false);

        // Categories
        if (R::count('category') === 0) {
            foreach ([
                ['name' => 'Custom Cakes',    'description' => 'Personalized cakes for any occasion'],
                ['name' => 'Cupcakes',         'description' => 'Mini cakes perfect for parties'],
                ['name' => 'Cake Pops',        'description' => 'Fun bite-sized cake on a stick'],
                ['name' => 'Cookies',          'description' => 'Homemade cookies and brownies'],
                ['name' => 'Treat Packages',   'description' => 'Curated sweet boxes and party packages'],
                ['name' => 'Presale Collections', 'description' => 'Seasonal limited-time collections'],
            ] as $cat) {
                $c = R::dispense('category');
                $c->name        = $cat['name'];
                $c->description = $cat['description'];
                R::store($c);
            }
        }

        // Admin account
        if (R::count('admin') === 0) {
            $admin = R::dispense('admin');
            $admin->email         = 'sabrina@sagesucre.ca';
            $admin->password_hash = password_hash('Admin1234!', PASSWORD_BCRYPT);
            $admin->first_name    = 'Sabrina';
            $admin->last_name     = 'Moustaine';
            R::store($admin);
        }

        // Pickup locations
        if (R::count('pickuplocation') === 0) {
            foreach ([
                'Plateau (Montreal)',
                'Boisbriand',
            ] as $loc) {
                $l = R::dispense('pickuplocation');
                $l->name = $loc;
                R::store($l);
            }
        }

        // Products
        if (R::count('product') === 0) {
            $products = [
                [
                    'name' => 'Vanilla Cupcakes',
                    'description' => 'Classic vanilla cupcakes with buttercream frosting.',
                    'price' => 24.00,
                    'image' => 'cupcakes.jpg',
                    'category_id' => 2,
                ],
                [
                    'name' => 'Custom Chocolate Cake',
                    'description' => 'Rich chocolate cake made for birthdays and events.',
                    'price' => 45.00,
                    'image' => 'chocolate-cake.jpg',
                    'category_id' => 1,
                ],
                [
                    'name' => 'Decorated Sugar Cookies',
                    'description' => 'Homemade decorated cookies for special occasions.',
                    'price' => 18.00,
                    'image' => 'cookies.jpg',
                    'category_id' => 4,
                ],
            ];

            foreach ($products as $item) {
                $product = R::dispense('product');
                $product->name = $item['name'];
                $product->description = $item['description'];
                $product->price = $item['price'];
                $product->image = $item['image'];
                $product->is_available = 1;
                $product->category_id = $item['category_id'];

                R::store($product);
            }
        }

        // Create blockeddate table if empty
        if (R::count('blockeddate') === 0) {
            $dummy = R::dispense('blockeddate');
            $dummy->location_id = 0;
            $dummy->date = '2000-01-01';
            $id = R::store($dummy);
            R::trash(R::load('blockeddate', $id));
        }

        


        R::freeze(true);
    }
}