<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function show($id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        return response()->json(new CustomerResource($customer));
    }

    public function update(CustomerRequest $request, $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $customer->update($request->validated());
        return response()->json([
            'message' => 'Cliente actualizado correctamente.',
            'customer' => new CustomerResource($customer),
        ]);
    }



    
}
