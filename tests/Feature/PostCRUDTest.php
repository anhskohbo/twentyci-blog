<?php

namespace Tests\Feature;

use App\Group;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PostCRUDTest extends TestCase
{
    use DatabaseTransactions;

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
                    'status' => 'draft'
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
                'status' => 'draft'
            ]
        )
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('posts', ['slug' => 'a-great-title']);
    }

    public function test_editor_cannot_publish_a_post()
    {
        $this->asEditor();

        $response = $this
            ->post(
                route('dashboard.posts.store'),
                [
                    'title' => 'The dummy title',
                    'content' => 'content',
                    'status' => 'publish'
                ]
            );

        $this->assertDatabaseHas('posts', ['title' => 'The dummy title', 'status' => 'draft']);
    }

    public function test_edit_post_policy_author_can_see_other_posts()
    {
        $author = User::factory()->create(['level' => Group::MEMBER]);
        $post = Post::factory()->create(['user_id' => $author->getKey()]);

        // Login as another editor.
        $this->asEditor();

        $this->get(route('dashboard.posts.edit', $post))
            ->assertForbidden();
    }

    public function test_edit_post_policy_only_author_can_see_their_post()
    {
        $author = User::factory()->create(['level' => Group::MEMBER]);
        $post = Post::factory()->create(['user_id' => $author->getKey()]);

        $this->actingAs($author);

        $this->get(route('dashboard.posts.edit', $post))
            ->assertOk()
            ->assertSeeText('Save');
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

    public function test_delete_post_policy_author_can_delete_another_posts()
    {
        $author = User::factory()->create(['level' => Group::MEMBER]);
        $post = Post::factory()->create(['user_id' => $author->getKey()]);

        // Login as another editor.
        $this->asEditor();

        $this->delete(route('dashboard.posts.destroy', $post))
            ->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->getKey()]);
    }

    public function test_delete_post_policy_only_author_can_delete_their_post()
    {
        $author = User::factory()->create(['level' => Group::MEMBER]);
        $post = Post::factory()->create(['user_id' => $author->getKey()]);

        // Login as another editor.
        $this->actingAs($author);

        $this->delete(route('dashboard.posts.destroy', $post))
            ->assertRedirect();

        $this->assertDatabaseMissing('posts', ['id' => $post->getKey()]);
    }

    public function test_delete_post_policy_admin_can_delete_author_posts()
    {
        $author = User::factory()->create(['level' => Group::ADMINISTRATOR]);
        $post = Post::factory()->create(['user_id' => $author->getKey()]);

        // Login as another editor.
        $this->asAdmin();

        $this->delete(route('dashboard.posts.destroy', $post))
            ->assertRedirect();

        $this->assertDatabaseMissing('posts', ['id' => $post->getKey()]);
    }
}
