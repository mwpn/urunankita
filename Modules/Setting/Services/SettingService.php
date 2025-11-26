<?php

namespace Modules\Setting\Services;

use Modules\Setting\Models\SettingModel;

class SettingService
{
    protected SettingModel $settingModel;
    protected array $cache = [];

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    /**
     * Get setting value
     *
     * @param string $key
     * @param mixed $default
     * @param string|null $scope (global, tenant, user)
     * @param int|null $scopeId (tenant_id or user_id)
     * @return mixed
     */
    public function get(string $key, mixed $default = null, ?string $scope = null, ?int $scopeId = null)
    {
        // Auto-detect scope if not provided
        if ($scope === null) {
            $scope = $this->detectScope();
        }
        if ($scopeId === null && $scope !== 'global') {
            $scopeId = $this->detectScopeId($scope);
        }

        // Cache key
        $cacheKey = "{$scope}_{$scopeId}_{$key}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $setting = $this->settingModel->getSetting($key, $scope, $scopeId);
        
        if (!$setting) {
            // Try to get from parent scope (user -> tenant -> global)
            if ($scope === 'user') {
                return $this->get($key, $default, 'tenant', session()->get('tenant_id'));
            } elseif ($scope === 'tenant') {
                return $this->get($key, $default, 'global');
            }
            return $default;
        }

        $value = $this->decodeValue($setting['value'], $setting['type']);
        $this->cache[$cacheKey] = $value;

        return $value;
    }

    /**
     * Set setting value
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $scope
     * @param int|null $scopeId
     * @param string|null $type
     * @param string|null $description
     * @return bool
     */
    public function set(string $key, $value, ?string $scope = null, ?int $scopeId = null, ?string $type = null, ?string $description = null): bool
    {
        // Auto-detect scope if not provided
        if ($scope === null) {
            $scope = $this->detectScope();
        }
        if ($scopeId === null && $scope !== 'global') {
            $scopeId = $this->detectScopeId($scope);
        }

        // Auto-detect type if not provided
        if ($type === null) {
            $type = $this->detectType($value);
        }

        $encodedValue = $this->encodeValue($value, $type);

        $data = [
            'key' => $key,
            'value' => $encodedValue,
            'type' => $type,
            'scope' => $scope,
            'scope_id' => $scopeId,
            'description' => $description,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $existing = $this->settingModel->getSetting($key, $scope, $scopeId);
        
        if ($existing) {
            $this->settingModel->update($existing['id'], $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->settingModel->insert($data);
        }

        // Clear cache
        $cacheKey = "{$scope}_{$scopeId}_{$key}";
        unset($this->cache[$cacheKey]);

        return true;
    }

    /**
     * Get all settings for a scope
     *
     * @param string|null $scope
     * @param int|null $scopeId
     * @return array
     */
    public function getAll(?string $scope = null, ?int $scopeId = null): array
    {
        if ($scope === null) {
            $scope = $this->detectScope();
        }
        if ($scopeId === null && $scope !== 'global') {
            $scopeId = $this->detectScopeId($scope);
        }

        $settings = $this->settingModel->getByScope($scope, $scopeId);
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key']] = $this->decodeValue($setting['value'], $setting['type']);
        }

        return $result;
    }

    /**
     * Delete setting
     *
     * @param string $key
     * @param string|null $scope
     * @param int|null $scopeId
     * @return bool
     */
    public function delete(string $key, ?string $scope = null, ?int $scopeId = null): bool
    {
        if ($scope === null) {
            $scope = $this->detectScope();
        }
        if ($scopeId === null && $scope !== 'global') {
            $scopeId = $this->detectScopeId($scope);
        }

        $setting = $this->settingModel->getSetting($key, $scope, $scopeId);
        
        if ($setting) {
            // Clear cache
            $cacheKey = "{$scope}_{$scopeId}_{$key}";
            unset($this->cache[$cacheKey]);
            
            return $this->settingModel->delete($setting['id']);
        }

        return false;
    }

    /**
     * Get tenant settings (with fallback to global)
     *
     * @param string $key
     * @param mixed $default
     * @param int|null $tenantId
     * @return mixed
     */
    public function getTenant(string $key, mixed $default = null, ?int $tenantId = null): mixed
    {
        $tenantId = $tenantId ?? session()->get('tenant_id');
        
        // Try tenant first
        $value = $this->get($key, null, 'tenant', $tenantId);
        
        if ($value !== null) {
            return $value;
        }

        // Fallback to global
        return $this->get($key, $default, 'global');
    }

    /**
     * Set tenant setting
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $tenantId
     * @return bool
     */
    public function setTenant(string $key, $value, ?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? session()->get('tenant_id');
        return $this->set($key, $value, 'tenant', $tenantId);
    }

    /**
     * Detect scope from context
     *
     * @return string
     */
    protected function detectScope(): string
    {
        // Default to tenant scope for multi-tenant apps
        if (session()->get('tenant_id')) {
            return 'tenant';
        }
        return 'global';
    }

    /**
     * Detect scope ID
     *
     * @param string $scope
     * @return int|null
     */
    protected function detectScopeId(string $scope): ?int
    {
        if ($scope === 'tenant') {
            return session()->get('tenant_id');
        } elseif ($scope === 'user') {
            return auth_user()['id'] ?? null;
        }
        return null;
    }

    /**
     * Detect value type
     *
     * @param mixed $value
     * @return string
     */
    protected function detectType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_int($value)) {
            return 'integer';
        } elseif (is_float($value)) {
            return 'float';
        } elseif (is_array($value) || is_object($value)) {
            return 'json';
        }
        return 'string';
    }

    /**
     * Encode value based on type
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    protected function encodeValue($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'integer':
            case 'float':
                return (string) $value;
            case 'json':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }

    /**
     * Decode value based on type
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    protected function decodeValue(string $value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
}

