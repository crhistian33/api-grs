<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\v1\CompanyCollection;
use App\Http\Resources\v1\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Exception;

class CompanyController extends Controller
{
    use ApiResponse;

    protected $companies;

    public function index()
    {
        try {
            $this->companies = Company::all();
            return new CompanyCollection($this->companies);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getDeleted() {
        try {
            $this->companies = Company::onlyTrashed()->get();
            return new CompanyCollection($this->companies);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function store(CompanyRequest $request)
    {
        try {
            $company = Company::create($request->validated());
            return $this->createdResponse($company, config('messages.success.create_title'), 'La empresa '.config('messages.success.create_message'));
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
            $company->update($request->validated());
            return $this->successResponse($company, config('messages.success.update_title'), 'La empresa '.config('messages.success.update_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $delete = filter_var(request()->query('delete'), FILTER_VALIDATE_BOOL);
            $company = Company::withTrashed()->findOrFail($id);
            if(!$delete) {
                $company->delete();
                return $this->successResponse(null, config('messages.success.remove_title'), 'La empresa '.config('messages.success.remove_message'));
            } else {
                $company->forceDelete();
                return $this->successResponse(null, config('messages.success.delete_title'), 'La empresa '.config('messages.success.delete_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restore($id)
    {
        try {
            $company = Company::onlyTrashed()->findOrFail($id);
            $company->restore();
            return $this->successResponse(null, config('messages.success.restore_title'), 'La empresa '.config('messages.success.restore_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function destroyAll(Request $request)
    {
        try {
            $companies = new CompanyCollection($request->resources);
            $companyIds = $companies->getIdsAttribute();
            $delete = filter_var($request->del, FILTER_VALIDATE_BOOL);

            $existingCompany = Company::withTrashed()->whereIn('id', $companyIds)->count();

            if ($existingCompany !== count($companyIds)) {
                return $this->errorResponse('Uno de los registros no existe.'.$existingCompany, 404);
            }

            if(!$delete) {
                Company::whereIn('id', $companyIds)->delete();
                return $this->successResponse(null, config('messages.success.removeall_title'), 'Las empresas '.config('messages.success.removeall_message'));
            }
            else {
                Company::whereIn('id', $companyIds)->forceDelete();
                return $this->successResponse(null, config('messages.success.deleteall_title'), 'Las empresas '.config('messages.success.deleteall_message'));
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restoreAll(Request $request)
    {
        try {
            $companies = new CompanyCollection($request->resources);
            $companyIds = $companies->getIdsAttribute();
            Company::whereIn('id', $companyIds)->restore();
            return $this->successResponse(null, config('messages.success.restoreall_title'), 'Las empresas '.config('messages.success.restoreall_message'));
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
