<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestAccessSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that unauthenticated guests accessing HTML /app/* routes get redirected to login (302).
     */
    public function test_guest_is_redirected_to_login_on_all_app_routes(): void
    {
        $routes = [
            '/app',
            '/app/courses',
            '/app/schedule',
            '/app/assignments',
            '/app/attendance',
            '/app/grades',
            '/app/grades/khs/download',
            '/app/materials',
            '/app/discussions',
            '/app/settings',
            '/app/notifications',
            '/app/search',
            '/app/copilot',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(302, "Guest access to HTML {$route} should redirect to login");
            $response->assertRedirect(route('login'));
        }
    }

    /**
     * Test that unauthenticated guests sending JSON requests to /api/* and /app/* get 401 Unauthorized.
     */
    public function test_guest_receives_401_on_all_json_requests(): void
    {
        $getJsonRoutes = [
            '/api/schedule/events',
            '/api/notifications',
            '/api/copilot/suggestions',
            '/app/search',
        ];

        foreach ($getJsonRoutes as $route) {
            $response = $this->getJson($route);
            $response->assertStatus(401, "Guest JSON GET request to {$route} should return 401 Unauthorized");
        }

        $postJsonRoutes = [
            '/api/copilot/ask',
            '/app/discussions',
            '/app/notifications/mark-all-read',
            '/app/attendance/check-in/qr',
            '/app/attendance/check-in/code',
            '/app/attendance/permit',
        ];

        foreach ($postJsonRoutes as $route) {
            $response = $this->postJson($route, []);
            $response->assertStatus(401, "Guest JSON POST request to {$route} should return 401 Unauthorized");
        }
    }
}
