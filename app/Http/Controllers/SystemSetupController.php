<?php

namespace App\Http\Controllers;

use App\Services\AppRoles;
use App\Support\SystemSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SystemSetupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user || !$user->hasRole(AppRoles::Software)) {
                abort(403, 'Access denied. Software role required.');
            }
            return $next($request);
        });
    }

    public function edit()
    {
        $settings = [
            'top_navbar_title' => SystemSettings::topNavbarTitle(),
            'print_slip_heading' => SystemSettings::printSlipHeading(),
            'print_slip_phone' => SystemSettings::printSlipPhone(),
            'print_slip_image' => SystemSettings::printSlipImage(),
            'print_slip_image_url' => SystemSettings::printSlipImageUrl(),
            'color_combination' => SystemSettings::activeColorCombination()['id'],
            'navbar_primary_color' => SystemSettings::navbarPrimaryColor(),
            'navbar_secondary_color' => SystemSettings::navbarSecondaryColor(),
        ];
        $colorCombinations = SystemSettings::colorCombinations();

        return view('setup.system', compact('settings', 'colorCombinations'));
    }

    public function update(Request $request)
    {
        $allowedComboIds = collect(SystemSettings::colorCombinations())->pluck('id')->all();

        $validated = $request->validate([
            'top_navbar_title' => 'required|string|max:100',
            'print_slip_heading' => 'required|string|max:150',
            'print_slip_phone' => 'nullable|string|max:50',
            'color_combination' => 'required|string|in:' . implode(',', $allowedComboIds),
            'print_slip_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
            'remove_print_slip_image' => 'nullable|boolean',
        ]);

        SystemSettings::set(SystemSettings::TOP_NAVBAR_TITLE, $validated['top_navbar_title']);
        SystemSettings::set(SystemSettings::PRINT_SLIP_HEADING, $validated['print_slip_heading']);
        SystemSettings::set(SystemSettings::PRINT_SLIP_PHONE, $validated['print_slip_phone'] ?? '');

        $combo = SystemSettings::resolveColorCombination($validated['color_combination']);
        SystemSettings::set(SystemSettings::NAVBAR_PRIMARY_COLOR, $combo['primary']);
        SystemSettings::set(SystemSettings::NAVBAR_SECONDARY_COLOR, $combo['secondary']);

        $currentImage = SystemSettings::printSlipImage();

        if ($request->boolean('remove_print_slip_image') && $currentImage !== '') {
            $this->deletePublicFile($currentImage);
            SystemSettings::set(SystemSettings::PRINT_SLIP_IMAGE, '');
            $currentImage = '';
        }

        if ($request->hasFile('print_slip_image')) {
            $dir = public_path('uploads/system');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            if ($currentImage !== '') {
                $this->deletePublicFile($currentImage);
            }

            $file = $request->file('print_slip_image');
            $name = 'print-slip-' . Str::lower(Str::random(8)) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($dir, $name);
            SystemSettings::set(SystemSettings::PRINT_SLIP_IMAGE, 'uploads/system/' . $name);
        }

        return redirect()
            ->route('setup.system')
            ->with('success', 'System Setup saved successfully.');
    }

    private function deletePublicFile(string $relativePath): void
    {
        $full = public_path($relativePath);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
