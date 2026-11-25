<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Models\User;
use Filament\Auth\Http\Responses\LoginResponse;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Liberu\CRM\ClientOnboardingFilament\Pages\PlatformSetupWizard as SetupWizard;
use Liberu\CRM\MarketingResources\Services\PlatformConfiguration;
use Liberu\CRM\MarketingResources\Services\WorkspaceAccess;
use Liberu\Foundation\Organizations\Models\Team;
use Livewire\Features\SupportRedirects\Redirector;

final class PanelLoginResponse extends LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = Filament::auth()->user();
        $panel = Filament::getCurrentPanel()?->getId();
        $team = $user instanceof User ? $user->currentTeam : null;
        if ($user instanceof User && $team) {
            if ($panel === 'admin' && $user->isSuperAdmin() && ! app(PlatformConfiguration::class)->settings()?->getAttribute('setup_completed_at')) {
                return redirect(SetupWizard::getUrl(['tenant' => $team], panel: 'admin'));
            }
            if ($panel === 'app' && $team instanceof Team && ! $team->getAttribute('setup_completed_at') && app(WorkspaceAccess::class)->canManage($user, $team)) {
                return redirect(\Liberu\CRM\Core\Filament\App\Pages\SetupWizard::getUrl(['tenant' => $team], panel: 'app'));
            }
        }

        return parent::toResponse($request);
    }
}
