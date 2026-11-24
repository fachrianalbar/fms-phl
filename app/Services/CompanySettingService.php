<?php

namespace App\Services;

use App\Helpers\GenerateCode;
use App\Models\CompanySetting;
use App\Traits\LogActivity;
use Illuminate\Http\UploadedFile;
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
        $logoFile = $this->resolveUploadedLogo($request);
        $logo = $logoFile ? $this->storeLogoFile($logoFile) : null;

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
        $logoFile = $this->resolveUploadedLogo($request);

        if ($logoFile) {
            $logo = $this->storeLogoFile($logoFile, $data->logo);
        }

        $this->service->where('id', $id)->update([
            'name' => $request->name,
            'owner' => $request->owner,
            'address' => $request->address,
            'logo' => $logo,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $this->logActivity($title, $this->getById($id), 'After Update');
    }

    private function resolveUploadedLogo($request): ?UploadedFile
    {
        if (method_exists($request, 'file') && $request->file('logo') instanceof UploadedFile) {
            return $request->file('logo');
        }

        return $request->logo instanceof UploadedFile ? $request->logo : null;
    }

    private function storeLogoFile(UploadedFile $file, ?string $oldLogo = null): string
    {
        $path = 'public/company_setting/logo';

        if ($oldLogo) {
            Storage::delete($path.'/'.$oldLogo);
        }

        $filename = $file->hashName();
        Storage::putFileAs($path, $file, $filename);

        return $filename;
    }

    public function destroy($id, $title)
    {
        $this->logActivity($title, $this->getById($id), 'Delete');

        $this->service->where('id', $id)->delete();
    }
}
