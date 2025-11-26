<?php

namespace Modules\Core\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;
use Config\Database;

class SubdomainFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Use HTTP_HOST directly instead of getUri()->getHost() for better subdomain detection
        $host = $_SERVER['HTTP_HOST'] ?? $request->getUri()->getHost();
        $baseDomain = env('app.baseDomain', 'urunankita.id'); // urunankita.id
        
        // Log untuk debug
        log_message('debug', "SubdomainFilter: HTTP_HOST={$host}, URI Host=" . $request->getUri()->getHost());
        
        // For local development, check localhost patterns
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || strpos($host, '.test') !== false) {
            // Local: check if host is like {tenant}.urunankita.test
            $hostParts = explode('.', $host);
            
            log_message('debug', "SubdomainFilter: Host parts: " . json_encode($hostParts) . ", Count: " . count($hostParts));
            
            // Format: {tenant}.urunankita.test (3 parts minimum)
            if (count($hostParts) >= 3 && end($hostParts) === 'test') {
                // Check if this is tenant subdomain (e.g., jerry.urunankita.test)
                // Main domain would be urunankita.test (2 parts only)
                if (count($hostParts) === 3) {
                    $subdomain = $hostParts[0];
                    $domain = $hostParts[1];
                    
                    log_message('debug', "SubdomainFilter: Detected subdomain={$subdomain}, domain={$domain}");
                    
                    // Ignore common subdomains
                    if (!in_array(strtolower($subdomain), ['www', 'api', 'admin', 'app'])) {
                        // Verify domain is urunankita
                        if ($domain === 'urunankita') {
                            log_message('debug', "SubdomainFilter: Resolving tenant for subdomain={$subdomain}");
                            $this->resolveTenant($subdomain);
                            return;
                        }
                    }
                }
            }
            
            // Default to main domain for local (urunankita.test)
            log_message('debug', "SubdomainFilter: No tenant subdomain detected, treating as main domain");
            session()->remove('tenant_slug');
            session()->remove('tenant_id');
            session()->remove('tenant_db');
            session()->set('is_subdomain', false);
            return;
        }
        
        // Production: Extract subdomain from host
        $hostParts = explode('.', $host);
        
        // Check if this is a subdomain request (e.g., tenant.urunankita.id)
        if (count($hostParts) > 2) {
            // Format: {tenant}.urunankita.id
            $subdomain = $hostParts[0];
            $domain = $hostParts[1];
            
            // Verify domain matches base domain (without TLD)
            $baseDomainParts = explode('.', $baseDomain);
            $expectedDomain = $baseDomainParts[0] ?? 'urunankita';
            
            // Ignore common subdomains (www, api, admin)
            if (in_array(strtolower($subdomain), ['www', 'api', 'admin', 'app'])) {
                // This is main domain, no tenant resolution needed
                session()->remove('tenant_slug');
                session()->remove('tenant_id');
                session()->set('is_subdomain', false);
                return;
            }
            
            // Verify domain is urunankita
            if ($domain === $expectedDomain) {
                // This is tenant subdomain
                $this->resolveTenant($subdomain);
            } else {
                // Not our domain, treat as main domain
                session()->remove('tenant_slug');
                session()->remove('tenant_id');
                session()->set('is_subdomain', false);
            }
        } else {
            // Main domain (urunankita.id or urunankita.test)
            session()->remove('tenant_slug');
            session()->remove('tenant_id');
            session()->remove('tenant_db');
            session()->set('is_subdomain', false);
        }
    }

    /**
     * Resolve tenant from subdomain
     * Note: Bootstrap file (app/Config/Boot/tenant.php) sudah handle early detection
     * Filter ini hanya ensure session sudah di-set dengan benar
     */
    protected function resolveTenant(string $subdomain): void
    {
        try {
            // CRITICAL: Always query from CENTRAL database, not tenant database
            $central = Database::connect(); // This should connect to default (central) database
            $tenant = $central->table('tenants')
                ->where('slug', $subdomain)
                ->where('status', 'active')
                ->get()
                ->getRowArray();
            
            if ($tenant && isset($tenant['id'])) {
                // Simplified: No need to register tenant DB connection anymore
                // All data is in single database, BaseModel will auto-filter by tenant_id
                
                // Set tenant context in session
                $tenantId = (int) $tenant['id'];
                session()->set('tenant_id', $tenantId);
                session()->set('tenant_slug', $subdomain);
                session()->set('is_subdomain', true);
                
                log_message('debug', "SubdomainFilter: Resolved tenant - slug={$subdomain}, id={$tenantId}");
            } else {
                // Tenant not found
                log_message('warning', "SubdomainFilter: Tenant not found for subdomain={$subdomain}");
                session()->remove('tenant_slug');
                session()->remove('tenant_id');
                session()->set('is_subdomain', false);
            }
        } catch (\Throwable $e) {
            // Invalid tenant subdomain - treat as main domain
            // Log error but don't block request
            log_message('error', 'SubdomainFilter resolveTenant error: ' . $e->getMessage());
            session()->remove('tenant_slug');
            session()->remove('tenant_id');
            session()->remove('tenant_db');
            session()->set('is_subdomain', false);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

