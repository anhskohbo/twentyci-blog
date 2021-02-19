<?php

namespace Tests\Feature;

use App\Group;
use App\Models\Post;
use App\Models\User;
use Tests\TestCase;

class PostCRUDTest extends TestCase
{
    public function test_guest_cannot_access_dashboard_page()
    {
        $this->get(route('dashboard.posts.index'))
            ->assertRedirect('/login');

        $this->get(route('dashboard.posts.create'))
            ->assertRedirect('/login');

        $this->get(route('dashboard.posts.edit', [1]))
            ->assertRedirect('/login');
    }

    public function test_create_new_post_with_empty_content()
    {
        $this->asEditor();

        $this->post(
            route('dashboard.posts.store'),
            [
                'title' => '',
            ]
        )->assertSessionHasErrors(['title', 'content']);

        $this->post(
            route('dashboard.posts.store'),
            [
                'title' => 'title',
                'content' => '',
            ]
        )->assertSessionHasErrors(['content']);
    }

    public function test_create_new_post_and_redirect_edit_page()
    {
        $this->asEditor();

        $response = $this
            ->followingRedirects()
            ->post(
                route('dashboard.posts.store'),
                [
                    'title' => 'A great title',
                    'content' => 'content',
                ]
            );

        $response
            ->assertViewIs('dashboard.posts.edit');
    }

    public function test_create_new_post_and_slug_auto_generated()
    {
        $this->asEditor();

        $this->post(
            route('dashboard.posts.store'),
            [
                'title' => 'A great title',
                'content' => 'content',
            ]
        )
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', ['slug' => 'a-great-title']);
    }

    public function test_edit_post_policy_only_author_can_see_their_post()
    {
        $author = User::factory()->create(['level' => Group::MEMBER]);
        $post = Post::factory()->create(['user_id' => $author->getKey()]);

        // Login as another editor.
        $this->asEditor();

        $this->get(route('dashboard.posts.edit', $post))
            ->assertForbidden();
    }

    public function test_edit_post_policy_admin_can_see_author_posts()
    {
        $author = User::factory()->create(['level' => Group::MEMBER]);
        $post = Post::factory()->create(['user_id' => $author->getKey()]);

        // Login as another editor.
        $this->asAdmin();

        $this->get(route('dashboard.posts.edit', $post))
            ->assertOK()
            ->assertSeeText('Edit Post');
    }
}
