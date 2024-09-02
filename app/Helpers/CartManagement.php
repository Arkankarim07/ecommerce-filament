<?php
namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\Facades\Cookie;

class CartManagement {
    // add item to cart
    static public function addItemsToCart($product_id) {
        // Ambil item-itemn di dalam cart dari cookie
        $cart_items = self::getCartItemsFromCookie();
        
        $existing_item = null; //variable untuk menampung index item yang ada, dengan default null
        
        // looping untuk mencari apakah produl sudah ada di keranjang
        foreach ($cart_items as $key => $item) {
            // jika id dari item sama, simpan index di $existing_item
            if($item['product_id'] == $product_id) {
                $existing_item = $key;
                break;
            }
        }
        
        if($existing_item !== null) {
            // jika item sudah ada, tambahkan jumlah quantity
            $cart_items[$existing_item]['quantity']++;
            // hitung total = unit_amount * quantity
            $cart_items[$existing_item]['total_amount'] = $cart_items[$existing_item]['quantity'] * $cart_items[$existing_item]['unit_amount'];

            $cart_items[$existing_item]['unit_amount'];
        } else {
            // jika produk belum ada , ambil data dari database products
            $product = Product::where('id', $product_id)->first(['id', 'name', 'image', 'price']);
            if($product) {
                // tambahkan produk baru ke dalam array $cart_items
                $cart_items[] = [
                    'product_id' => $product_id,
                    'name' => $product->name,
                    'image' => $product->image[0],
                    'quantity' => 1,
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price
                ];
            }
        }

        // simpan item-item cart kembali ke cookie
        self::addCartItemsToCookie($cart_items);
        // kembalikan jumlah item di cart
        return count($cart_items);
        
    }

    // add items to cart with qty
    static public function addItemsToCartWithQty($product_id, $qty = 1) {
        // Ambil item-itemn di dalam cart dari cookie
        $cart_items = self::getCartItemsFromCookie();
        
        $existing_item = null; //variable untuk menampung index item yang ada, dengan default null
        
        // looping untuk mencari apakah produl sudah ada di keranjang
        foreach ($cart_items as $key => $item) {
            // jika id dari item sama, simpan index di $existing_item
            if($item['product_id'] == $product_id) {
                $existing_item = $key;
                break;
            }
        }
        
        if($existing_item !== null) {
            // jika item sudah ada, tambahkan jumlah quantity
            $cart_items[$existing_item]['quantity'] = $qty;
            // hitung total = unit_amount * quantity
            $cart_items[$existing_item]['total_amount'] = $cart_items[$existing_item]['quantity'] * $cart_items[$existing_item]['unit_amount'];

            $cart_items[$existing_item]['unit_amount'];
        } else {
            // jika produk belum ada , ambil data dari database products
            $product = Product::where('id', $product_id)->first(['id', 'name', 'image', 'price']);
            if($product) {
                // tambahkan produk baru ke dalam array $cart_items
                $cart_items[] = [
                    'product_id' => $product_id,
                    'name' => $product->name,
                    'image' => $product->image[0],
                    'quantity' => $qty,
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price
                ];
            }
        }

        // simpan item-item cart kembali ke cookie
        self::addCartItemsToCookie($cart_items);
        // kembalikan jumlah item di cart
        return count($cart_items);
        
    }



    // remove item to cart
    static public function removeCartItem($product_id) {
        // Ambil item-itemn di dalam cart dari cookie
        $cart_items = self::getCartItemsFromCookie();

        // Looping untuk mencari dan menghapus item berdasarkan product_id
        foreach ($cart_items as $key => $item) {
            // jika id dari item sama, hapus item tersebut
            if($item['product_id'] == $product_id) {
                unset($cart_items[$key]);
            }
        }

         // Simpan item-item cart kembali ke cookie
        self::addCartItemsToCookie($cart_items);

        // Kembalikan item-item cart yang sudah diperbarui
        return $cart_items;
        
    }
    // add cart items to cookie
    static public function addCartItemsToCookie($cart_items) {
        Cookie::queue('cart_items', json_encode($cart_items), 60*24*30);
        /* 
            Buat Cookie dengan Nama cart_items
            dan buat jadi expired setiap 30 hari

            json_encode mengonversi data(array atau objek) ke string JSON
            CONTOH:
            {"nama":"John","umur":25,"hobi":["membaca","berenang"]}

        */
    }


    // clear cart items from cookie
    static public function clearCartItems() {
        Cookie::queue(Cookie::forget('cart_items'));
        // menghapus cookie yang bernama cart_items
    }
    // get all cart items from cookie
    static public function getCartItemsFromCookie() {
        $cart_items = json_decode(Cookie::get('cart_items'), true);

        if(!$cart_items) {
            $cart_items = [];
        }

        return $cart_items;

        /* 
            json_Decode mengonversi string JSON ke data(array atau objek)
             
            Jika Cookie cart_items tidak ada, buat cart_items kosong

            mengembalikan array
        */
    }
    
    // increase item quantity
    static public function incrementQuantityToCartItem($product_id) {
        // Ambil item-item dari cookie
        $cart_items = self::getCartItemsFromCookie();
    
        // Looping untuk mencari item berdasarkan product_id dan menambah quantity
        foreach($cart_items as $key => $item) {
            if($item['product_id'] == $product_id) {
                // tambahkan quantity ++
                $cart_items[$key]['quantity']++;
                // hitung total = price * quantity
                $cart_items[$key]['total_amount'] = (float)$cart_items[$key]['quantity'] * (float)$cart_items[$key]['unit_amount'];
            }
        }
    
        // Simpan item-item cart kembali ke cookie
        self::addCartItemsToCookie($cart_items);
        return $cart_items; // kembalikan item di cart
    }
    
    static public function decrementQuantityToCartItem($product_id) {
        // Ambil item-item dari cookie
        $cart_items = self::getCartItemsFromCookie();
    
        // Looping untuk mencari item berdasarkan product_id dan mengurangi quantity
        foreach($cart_items as $key => $item) {
            if($item['product_id'] == $product_id) {
                // jika quantity lebih dari 1, kurangi quantity
                if($cart_items[$key]['quantity'] > 1) {
                    $cart_items[$key]['quantity']--;
                    $cart_items[$key]['total_amount'] = (float)$cart_items[$key]['quantity'] * (float)$cart_items[$key]['unit_amount'];
                }
            }
        }
    
        // Simpan item-item cart kembali ke cookie
        self::addCartItemsToCookie($cart_items);
        return $cart_items; // kembalikan item di cart
    }
    
    // calculate grand total
    static public function calculateGrandTotal($items) {
        // Hitung total dari semua item di keranjang dengan menjumlahkan 'total_amount' dari setiap item
        return array_sum(array_column($items, 'total_amount'));
    }

}

