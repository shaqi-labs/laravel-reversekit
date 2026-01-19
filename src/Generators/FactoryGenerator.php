<?php

declare(strict_types=1);

namespace ShaqiLabs\ReverseKit\Generators;

class FactoryGenerator extends BaseGenerator
{
    /**
     * Generate a model factory.
     *
     * @param array<string, mixed> $entity
     */
    public function generate(array $entity, bool $force = false): string
    {
        $name = $entity['name'];
        $className = "{$name}Factory";
        $path = $this->getPath($className);

        if (! $force && $this->filesystem->exists($path)) {
            return "skipped:{$path}";
        }

        $stub = $this->getStub('factory');
        $content = $this->buildContent($stub, $entity, $className);

        $this->writeFile($path, $content, $force);

        return $path;
    }

    /**
     * Build the factory content.
     */
    protected function buildContent(string $stub, array $entity, string $className): string
    {
        $name = $entity['name'];
        $modelNamespace = $this->getNamespace('Models');
        $definitions = $this->buildDefinitions($entity);
        $imports = $this->buildImports($entity);
        $states = $this->buildStates($entity);

        return str_replace(
            [
                '{{ namespace }}',
                '{{ modelNamespace }}',
                '{{ model }}',
                '{{ class }}',
                '{{ definitions }}',
                '{{ imports }}',
                '{{ states }}',
            ],
            [
                'Database\\Factories',
                $modelNamespace,
                $name,
                $className,
                $definitions,
                $imports,
                $states,
            ],
            $stub
        );
    }

    /**
     * Build factory definitions.
     */
    protected function buildDefinitions(array $entity): string
    {
        $definitions = [];

        foreach ($entity['fields'] as $fieldName => $field) {
            if ($fieldName === 'id') {
                continue;
            }

            $faker = $this->getFakerMethod($fieldName, $field);
            $definitions[] = "            '{$fieldName}' => {$faker},";
        }

        return implode("\n", $definitions);
    }

    /**
     * Get appropriate Faker method for a field.
     */
    protected function getFakerMethod(string $fieldName, array $field): string
    {
        // Check for foreign keys first
        if (str_ends_with($fieldName, '_id')) {
            return $this->getForeignKeyFaker($fieldName);
        }

        // Check field name patterns
        $patternMethod = $this->getFakerByFieldName($fieldName);
        if ($patternMethod !== null) {
            return $patternMethod;
        }

        // Fallback to type-based methods
        return $this->getFakerByType($field);
    }

    /**
     * Get Faker method for foreign key fields.
     */
    protected function getForeignKeyFaker(string $fieldName): string
    {
        $relatedModel = ucfirst($this->relationshipDetector->singularize(str_replace('_id', '', $fieldName)));
        return "\\{$this->getNamespace('Models')}\\{$relatedModel}::factory()";
    }

    /**
     * Get Faker method based on field name patterns.
     */
    protected function getFakerByFieldName(string $fieldName): ?string
    {
        $patterns = $this->getFieldNamePatterns();

        foreach ($patterns as $pattern => $fakerMethod) {
            if (str_contains($fieldName, $pattern) || $fieldName === $pattern) {
                return $fakerMethod;
            }
        }

        return null;
    }

    /**
     * Field name to Faker method mapping.
     */
    protected function getFieldNamePatterns(): array
    {
        return [
            'email' => 'fake()->unique()->safeEmail()',
            'name' => 'fake()->name()',
            'title' => 'fake()->sentence()',
            'body' => 'fake()->paragraphs(3, true)',
            'content' => 'fake()->paragraphs(3, true)',
            'description' => 'fake()->paragraphs(3, true)',
            'url' => 'fake()->url()',
            'link' => 'fake()->url()',
            'phone' => 'fake()->phoneNumber()',
            'address' => 'fake()->address()',
            'city' => 'fake()->city()',
            'country' => 'fake()->country()',
            'zip' => 'fake()->postcode()',
            'postal' => 'fake()->postcode()',
            'image' => 'fake()->imageUrl()',
            'avatar' => 'fake()->imageUrl()',
            'photo' => 'fake()->imageUrl()',
            'password' => 'bcrypt(\'password\')',
        ];
    }

    /**
     * Get Faker method based on PHP type.
     */
    protected function getFakerByType(array $field): string
    {
        $phpType = $field['phpType'] ?? $field['php_type'] ?? 'string';

        return match ($phpType) {
            'int', 'integer' => 'fake()->numberBetween(1, 1000)',
            'float' => 'fake()->randomFloat(2, 1, 1000)',
            'bool', 'boolean' => 'fake()->boolean()',
            'array' => '[]',
            default => 'fake()->word()',
        };
    }

    /**
     * Build imports for factory.
     */
    protected function buildImports(array $entity): string
    {
        return '';
    }

    /**
     * Build state methods.
     */
    protected function buildStates(array $entity): string
    {
        $states = [];

        // Add common states based on field names
        foreach ($entity['fields'] as $fieldName => $field) {
            if ($fieldName === 'published' || $fieldName === 'is_published') {
                $states[] = $this->buildPublishedState($fieldName);
            }

            if ($fieldName === 'active' || $fieldName === 'is_active') {
                $states[] = $this->buildActiveState($fieldName);
            }
        }

        return implode("\n", $states);
    }

    /**
     * Build published state method.
     */
    protected function buildPublishedState(string $fieldName = 'published'): string
    {
        return <<<PHP

    /**
     * Indicate that the model is published.
     */
    public function published(): static
    {
        return \$this->state(fn (array \$attributes) => [
            '{$fieldName}' => true,
        ]);
    }

    /**
     * Indicate that the model is unpublished.
     */
    public function unpublished(): static
    {
        return \$this->state(fn (array \$attributes) => [
            '{$fieldName}' => false,
        ]);
    }
PHP;
    }

    /**
     * Build active state method.
     */
    protected function buildActiveState(string $fieldName = 'active'): string
    {
        return <<<PHP

    /**
     * Indicate that the model is active.
     */
    public function active(): static
    {
        return \$this->state(fn (array \$attributes) => [
            '{$fieldName}' => true,
        ]);
    }

    /**
     * Indicate that the model is inactive.
     */
    public function inactive(): static
    {
        return \$this->state(fn (array \$attributes) => [
            '{$fieldName}' => false,
        ]);
    }
PHP;
    }

    /**
     * Get the path for the factory class.
     */
    protected function getPath(string $className): string
    {
        return database_path('factories/' . $className . '.php');
    }
}

