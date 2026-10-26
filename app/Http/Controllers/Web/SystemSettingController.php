<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(): View
    {
        $settings = SystemSetting::all()->pluck('value', 'key')->toArray();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'delay_threshold_low' => ['required', 'numeric', 'min:1'],
            'delay_threshold_medium' => ['required', 'numeric', 'gt:delay_threshold_low'],
            'group_scenario' => ['required', 'string', 'in:A,B,C,CUSTOM'],
        ]);

        $old = SystemSetting::all()->pluck('value', 'key')->toArray();

        SystemSetting::set('delay_threshold_low', $request->delay_threshold_low, 'delay_rules', 'Hours threshold for low delay classification');
        SystemSetting::set('delay_threshold_medium', $request->delay_threshold_medium, 'delay_rules', 'Hours threshold for medium delay classification');
        SystemSetting::set('group_scenario', $request->group_scenario, 'scenario', 'Academic UAS group scenario preset');

        $this->auditService->log(
            'SETTINGS_UPDATED',
            'SystemSetting',
            null,
            "System delay rules and group scenario updated to Group {$request->group_scenario}.",
            $old,
            $request->only(['delay_threshold_low', 'delay_threshold_medium', 'group_scenario']),
            Auth::id()
        );

        return back()->with('success', 'System business rules & delay thresholds updated successfully.');
    }

    /**
     * Quick preset activator for Group A, B, or C scenarios (Slide 23).
     */
    public function applyPreset(string $group): RedirectResponse
    {
        switch (strtoupper($group)) {
            case 'A':
                // Group A: Route SEA -> Europe, delay threshold 12 jam, customs hold
                SystemSetting::set('delay_threshold_low', 6, 'delay_rules');
                SystemSetting::set('delay_threshold_medium', 12, 'delay_rules');
                SystemSetting::set('group_scenario', 'A', 'scenario');
                break;
            case 'B':
                // Group B: Route Asia -> North America, delay threshold 18 jam, weather delay
                SystemSetting::set('delay_threshold_low', 8, 'delay_rules');
                SystemSetting::set('delay_threshold_medium', 18, 'delay_rules');
                SystemSetting::set('group_scenario', 'B', 'scenario');
                break;
            case 'C':
                // Group C: Route SEA -> Australia, delay threshold 24 jam, documentation issue
                SystemSetting::set('delay_threshold_low', 12, 'delay_rules');
                SystemSetting::set('delay_threshold_medium', 24, 'delay_rules');
                SystemSetting::set('group_scenario', 'C', 'scenario');
                break;
            default:
                return back()->with('error', 'Invalid group preset selected.');
        }

        $this->auditService->log(
            'SCENARIO_PRESET_APPLIED',
            'SystemSetting',
            null,
            "Applied Group {$group} scenario preset.",
            null,
            ['preset' => $group],
            Auth::id()
        );

        return back()->with('success', "Group {$group} scenario preset activated successfully!");
    }
}
