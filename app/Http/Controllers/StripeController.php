<?php

namespace App\Http\Controllers;

use Exception;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use Stripe\Exception\CardException;

class StripeController extends Controller
{
    private $stripe;
    public $error;
    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.api_keys.secret_key'));
    }

    private function createToken($card, $year, $month, $cvv){
        $token = null;
        try {
            $token_data = [
                'card' => [
                    'number' => $card,
                    'exp_month' => $month,
                    'exp_year' => $year,
                    'cvc' => $cvv
                ]
            ];
            $token = $this->stripe->tokens->create($token_data);
        } catch (CardException $e){
            $token['error'] =  $e->getError()->message;
        } catch (Exception $e){
            $token['error'] = $e->getMessage();
        }

        return $token;
    }

    private function charge_amount($token, $amount, $description){
        $charge = null;
        try {
            $charge_data = [
                'amount' => $amount * 100,
                'currency' => 'usd',
                'source' => $token,
                'description' => $description
            ];
            $charge = $this->stripe->charges->create($charge_data);
        } catch (Exception $e) {
            $charge['error'] = $e->getMessage();
        }

        return $charge;
    }

    public function charge($cardnumber, $month, $year, $cvv, $amount, $description){
        $token = $this->createToken($cardnumber, $year, $month, $cvv);        
        if(!empty($token['error'])){
            $this->error = $token['error'];
            return false;
        }        
        if(empty($token['id'])){
            $this->error = "Failed to Charge Card!";
            return false;
        }

        $charge = $this->charge_amount($token['id'], $amount, $description);
        if(!empty($charge['error'])){
            $this->error = $charge['error'];
            return false;
        }
        if(!empty($charge && $charge['status'] == 'success')){
            return $charge;
        } else {
            $this->error = "Payment Failed";
            return false;
        }
    }
}
