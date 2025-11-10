<?php

namespace App\Services;

use App\Helpers\GenerateCode;
use App\Models\CompanySetting;
use App\Traits\LogActivity;
use Illuminate\Support\Facades\Storage;

class CompanySettingService
{
    use LogActivity;

    protected $service;

    public function __construct(CompanySetting $companySetting)
    {
        $this->service = $companySetting;
    }

    public function findAll()
    {
        return $this->service->get();
    }

    public function getById($id)
    {
        return $this->service->where('id', $id)->first();
    }

    public function store($request, $title)
    {
        $logo = null;
        if ($request->logo) {
            $file = $request->logo;
            $logo = $file->getClientOriginalName();

            $logo = str_replace(' ', '_', $logo);

            $path = 'public/company_setting/logo';

            Storage::putFileAs($path, $file, $logo);
        }

        $data = $this->service->create([
            'code' => GenerateCode::generateCode('FMSCS'),
            'name' => $request->name,
            'owner' => $request->owner,
            'address' => $request->address,
            'logo' => $logo,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $this->logActivity($title, $data, 'Create');
    }

    public function update($request, $id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Before Update');

        $data = $this->getById($id);

        $logo = $data->logo;

        if ($request->logo) {
            $file = $request->logo;
            $logo = $file->getClientOriginalName();

            $path = 'public/company_setting/logo/';
            if ($data->logo) {
                Storage::delete($path.$data->logo);
            }

            Storage::putFileAs($path, $file, $logo);
        }

        $this->service->where('id', $id)->update([
            'name' => $request->name,
            'name' => $request->name,
            'owner' => $request->owner,
            'address' => $request->address,
            'logo' => $logo,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
