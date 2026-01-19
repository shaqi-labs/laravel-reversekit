<?php

declare(strict_types=1);

namespace ShaqiLabs\ReverseKit;

use Illuminate\Support\ServiceProvider;
use ShaqiLabs\ReverseKit\Commands\ReverseGenerateCommand;
use ShaqiLabs\ReverseKit\Commands\ReverseInteractiveCommand;
use ShaqiLabs\ReverseKit\Generators\ControllerGenerator;
use ShaqiLabs\ReverseKit\Generators\FactoryGenerator;
use ShaqiLabs\ReverseKit\Generators\FormRequestGenerator;
use ShaqiLabs\ReverseKit\Generators\MigrationGenerator;
use ShaqiLabs\ReverseKit\Generators\ModelGenerator;
use ShaqiLabs\ReverseKit\Generators\PolicyGenerator;
use ShaqiLabs\ReverseKit\Generators\ResourceGenerator;
use ShaqiLabs\ReverseKit\Generators\RouteGenerator;
use ShaqiLabs\ReverseKit\Generators\SeederGenerator;
use ShaqiLabs\ReverseKit\Generators\TestGenerator;
use ShaqiLabs\ReverseKit\Parsers\ApiUrlParser;
use ShaqiLabs\ReverseKit\Parsers\DatabaseParser;
use ShaqiLabs\ReverseKit\Parsers\JsonParser;
use ShaqiLabs\ReverseKit\Parsers\OpenApiParser;
use ShaqiLabs\ReverseKit\Parsers\PostmanParser;
use ShaqiLabs\ReverseKit\Support\RelationshipDetector;
use ShaqiLabs\ReverseKit\Support\TypeInferrer;

class ReverseKitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/reversekit.php', 'reversekit');

        $this->app->singleton(TypeInferrer::class, function () {
            return new TypeInferrer();
        });

        $this->app->singleton(RelationshipDetector::class, function ($app) {
            return new RelationshipDetector($app->make(TypeInferrer::class));
        });

        // Register all parsers
        $this->app->singleton(JsonParser::class, function ($app) {
            return new JsonParser(
                $app->make(TypeInferrer::class),
                $app->make(RelationshipDetector::class)
            );
        });

        $this->app->singleton(ApiUrlParser::class, function ($app) {
            return new ApiUrlParser($app->make(JsonParser::class));
        });

        $this->app->singleton(OpenApiParser::class, function ($app) {
            return new OpenApiParser(
                $app->make(TypeInferrer::class),
                $app->make(RelationshipDetector::class)
            );
        });

        $this->app->singleton(PostmanParser::class, function ($app) {
            return new PostmanParser(
                $app->make(TypeInferrer::class),
                $app->make(RelationshipDetector::class)
            );
        });

        $this->app->singleton(DatabaseParser::class, function ($app) {
            return new DatabaseParser($app->make(RelationshipDetector::class));
        });

        // Register all generators
        $this->app->singleton(ModelGenerator::class);
        $this->app->singleton(MigrationGenerator::class);
        $this->app->singleton(ControllerGenerator::class);
        $this->app->singleton(ResourceGenerator::class);
        $this->app->singleton(FormRequestGenerator::class);
        $this->app->singleton(PolicyGenerator::class);
        $this->app->singleton(FactoryGenerator::class);
        $this->app->singleton(SeederGenerator::class);
        $this->app->singleton(TestGenerator::class);
        $this->app->singleton(RouteGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ReverseGenerateCommand::class,
                ReverseInteractiveCommand::class,
            ]);

            // Publish config
            $this->publishes([
                __DIR__ . '/../config/reversekit.php' => config_path('reversekit.php'),
            ], 'reversekit-config');

            // Publish stubs
            $this->publishes([
                __DIR__ . '/../stubs' => resource_path('stubs/reversekit'),
            ], 'reversekit-stubs');
        }
    }
}

