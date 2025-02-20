<?php

namespace App\Http\Controllers\Api\v1;

use App\Exceptions\HandleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\v1\CompanyCollection;
use App\Http\Resources\v1\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    use ApiResponse;

    protected $companies;
    protected $company;
    protected array $relations = ['createdBy', 'updatedBy'];
    protected array $fields = ['id', 'code', 'name', 'created_by', 'updated_by'];

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try {
            $user = auth()->user();

            if($user->role->name === 'admin')
                $this->companies = Company::with($this->relations)->select($this->fields)->get();
            else
                $this->companies = auth()->user()->companies;

            return new CompanyCollection($this->companies);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getTrashed() {
        try {
            $user = auth()->user();
            $query = $this->companies = Company::with($this->relations)
                ->select($this->fields)
                ->onlyTrashed();

            if ($user->role->name !== 'admin') {
                $query->whereHas('users', function ($query) use ($user) {
                    $query->where('users.id', $user->id);
                });
            }

            $this->companies = $query->get();

            return new CompanyCollection($this->companies);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(CompanyRequest $request)
    {
        try {
            $companyStore = Company::create($request->getAllFields());
            $this->company = new CompanyResource($companyStore);
            return $this->createdResponse($this->company, config('messages.success.create_title'), 'La empresa '.config('messages.success.create_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function show(Company $company)
    {
        try {
            return new CompanyResource($company);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function update(CompanyRequest $request, Company $company)
    {
        try {
            $company->update($request->getAllFields());
            $this->company = new CompanyResource($company);
            return $this->successResponse($this->company, config('messages.success.update_title'), 'La empresa '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $force = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $company = Company::withTrashed()->findOrFail($id);

            $company->safeDelete($force);
            $messageKey = $force ? 'delete' : 'remove';

            return $this->successResponse(null, config("messages.success.{$messageKey}_title"), 'La empresa '.config("messages.success.{$messageKey}_message"));
        }
        catch (HandleException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
        catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request)
    {
        try {
            $companies = new CompanyCollection($request->resources);
            $companiesIds = $companies->getIdsAttribute();
            $force = filter_var($request->del, FILTER_VALIDATE_BOOL);
            $active = filter_var($request->active, FILTER_VALIDATE_BOOL);

            $result = Company::destroyAll($companiesIds, $force);

            $data = $active
                ? Company::with($this->relations)->select($this->fields)->get()
                : Company::with($this->relations)->select($this->fields)->onlyTrashed()->get();

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
            return $this->handleException($e);
        }
    }

    public function restore($id)
    {
        try {
            $company = Company::onlyTrashed()->findOrFail($id);
            $company->restore();
            $this->company = new CompanyResource($company);
            return $this->successResponse($this->company, config('messages.success.restore_title'), 'La empresa '.config('messages.success.restore_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            return DB::transaction(function() use ($request) {
                $companies = new CompanyCollection($request->resources);
                $companyIds = $companies->getIdsAttribute();
                Company::whereIn('id', $companyIds)->restore();

                $this->companies = Company::whereIn('id', $companyIds)->get();

                return $this->successResponse($this->companies, config('messages.success.restoreall_title'), 'Las empresas '.config('messages.success.restoreall_message'));
            }, 5);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
