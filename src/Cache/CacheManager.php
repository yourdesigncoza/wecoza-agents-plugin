<?php
/**
 * Cache Manager
 *
 * Handles caching of frequently accessed data to improve performance.
 *
 * @package WeCoza\Agents
 * @since 1.0.0
 */

namespace WeCoza\Agents\Cache;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cache Manager class
 *
 * @since 1.0.0
 */
class CacheManager {

    /**
     * Cache prefix
     *
     * @var string
     */
    private $cache_prefix = 'wecoza_agents_';

    /**
     * Default cache expiration (in seconds)
     *
     * @var int
     */
    private $default_expiration = 3600; // 1 hour

    /**
     * Cache groups
     *
     * @var array
     */
    private $cache_groups = array(
        'agents' => 'agents',
        'agent_meta' => 'agent_meta',
        'agent_lists' => 'agent_lists',
        'agent_counts' => 'agent_counts',
        'agent_search' => 'agent_search',
        'settings' => 'settings',
        'stats' => 'stats',
    );

    /**
     * Cache statistics
     *
     * @var array
     */
    private $cache_stats = array(
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0,
    );

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init_cache_groups();
        $this->setup_cache_hooks();
    }

    /**
     * Initialize cache groups
     *
     * @since 1.0.0
     */
    private function init_cache_groups() {
        foreach ($this->cache_groups as $group) {
            wp_cache_add_global_groups($group);
        }
    }

    /**
     * Setup cache invalidation hooks
     *
     * @since 1.0.0
     */
    private function setup_cache_hooks() {
        // Agent data changes
        add_action('wecoza_agents_agent_created', array($this, 'invalidate_agent_caches'));
        add_action('wecoza_agents_agent_updated', array($this, 'invalidate_agent_caches'));
        add_action('wecoza_agents_agent_deleted', array($this, 'invalidate_agent_caches'));
        
        // Agent meta changes
        add_action('wecoza_agents_agent_meta_updated', array($this, 'invalidate_agent_meta_cache'));
        add_action('wecoza_agents_agent_meta_deleted', array($this, 'invalidate_agent_meta_cache'));
        
        // Settings changes
        add_action('update_option_wecoza_agents_settings', array($this, 'invalidate_settings_cache'));
        
        // Periodic cache cleanup
        add_action('wp_scheduled_delete', array($this, 'cleanup_expired_caches'));
    }

    /**
     * Get cached data
     *
     * @since 1.0.0
     * @param string $key Cache key
     * @param string $group Cache group
     * @return mixed|false Cached data or false if not found
     */
    public function get($key, $group = 'agents') {
        $cache_key = $this->get_cache_key($key);
        $data = wp_cache_get($cache_key, $group);
        
        if ($data !== false) {
            $this->cache_stats['hits']++;
            return $data;
        }
        
        $this->cache_stats['misses']++;
        return false;
    }

    /**
     * Set cached data
     *
     * @since 1.0.0
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param string $group Cache group
     * @param int $expiration Expiration time in seconds
     * @return bool Whether the data was cached
     */
    public function set($key, $data, $group = 'agents', $expiration = null) {
        $cache_key = $this->get_cache_key($key);
        $expiration = $expiration ?? $this->default_expiration;
        
        $result = wp_cache_set($cache_key, $data, $group, $expiration);
        
        if ($result) {
            $this->cache_stats['sets']++;
        }
        
        return $result;
    }

    /**
     * Delete cached data
     *
     * @since 1.0.0
     * @param string $key Cache key
     * @param string $group Cache group
     * @return bool Whether the data was deleted
     */
    public function delete($key, $group = 'agents') {
        $cache_key = $this->get_cache_key($key);
        $result = wp_cache_delete($cache_key, $group);
        
        if ($result) {
            $this->cache_stats['deletes']++;
        }
        
        return $result;
    }

    /**
     * Get or set cached data
     *
     * @since 1.0.0
     * @param string $key Cache key
     * @param callable $callback Callback to generate data if not cached
     * @param string $group Cache group
     * @param int $expiration Expiration time in seconds
     * @return mixed Cached or generated data
     */
    public function get_or_set($key, $callback, $group = 'agents', $expiration = null) {
        $data = $this->get($key, $group);
        
        if ($data === false) {
            $data = call_user_func($callback);
            if ($data !== false) {
                $this->set($key, $data, $group, $expiration);
            }
        }
        
        return $data;
    }

    /**
     * Cache agent data
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @param array $agent_data Agent data
     * @param int $expiration Expiration time
     */
    public function cache_agent($agent_id, $agent_data, $expiration = null) {
        $this->set("agent_{$agent_id}", $agent_data, 'agents', $expiration);
        
        // Cache by email for quick lookups
        if (!empty($agent_data['email'])) {
            $this->set("agent_email_{$agent_data['email']}", $agent_data, 'agents', $expiration);
        }
        
        // Cache by ID number for quick lookups
        if (!empty($agent_data['id_number'])) {
            $this->set("agent_id_number_{$agent_data['id_number']}", $agent_data, 'agents', $expiration);
        }
    }

    /**
     * Get cached agent
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @return array|false Agent data or false if not cached
     */
    public function get_cached_agent($agent_id) {
        return $this->get("agent_{$agent_id}", 'agents');
    }

    /**
     * Get cached agent by email
     *
     * @since 1.0.0
     * @param string $email Email address
     * @return array|false Agent data or false if not cached
     */
    public function get_cached_agent_by_email($email) {
        return $this->get("agent_email_{$email}", 'agents');
    }

    /**
     * Get cached agent by ID number
     *
     * @since 1.0.0
     * @param string $id_number ID number
     * @return array|false Agent data or false if not cached
     */
    public function get_cached_agent_by_id_number($id_number) {
        return $this->get("agent_id_number_{$id_number}", 'agents');
    }

    /**
     * Cache agent list
     *
     * @since 1.0.0
     * @param array $args Query arguments
     * @param array $agents Agent list
     * @param int $expiration Expiration time
     */
    public function cache_agent_list($args, $agents, $expiration = null) {
        $cache_key = 'agent_list_' . md5(serialize($args));
        $this->set($cache_key, $agents, 'agent_lists', $expiration);
    }

    /**
     * Get cached agent list
     *
     * @since 1.0.0
     * @param array $args Query arguments
     * @return array|false Agent list or false if not cached
     */
    public function get_cached_agent_list($args) {
        $cache_key = 'agent_list_' . md5(serialize($args));
        return $this->get($cache_key, 'agent_lists');
    }

    /**
     * Cache agent count
     *
     * @since 1.0.0
     * @param array $args Query arguments
     * @param int $count Agent count
     * @param int $expiration Expiration time
     */
    public function cache_agent_count($args, $count, $expiration = null) {
        $cache_key = 'agent_count_' . md5(serialize($args));
        $this->set($cache_key, $count, 'agent_counts', $expiration);
    }

    /**
     * Get cached agent count
     *
     * @since 1.0.0
     * @param array $args Query arguments
     * @return int|false Agent count or false if not cached
     */
    public function get_cached_agent_count($args) {
        $cache_key = 'agent_count_' . md5(serialize($args));
        return $this->get($cache_key, 'agent_counts');
    }

    /**
     * Cache search results
     *
     * @since 1.0.0
     * @param string $search_term Search term
     * @param array $args Search arguments
     * @param array $results Search results
     * @param int $expiration Expiration time
     */
    public function cache_search_results($search_term, $args, $results, $expiration = null) {
        $cache_key = 'search_' . md5($search_term . serialize($args));
        $this->set($cache_key, $results, 'agent_search', $expiration ?: 1800); // 30 minutes
    }

    /**
     * Get cached search results
     *
     * @since 1.0.0
     * @param string $search_term Search term
     * @param array $args Search arguments
     * @return array|false Search results or false if not cached
     */
    public function get_cached_search_results($search_term, $args) {
        $cache_key = 'search_' . md5($search_term . serialize($args));
        return $this->get($cache_key, 'agent_search');
    }

    /**
     * Cache agent meta
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @param string $meta_key Meta key
     * @param mixed $meta_value Meta value
     * @param int $expiration Expiration time
     */
    public function cache_agent_meta($agent_id, $meta_key, $meta_value, $expiration = null) {
        $cache_key = "agent_meta_{$agent_id}_{$meta_key}";
        $this->set($cache_key, $meta_value, 'agent_meta', $expiration);
    }

    /**
     * Get cached agent meta
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     * @param string $meta_key Meta key
     * @return mixed|false Meta value or false if not cached
     */
    public function get_cached_agent_meta($agent_id, $meta_key) {
        $cache_key = "agent_meta_{$agent_id}_{$meta_key}";
        return $this->get($cache_key, 'agent_meta');
    }

    /**
     * Cache statistics
     *
     * @since 1.0.0
     * @param string $stat_type Statistics type
     * @param mixed $stats Statistics data
     * @param int $expiration Expiration time
     */
    public function cache_stats($stat_type, $stats, $expiration = null) {
        $cache_key = "stats_{$stat_type}";
        $this->set($cache_key, $stats, 'stats', $expiration ?: 3600); // 1 hour
    }

    /**
     * Get cached statistics
     *
     * @since 1.0.0
     * @param string $stat_type Statistics type
     * @return mixed|false Statistics data or false if not cached
     */
    public function get_cached_stats($stat_type) {
        $cache_key = "stats_{$stat_type}";
        return $this->get($cache_key, 'stats');
    }

    /**
     * Invalidate agent caches
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     */
    public function invalidate_agent_caches($agent_id = null) {
        if ($agent_id) {
            // Invalidate specific agent caches
            $this->delete("agent_{$agent_id}", 'agents');
            $this->delete_agent_related_caches($agent_id);
        }
        
        // Invalidate list and count caches
        $this->flush_cache_group('agent_lists');
        $this->flush_cache_group('agent_counts');
        $this->flush_cache_group('agent_search');
        $this->flush_cache_group('stats');
    }

    /**
     * Invalidate agent meta cache
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     */
    public function invalidate_agent_meta_cache($agent_id) {
        // This would require tracking all meta keys, so we flush the entire group
        $this->flush_cache_group('agent_meta');
    }

    /**
     * Invalidate settings cache
     *
     * @since 1.0.0
     */
    public function invalidate_settings_cache() {
        $this->flush_cache_group('settings');
    }

    /**
     * Delete agent-related caches
     *
     * @since 1.0.0
     * @param int $agent_id Agent ID
     */
    private function delete_agent_related_caches($agent_id) {
        // Get agent data to clear email/ID number caches
        $agent_data = $this->get_cached_agent($agent_id);
        if ($agent_data) {
            if (!empty($agent_data['email'])) {
                $this->delete("agent_email_{$agent_data['email']}", 'agents');
            }
            if (!empty($agent_data['id_number'])) {
                $this->delete("agent_id_number_{$agent_data['id_number']}", 'agents');
            }
        }
    }

    /**
     * Flush entire cache group
     *
     * @since 1.0.0
     * @param string $group Cache group
     */
    private function flush_cache_group($group) {
        // WordPress doesn't have a native way to flush groups
        // This is a simplified implementation
        $cache_keys = get_option($this->cache_prefix . "keys_{$group}", array());
        foreach ($cache_keys as $key) {
            wp_cache_delete($key, $group);
        }
        delete_option($this->cache_prefix . "keys_{$group}");
    }

    /**
     * Get cache key with prefix
     *
     * @since 1.0.0
     * @param string $key Original key
     * @return string Prefixed key
     */
    private function get_cache_key($key) {
        return $this->cache_prefix . $key;
    }

    /**
     * Cleanup expired caches
     *
     * @since 1.0.0
     */
    public function cleanup_expired_caches() {
        // This would be handled by the object cache implementation
        // For now, we just log the cleanup
        if (WP_DEBUG) {
            error_log('WeCoza Agents: Running cache cleanup');
        }
    }

    /**
     * Get cache statistics
     *
     * @since 1.0.0
     * @return array Cache statistics
     */
    public function get_cache_stats() {
        $total_requests = $this->cache_stats['hits'] + $this->cache_stats['misses'];
        $hit_rate = $total_requests > 0 ? ($this->cache_stats['hits'] / $total_requests) * 100 : 0;
        
        return array(
            'hits' => $this->cache_stats['hits'],
            'misses' => $this->cache_stats['misses'],
            'sets' => $this->cache_stats['sets'],
            'deletes' => $this->cache_stats['deletes'],
            'hit_rate' => round($hit_rate, 2),
            'total_requests' => $total_requests,
        );
    }

    /**
     * Get cache configuration
     *
     * @since 1.0.0
     * @return array Cache configuration
     */
    public function get_cache_config() {
        return array(
            'prefix' => $this->cache_prefix,
            'default_expiration' => $this->default_expiration,
            'groups' => $this->cache_groups,
            'backend' => $this->get_cache_backend(),
        );
    }

    /**
     * Get cache backend information
     *
     * @since 1.0.0
     * @return array Cache backend info
     */
    private function get_cache_backend() {
        $backend = array(
            'type' => 'WordPress Object Cache',
            'persistent' => wp_using_ext_object_cache(),
            'support' => array(
                'groups' => true,
                'expiration' => true,
                'flush' => true,
            ),
        );
        
        if (function_exists('wp_cache_supports')) {
            $backend['supports_get_multiple'] = wp_cache_supports('get_multiple');
            $backend['supports_set_multiple'] = wp_cache_supports('set_multiple');
            $backend['supports_delete_multiple'] = wp_cache_supports('delete_multiple');
        }
        
        return $backend;
    }

    /**
     * Warm up cache
     *
     * @since 1.0.0
     */
    public function warm_up_cache() {
        // Pre-load frequently accessed data
        $this->warm_up_agent_counts();
        $this->warm_up_recent_agents();
        $this->warm_up_statistics();
    }

    /**
     * Warm up agent counts
     *
     * @since 1.0.0
     */
    private function warm_up_agent_counts() {
        $statuses = array('active', 'inactive', 'pending');
        foreach ($statuses as $status) {
            $args = array('status' => $status);
            // This would typically call the actual database query
            // For now, we'll just set a placeholder
            $this->cache_agent_count($args, 0);
        }
    }

    /**
     * Warm up recent agents
     *
     * @since 1.0.0
     */
    private function warm_up_recent_agents() {
        $args = array(
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 20,
        );
        // This would typically call the actual database query
        // For now, we'll just set a placeholder
        $this->cache_agent_list($args, array());
    }

    /**
     * Warm up statistics
     *
     * @since 1.0.0
     */
    private function warm_up_statistics() {
        $stats = array(
            'total_agents' => 0,
            'active_agents' => 0,
            'recent_registrations' => 0,
        );
        $this->cache_stats('dashboard', $stats);
    }

    /**
     * Clear all caches
     *
     * @since 1.0.0
     */
    public function clear_all_caches() {
        foreach ($this->cache_groups as $group) {
            $this->flush_cache_group($group);
        }
        
        // Reset statistics
        $this->cache_stats = array(
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'deletes' => 0,
        );
        
        if (WP_DEBUG) {
            error_log('WeCoza Agents: All caches cleared');
        }
    }

    /**
     * Get cache size estimate
     *
     * @since 1.0.0
     * @return array Cache size information
     */
    public function get_cache_size() {
        // This is a simplified estimate
        $total_keys = array_sum($this->cache_stats);
        $estimated_size = $total_keys * 2048; // Rough estimate: 2KB per cached item
        
        return array(
            'total_keys' => $total_keys,
            'estimated_size_bytes' => $estimated_size,
            'estimated_size_mb' => round($estimated_size / 1024 / 1024, 2),
        );
    }
}