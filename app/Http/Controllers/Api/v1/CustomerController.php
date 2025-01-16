<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\v1\CustomerCollection;
use App\Http\Resources\v1\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;

class CustomerController extends Controller
{
    use ApiResponse;

    protected $customers;

    public function index()
    {
        try {
            $this->customers = Customer::all();
            return new CustomerCollection($this->customers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getDeleted() {
        try {
            $this->customers = Customer::onlyTrashed()->get();
            return new CustomerCollection($this->customers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(CustomerRequest $request)
    {
        try {
            $customer = Customer::create($request->getAllFields());
            return $this->createdResponse($customer, config('messages.success.create_title'), 'El cliente '.config('messages.success.create_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Customer $customer)
    {
        try {
            return new CustomerResource($customer);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        try {
            $customer->update($request->getAllFields());
            return $this->successResponse($customer, config('messages.success.update_title'), 'El cliente '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $delete = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $customer = Customer::withTrashed()->findOrFail($id);
            if(!$delete) {
                $customer->delete();
                return $this->successResponse(null, config('messages.success.remove_title'), 'El cliente '.config('messages.success.remove_message'));
            } else {
                $customer->forceDelete();
                return $this->successResponse(null, config('messages.success.delete_title'), 'El cliente '.config('messages.success.delete_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restore($id)
    {
        try {
            $customer = Customer::onlyTrashed()->findOrFail($id);
            $customer->restore();
            return $this->successResponse(null, config('messages.success.restore_title'), 'El cliente '.config('messages.success.restore_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request)
    {
        try {
            $customers = new CustomerCollection($request->resources);
            $customerIds = $customers->getIdsAttribute();
            $delete = filter_var($request->del, FILTER_VALIDATE_BOOL);

            $existingCustomer = Customer::withTrashed()->whereIn('id', $customerIds)->count();

            if ($existingCustomer !== count($customerIds)) {
                return $this->errorResponse('Uno de los registros no existe.'.$existingCustomer, 404);
            }

            if(!$delete) {
                Customer::whereIn('id', $customerIds)->delete();
                return $this->successResponse(null, config('messages.success.removeall_title'), 'Los clientes '.config('messages.success.removeall_message'));
            }
            else {
                Customer::whereIn('id', $customerIds)->forceDelete();
                return $this->successResponse(null, config('messages.success.deleteall_title'), 'Los clientes '.config('messages.success.deleteall_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            $customers = new CustomerCollection($request->resources);
            $customerIds = $customers->getIdsAttribute();
            Customer::whereIn('id', $customerIds)->restore();
            return $this->successResponse(null, config('messages.success.restoreall_title'), 'Los clientes '.config('messages.success.restoreall_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
