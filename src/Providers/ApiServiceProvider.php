<?php

namespace Ducal\Api\Providers;

use Ducal\Api\Facades\ApiHelper;
use Ducal\Api\Http\Middleware\ForceJsonResponseMiddleware;
use Ducal\Api\Models\PersonalAccessToken;
use Ducal\Base\Facades\DashboardMenu;
use Ducal\Base\Facades\PanelSectionManager;
use Ducal\Base\PanelSections\PanelSectionItem;
use Ducal\Base\Supports\ServiceProvider;
use Ducal\Base\Traits\LoadAndPublishDataTrait;
use Ducal\Setting\PanelSections\SettingCommonPanelSection;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Events\RouteMatched;
use Laravel\Sanctum\Sanctum;

class ApiServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app['config']->set([
            'scribe.routes.0.match.prefixes' => ['api/*'],
            'scribe.routes.0.apply.headers' => [
                'Authorization' => 'Bearer {token}',
                'Api-Version' => 'v1',
            ],
        ]);

        if (class_exists('ApiHelper')) {
            AliasLoader::getInstance()->alias('ApiHelper', ApiHelper::class);
        }
    }

    public function boot(): void
    {
        if (version_compare('7.2.0', get_core_version(), '>')) {
            return;
        }

        $this
            ->setNamespace('packages/api')
            ->loadRoutes()
            ->loadAndPublishConfigurations(['api', 'permissions'])
            ->loadAndPublishTranslations()
            ->loadMigrations()
            ->loadAndPublishViews();

        if (ApiHelper::enabled()) {
            $this->loadRoutes(['api']);
        }

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-packages-api-sanctum-token',
                    'name' => trans('packages/api::sanctum-token.name'),
                    'icon' => 'ti ti-key',
                    'url' => route('api.sanctum-token.index'),
                    'permissions' => ['api.sanctum-token.index'],
                ]);
        });

        $this->app['events']->listen(RouteMatched::class, function () {
            if (ApiHelper::enabled()) {
                $this->app['router']->pushMiddlewareToGroup('api', ForceJsonResponseMiddleware::class);
            }
        });

        PanelSectionManager::beforeRendering(function () {
            PanelSectionManager::default()
                ->registerItem(
                    SettingCommonPanelSection::class,
                    fn () => PanelSectionItem::make('settings.common.api')
                        ->setTitle(trans('packages/api::api.settings'))
                        ->withDescription(trans('packages/api::api.settings_description'))
                        ->withIcon('ti ti-api')
                        ->withPriority(110)
                        ->withRoute('api.settings')
                );
        });
    }

    protected function getPath(string|null $path = null): string
    {
        return __DIR__ . '/../..' . ($path ? '/' . ltrim($path, '/') : '');
    }
}
