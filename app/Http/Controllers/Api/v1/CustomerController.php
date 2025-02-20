<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\HandleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\v1\CustomerCollection;
use App\Http\Resources\v1\CustomerResource;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    use ApiResponse;

    protected $customers;
    protected $customer;
    protected array $relations = ['company', 'createdBy', 'updatedBy'];
    protected array $fields = ['id', 'code', 'name', 'ruc', 'phone', 'company_id', 'created_by', 'updated_by'];

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function all(?Company $company = null)
    {
        try {
            $query = Customer::with($this->relations)
                ->select($this->fields);

            if($company)
                $query->where('company_id', $company->id);

            $this->customers = $query->get();
            return new CustomerCollection($this->customers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getTrashed(?Company $company = null) {
        try {
            $query = Customer::with($this->relations)
                ->select($this->fields)
                ->onlyTrashed();

            if($company)
                $query->where('company_id', $company->id);

            $this->customers = $query->get();
            return new CustomerCollection($this->customers);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(CustomerRequest $request)
    {
        try {
            $customer = Customer::create($request->getAllFields());
            $this->customer = new CustomerResource($customer);
            return $this->createdResponse($this->customer, config('messages.success.create_title'), 'El cliente '.config('messages.success.create_message'));
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
            $this->customer = new CustomerResource($customer);
            return $this->successResponse($this->customer, config('messages.success.update_title'), 'El cliente '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $force = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $customer = Customer::withTrashed()->findOrFail($id);

            $customer->safeDelete($force);
            $messageKey = $force ? 'delete' : 'remove';

            return $this->successResponse(null, config("messages.success.{$messageKey}_title"), 'El cliente '.config("messages.success.{$messageKey}_message"));
        }
        catch (HandleException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
        catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restore($id)
    {
        try {
            $customer = Customer::onlyTrashed()->findOrFail($id);
            $customer->restore();
            $this->customer = new CustomerResource($customer);
            return $this->successResponse($this->customer, config('messages.success.restore_title'), 'El cliente '.config('messages.success.restore_message'));
        }
        catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request, ?Company $company = null)
    {
        try {
            $customers = new CustomerCollection($request->resources);
            $customerIds = $customers->getIdsAttribute();
            $force = filter_var($request->del, FILTER_VALIDATE_BOOL);
            $active = filter_var($request->active, FILTER_VALIDATE_BOOL);

            $result = Customer::destroyAll($customerIds, $force);

            $query = $active
                ? Customer::with($this->relations)->select($this->fields)
                : Customer::with($this->relations)->select($this->fields)->onlyTrashed();

            if($company)
                $query->where('company_id', $company->id);

            $data = $query->get();

            return $this->successResponse(
                $data,
                $result['title'],
                $result['message']
            );
        }
        catch (HandleException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
        catch (Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            return DB::transaction(function() use ($request) {
                $customers = new CustomerCollection($request->resources);
                $customerIds = $customers->getIdsAttribute();
                Customer::whereIn('id', $customerIds)->restore();

                $this->customers = Customer::with('company')->whereIn('id', $customerIds)->get();

                return $this->successResponse($this->customers, config('messages.success.restoreall_title'), 'Los clientes '.config('messages.success.restoreall_message'));
            }, 5);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
