<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePaymentRequest;
use App\Models\Product;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProcessPayment extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(CreatePaymentRequest $request)
    {
        $user = User::firstOrCreate(
            [
                'email' => $request->email
            ],
            [
                'password' => Hash::make($request->password),
                'name' => $request->name,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'zip_code' => $request->zip_code,
            ]
        );

        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));

        try {
            $user->createOrGetStripeCustomer();

            $payment = $user->charge(
                $request->amount, $request->payment_method_id
            );

            $payment = $payment->asStripePaymentIntent();

            $order = $user->orders()->create([
                'transaction_id' => $payment->charges->data[0]->id,
                'total' => $payment->charges->data[0]->amount
            ]);

            $products = Product::all()->random(2);

            foreach ($products as $product){
                $order->products()->attach($product->id, ['quantity' => '2']);
            }

            $order->load('products');

            return Inertia::render('PaymentProcessed', [
                'success' => 'User created created.', 'order' => $order
            ]);

        } catch (\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        };

    }

}
